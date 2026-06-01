<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Stripe\Stripe;
use Stripe\PaymentIntent;

class PaymentController extends Controller
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    // GET /payments
    public function index(Request $request)
    {
        $payments = Payment::with(['booking.customer:id,name', 'createdBy:id,name'])
            ->when($request->booking_id,   fn($q) => $q->where('booking_id', $request->booking_id))
            ->when($request->status,       fn($q) => $q->where('status', $request->status))
            ->when($request->payment_type, fn($q) => $q->where('payment_type', $request->payment_type))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('payments.index', compact('payments'));
    }

    // GET /payments/:id
    public function show(Payment $payment)
    {
        $payment->load(['booking.customer', 'createdBy']);

        return view('payments.show', compact('payment'));
    }

    // GET /payments/create?booking_id=&type=deposit|settlement
    public function create(Request $request)
    {
        $booking = Booking::with(['customer', 'room.roomType', 'checkIn', 'bookingServices', 'payments', 'invoice'])
            ->findOrFail($request->booking_id);

        $type = $request->get('type', 'deposit');

        $taxRate    = Setting::taxRate() / 100;
        $extraTotal = $booking->bookingServices->sum('total_price');
        $subtotal   = (float) $booking->room_total + (float) $extraTotal;
        $taxAmount  = round($subtotal * $taxRate, 2);
        $paid       = $booking->payments->where('status', 'paid')->sum('amount');

        // Use invoice grand_total if available — it already carries any discount.
        $grandTotal = $booking->invoice
            ? (float) $booking->invoice->grand_total
            : round($subtotal + $taxAmount, 2);

        $amount = $type === 'deposit'
            ? round($booking->room_total * 0.5, 2)   // no tax on deposit
            : max(0, round($grandTotal - $paid, 2));  // discount + tax already in grandTotal

        return view('payments.create', compact('booking', 'type', 'amount', 'grandTotal', 'paid'));
    }

    // POST /payments/cash
    public function cash(Request $request)
    {
        $data = $request->validate([
            'booking_id'   => ['required', 'exists:bookings,id'],
            'payment_type' => ['required', 'in:deposit,settlement'],
            'amount'       => ['required', 'numeric', 'min:0.01'],
            'notes'        => ['nullable', 'string'],
        ]);

        // BUG FIX: Guard against duplicate deposits.
        // If a paid deposit already exists, block another one.
        if ($data['payment_type'] === 'deposit') {
            $existingDeposit = Payment::where('booking_id', $data['booking_id'])
                ->where('payment_type', 'deposit')
                ->where('status', 'paid')
                ->exists();

            if ($existingDeposit) {
                return back()->with('error', 'A deposit payment has already been recorded for this booking.');
            }
        }

        $payment = DB::transaction(function () use ($data) {
            $payment = Payment::create([
                ...$data,
                'method'     => 'cash',
                'status'     => 'paid',
                'paid_at'    => now(),
                'created_by' => auth()->id(),
            ]);

            $booking = Booking::findOrFail($data['booking_id']);

            if ($data['payment_type'] === 'deposit' && $booking->status === 'pending') {
                $booking->update(['status' => 'confirmed']);
            }

            if ($data['payment_type'] === 'settlement') {
                $booking->invoice?->update(['status' => 'paid', 'issued_at' => now()]);
            }

            return $payment;
        });

        return redirect()->route('payments.show', $payment)
            ->with('success', 'Cash payment recorded successfully.');
    }

    // POST /payments/intent  — JSON endpoint used by Stripe.js on the payment page
    public function createIntent(Request $request)
    {
        $data = $request->validate([
            'booking_id'   => ['required', 'exists:bookings,id'],
            'payment_type' => ['required', 'in:deposit,settlement'],
        ]);

        $booking = Booking::with(['room.roomType', 'bookingServices', 'checkIn', 'payments'])
            ->findOrFail($data['booking_id']);

        // BUG FIX: Guard against duplicate deposit payment intents.
        if ($data['payment_type'] === 'deposit') {
            $existingDeposit = $booking->payments
                ->where('payment_type', 'deposit')
                ->where('status', 'paid')
                ->isNotEmpty();

            if ($existingDeposit) {
                return response()->json(['message' => 'A deposit has already been paid for this booking.'], 422);
            }
        }

        $amount = $this->resolveAmount($booking, $data['payment_type']);

        $payment = Payment::create([
            'booking_id'   => $booking->id,
            'payment_type' => $data['payment_type'],
            'amount'       => $amount,
            'method'       => 'stripe',
            'status'       => 'pending',
            'created_by'   => auth()->id(),
        ]);

        $intent = PaymentIntent::create([
            'amount'   => (int) ($amount * 100),
            'currency' => config('services.stripe.currency', 'usd'),
            'metadata' => [
                'booking_id' => $booking->id,
                'payment_id' => $payment->id,
                'type'       => $data['payment_type'],
            ],
            'automatic_payment_methods' => ['enabled' => true],
        ]);

        $payment->update(['transaction_ref' => $intent->id]);

        return response()->json([
            'client_secret' => $intent->client_secret,
            'payment_id'    => $payment->id,
            'amount'        => $amount,
        ]);
    }

    // POST /payments/webhook — must remain JSON, excluded from CSRF
    public function webhook(Request $request)
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret    = config('services.stripe.webhook_secret');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Webhook signature verification failed.'], 400);
        }

        match ($event->type) {
            'payment_intent.succeeded'      => $this->handlePaymentSuccess($event->data->object),
            'payment_intent.payment_failed' => $this->handlePaymentFailed($event->data->object),
            default => null,
        };

        return response()->json(['received' => true]);
    }

    // POST /payments/:id/refund  [admin only]
    public function refund(Payment $payment)
    {
        if ($payment->status !== 'paid') {
            return back()->with('error', 'Only paid payments can be refunded.');
        }

        DB::transaction(function () use ($payment) {
            if ($payment->method === 'stripe' && $payment->transaction_ref) {
                \Stripe\Refund::create(['payment_intent' => $payment->transaction_ref]);
            }

            $payment->update(['status' => 'refunded']);
        });

        return back()->with('success', 'Payment refunded successfully.');
    }

    // GET /payments/summary/:bookingId  — JSON for live bill preview
    public function summary(int $bookingId)
    {
        $booking    = Booking::with(['checkIn', 'bookingServices.service', 'payments', 'invoice'])
                         ->findOrFail($bookingId);
        $taxRate    = Setting::taxRate();
        $extraTotal = $booking->bookingServices->sum('total_price');
        $subtotal   = (float) $booking->room_total + (float) $extraTotal;
        $taxAmount  = round($subtotal * ($taxRate / 100), 2);

        // Use invoice grand_total if it exists — it already carries any discount.
        $grandTotal     = $booking->invoice
            ? (float) $booking->invoice->grand_total
            : round($subtotal + $taxAmount, 2);
        $discountAmount = $booking->invoice
            ? (float) $booking->invoice->discount_amount
            : 0;

        $depositPaid = (float) $booking->payments
                           ->where('status', 'paid')
                           ->where('payment_type', 'deposit')
                           ->sum('amount');
        $paidTotal  = (float) $booking->payments->where('status', 'paid')->sum('amount');

        return response()->json([
            'room_total'      => $booking->room_total,
            'extra_total'     => $extraTotal,
            'subtotal'        => $subtotal,
            'discount_amount' => $discountAmount,
            'tax_rate'        => $taxRate,
            'tax_amount'      => $taxAmount,
            'grand_total'     => $grandTotal,
            'deposit_paid'    => $depositPaid,
            'total_paid'      => $paidTotal,
            'balance_due'     => max(0, $grandTotal - $paidTotal),
        ]);
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function resolveAmount(Booking $booking, string $type): float
    {
        if ($type === 'deposit') {
            return round($booking->room_total * 0.5, 2);
        }

        $booking->loadMissing(['invoice', 'bookingServices', 'payments']);

        if ($booking->invoice) {
            $grand = (float) $booking->invoice->grand_total;
        } else {
            $taxRate    = Setting::taxRate() / 100;
            $extraTotal = $booking->bookingServices->sum('total_price');
            $subtotal   = (float) $booking->room_total + (float) $extraTotal;
            $grand      = round($subtotal * (1 + $taxRate), 2);
        }

        $paid = $booking->payments->where('status', 'paid')->sum('amount');

        return max(0, round($grand - $paid, 2));
    }

    private function handlePaymentSuccess(\Stripe\PaymentIntent $intent): void
    {
        $payment = Payment::where('transaction_ref', $intent->id)->first();
        if (! $payment) return;

        DB::transaction(function () use ($payment) {
            $payment->update(['status' => 'paid', 'paid_at' => now()]);

            if ($payment->payment_type === 'deposit' && $payment->booking->status === 'pending') {
                $payment->booking->update(['status' => 'confirmed']);
            }

            if ($payment->payment_type === 'settlement') {
                $payment->booking->invoice?->update(['status' => 'paid', 'issued_at' => now()]);
            }
        });
    }

    private function handlePaymentFailed(\Stripe\PaymentIntent $intent): void
    {
        Payment::where('transaction_ref', $intent->id)->update(['status' => 'failed']);
    }
}
