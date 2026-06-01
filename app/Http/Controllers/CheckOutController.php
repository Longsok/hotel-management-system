<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingService;
use App\Models\CheckOut;
use App\Models\ExtraService;
use App\Models\Invoice;
use App\Models\RoomStatusLog;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckOutController extends Controller
{
    // GET /check-outs
    public function index(Request $request)
    {
        $checkOuts = CheckOut::with([
                'booking.customer:id,name,phone',
                'booking.room:id,room_number,floor',
                'checkedOutBy:id,name',
            ])
            ->when($request->date, fn($q) => $q->whereDate('check_out_time', $request->date))
            ->orderByDesc('check_out_time')
            ->paginate(15)
            ->withQueryString();

        return view('check-outs.index', compact('checkOuts'));
    }

    // GET /check-outs/create?booking_id=
    public function create(Request $request)
    {
        $booking = null;

        if ($request->booking_id) {
            $booking = Booking::with([
                    'customer',
                    'room.roomType',
                    'bookingServices.service',
                    'checkIn',
                    'payments',
                ])
                ->findOrFail($request->booking_id);

            if ($booking->status !== 'checked_in') {
                return redirect()->route('bookings.show', $booking)
                    ->with('error', 'Only checked-in bookings can be checked out.');
            }
        }

        $activeServices = ExtraService::active()->orderBy('name')->get();

        // Pre-calculate bill summary (no discount yet — applied at submit)
        $billSummary = $booking ? $this->buildBillSummary($booking) : null;

        // Discount permission context passed to view
        $discountEnabled  = Setting::discountEnabled();
        $maxDiscountRate  = Setting::maxDiscountRate();
        $isAdmin          = auth()->user()->isAdmin();

        // Determine if the current user can apply any discount at all
        $canDiscount = $isAdmin || ($discountEnabled && $maxDiscountRate > 0);

        // List of all currently checked-in bookings
        $checkedInBookings = Booking::with(['customer:id,name', 'room:id,room_number'])
            ->where('status', 'checked_in')
            ->orderBy('check_in_date')
            ->get();

        return view('check-outs.create', compact(
            'booking', 'activeServices', 'billSummary', 'checkedInBookings',
            'isAdmin', 'discountEnabled', 'maxDiscountRate', 'canDiscount'
        ));
    }

    // POST /check-outs/services  — add extra service to a checked-in booking
    public function addService(Request $request)
    {
        $data = $request->validate([
            'booking_id' => ['required', 'exists:bookings,id'],
            'service_id' => ['required', 'exists:extra_services,id'],
            'quantity'   => ['required', 'integer', 'min:1'],
        ]);

        $booking = Booking::findOrFail($data['booking_id']);

        if ($booking->status !== 'checked_in') {
            return redirect()
                ->route('check-outs.create', ['booking_id' => $data['booking_id']])
                ->with('error', 'Extra services can only be added to checked-in bookings.');
        }

        $service = ExtraService::findOrFail($data['service_id']);

        BookingService::create([
            'booking_id'  => $booking->id,
            'service_id'  => $service->id,
            'quantity'    => $data['quantity'],
            'unit_price'  => $service->price,
            'total_price' => $service->price * $data['quantity'],
        ]);

        return redirect()
            ->route('check-outs.create', ['booking_id' => $data['booking_id']])
            ->with('success', "Added {$data['quantity']}× {$service->name}.");
    }

    // DELETE /check-outs/services/:id  — remove extra service
    public function removeService(BookingService $bookingService)
    {
        $bookingId = $bookingService->booking_id;
        $bookingService->delete();

        return redirect()
            ->route('check-outs.create', ['booking_id' => $bookingId])
            ->with('success', 'Service removed.');
    }

    // POST /check-outs
    public function store(Request $request)
    {
        $data = $request->validate([
            'booking_id'      => ['required', 'exists:bookings,id'],
            'notes'           => ['nullable', 'string'],
            'discount_type'   => ['nullable', 'in:percent,fixed'],
            'discount_value'  => ['nullable', 'numeric', 'min:0'],
            'discount_reason' => ['nullable', 'string', 'max:255'],
        ]);

        $booking = Booking::with([
                'room.roomType',
                'bookingServices.service',
                'customer',
                'checkIn',
                'payments',
            ])
            ->findOrFail($data['booking_id']);

        if ($booking->status !== 'checked_in') {
            return back()->with('error', 'Only checked-in bookings can be checked out.');
        }

        if ($booking->checkOut()->exists()) {
            return back()->with('error', 'This booking has already been checked out.');
        }

        // ── Discount permission check ─────────────────────────────────────────
        $isAdmin         = auth()->user()->isAdmin();
        $discountEnabled = Setting::discountEnabled();
        $maxDiscountRate = Setting::maxDiscountRate();

        $discountType   = $data['discount_type']  ?? 'percent';
        $discountValue  = (float)($data['discount_value'] ?? 0);
        $discountReason = trim($data['discount_reason'] ?? '');

        if ($discountValue > 0) {
            if (!$isAdmin && !$discountEnabled) {
                // Discounts are globally disabled for staff
                return back()->withInput()
                    ->with('error', 'Discounts are disabled. Contact an admin to apply a discount.');
            }

            if (!$isAdmin && $maxDiscountRate <= 0) {
                // Enabled but max is 0 — still blocked
                return back()->withInput()
                    ->with('error', 'Staff discount limit is set to 0%. Contact an admin.');
            }

            if (!$isAdmin) {
                // Validate staff doesn't exceed the allowed limit
                $subtotal = (float) $booking->room_total
                          + $booking->bookingServices->sum('total_price');

                if ($discountType === 'percent' && $discountValue > $maxDiscountRate) {
                    return back()->withInput()
                        ->with('error', "Staff discount limit is {$maxDiscountRate}%. You entered {$discountValue}%.");
                }

                if ($discountType === 'fixed' && $subtotal > 0) {
                    $impliedPct = ($discountValue / $subtotal) * 100;
                    if ($impliedPct > $maxDiscountRate) {
                        $maxFixed = round($subtotal * $maxDiscountRate / 100, 2);
                        return back()->withInput()
                            ->with('error', "Staff discount limit is {$maxDiscountRate}% (max \${$maxFixed}). You entered \${$discountValue}.");
                    }
                }
            }
        }

        $bill = $this->buildBillSummary($booking, $discountType, $discountValue);

        $checkOut = DB::transaction(function () use ($booking, $data, $bill, $discountReason) {
            $checkOut = CheckOut::create([
                'booking_id'     => $booking->id,
                'check_out_time' => now(),
                'extra_total'    => $bill['extra_total'],
                'checked_out_by' => auth()->id(),
                'notes'          => $data['notes'] ?? null,
            ]);

            Invoice::create([
                'booking_id'        => $booking->id,
                'room_total'        => $booking->room_total,
                'extra_total'       => $bill['extra_total'],
                'subtotal'          => $bill['subtotal'],
                'discount_rate'     => $bill['discount_rate'],
                'discount_amount'   => $bill['discount_amount'],
                'discount_reason'   => $discountReason,
                'discounted_total'  => $bill['discounted_total'],
                'tax_rate'          => $bill['tax_rate'] * 100,
                'tax_amount'        => $bill['tax_amount'],
                'grand_total'       => $bill['grand_total'],
                'settlement_amount' => $bill['settlement'],
                'status'            => 'draft',
                'created_by'        => auth()->id(),
            ]);

            $booking->update(['status' => 'checked_out']);

            RoomStatusLog::create([
                'room_id'    => $booking->room_id,
                'old_status' => 'occupied',
                'new_status' => 'cleaning',
                'changed_by' => auth()->id(),
                'changed_at' => now(),
            ]);

            $booking->room->update(['status' => 'cleaning']);

            return $checkOut;
        });

        return redirect()->route('invoices.show', $booking->fresh()->invoice)
            ->with('success', 'Check-out complete. Settlement due: $' . number_format($bill['settlement'], 2));
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function buildBillSummary(
        Booking $booking,
        string $discountType = 'percent',
        float $discountValue = 0
    ): array {
        $taxRate    = Setting::taxRate() / 100;
        $extraTotal = $booking->bookingServices->sum('total_price');
        $subtotal   = (float) $booking->room_total + (float) $extraTotal;

        // Calculate discount amount and rate
        if ($discountValue > 0) {
            if ($discountType === 'percent') {
                $discountRate   = min($discountValue, 100);
                $discountAmount = round($subtotal * $discountRate / 100, 2);
            } else {
                $discountAmount = min($discountValue, $subtotal);
                $discountRate   = $subtotal > 0 ? round($discountAmount / $subtotal * 100, 2) : 0;
            }
        } else {
            $discountRate   = 0;
            $discountAmount = 0;
        }

        $discountedTotal = round($subtotal - $discountAmount, 2);
        $taxAmount       = round($discountedTotal * $taxRate, 2);
        $grandTotal      = round($discountedTotal + $taxAmount, 2);

        // Deposit from paid deposit payments
        $deposit = (float) $booking->payments
                       ->where('status', 'paid')
                       ->where('payment_type', 'deposit')
                       ->sum('amount');

        $settlement = max(0, round($grandTotal - $deposit, 2));

        return [
            'tax_rate'         => $taxRate,
            'extra_total'      => $extraTotal,
            'subtotal'         => $subtotal,
            'discount_type'    => $discountType,
            'discount_rate'    => $discountRate,
            'discount_amount'  => $discountAmount,
            'discounted_total' => $discountedTotal,
            'tax_amount'       => $taxAmount,
            'grand_total'      => $grandTotal,
            'deposit'          => $deposit,
            'settlement'       => $settlement,
        ];
    }
}
