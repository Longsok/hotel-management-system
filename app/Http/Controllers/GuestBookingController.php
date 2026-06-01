<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Room;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class GuestBookingController extends Controller
{
    // GET /book
    public function index()
    {
        $hotel = [
            'name'    => Setting::hotelName(),
            'tagline' => Setting::hotelTagline(),
            'address' => Setting::hotelAddress(),
            'city'    => Setting::hotelCity(),
            'phone'   => Setting::hotelPhone(),
            'email'   => Setting::hotelEmail(),
            'symbol'  => Setting::currencySymbol(),
        ];

        $depositRate     = Setting::depositRate();
        $checkInTime     = Setting::checkInTime();
        $checkOutTime    = Setting::checkOutTime();
        $stripePublicKey = config('services.stripe.key');

        return view('guest.booking', compact(
            'hotel', 'depositRate', 'checkInTime', 'checkOutTime', 'stripePublicKey'
        ));
    }

    // GET /book/rooms?check_in=&check_out=
    public function availableRooms(Request $request)
    {
        $request->validate([
            'check_in'  => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
        ]);

        $rooms = Room::with(['roomType', 'amenities'])
            ->where('status', '!=', 'maintenance')
            ->where('status', '!=', 'out_of_service')
            ->get()
            ->filter(fn($r) => $r->isAvailableForDates($request->check_in, $request->check_out))
            ->map(function ($room) {
                return [
                    'id'          => $room->id,
                    'room_number' => $room->room_number,
                    'floor'       => $room->floor,
                    'image'       => $room->image ? asset('storage/' . $room->image) : null,
                    'type'        => $room->roomType?->name,
                    'description' => $room->roomType?->description,
                    'base_price'  => (float) $room->roomType?->base_price,
                    'max_people'  => $room->roomType?->max_people,
                    'amenities'   => $room->amenities->pluck('name'),
                ];
            })
            ->values();

        return response()->json($rooms);
    }

    // POST /book/intent — create Stripe PaymentIntent + store pending data in session
    public function createIntent(Request $request)
    {
        $data = $request->validate([
            'room_id'     => ['required', 'exists:rooms,id'],
            'check_in'    => ['required', 'date', 'after_or_equal:today'],
            'check_out'   => ['required', 'date', 'after:check_in'],
            'name'        => ['required', 'string', 'max:100'],
            'email'       => ['required', 'email', 'max:100'],
            'phone'       => ['required', 'string', 'max:30'],
            'address'     => ['nullable', 'string', 'max:255'],
            'nationality' => ['nullable', 'string', 'max:100'],
            'requests'    => ['nullable', 'string', 'max:500'],
        ]);

        $room = Room::with('roomType')->findOrFail($data['room_id']);

        if (!$room->isAvailableForDates($data['check_in'], $data['check_out'])) {
            return response()->json(['error' => 'Room is no longer available for these dates.'], 422);
        }

        $nights    = (int) now()->parse($data['check_in'])->diffInDays($data['check_out']);
        $roomTotal = round((float) $room->roomType->base_price * $nights, 2);
        $deposit   = round($roomTotal * Setting::depositRate() / 100, 2);
        $depositCents = (int) round($deposit * 100);

        if ($depositCents < 50) {
            return response()->json(['error' => 'Deposit amount is too small to process.'], 422);
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $intent = PaymentIntent::create([
            'amount'   => $depositCents,
            'currency' => config('services.stripe.currency', 'usd'),
            'metadata' => [
                'room_id'    => $room->id,
                'check_in'   => $data['check_in'],
                'check_out'  => $data['check_out'],
                'guest_name' => $data['name'],
                'guest_email'=> $data['email'],
            ],
        ]);

        // Store booking data in session until payment is confirmed
        session([
            'guest_booking' => [
                'room_id'      => $room->id,
                'check_in'     => $data['check_in'],
                'check_out'    => $data['check_out'],
                'nights'       => $nights,
                'room_total'   => $roomTotal,
                'deposit'      => $deposit,
                'name'         => $data['name'],
                'email'        => $data['email'],
                'phone'        => $data['phone'],
                'address'      => $data['address'] ?? '',
                'nationality'  => $data['nationality'] ?? '',
                'requests'     => $data['requests'] ?? '',
                'intent_id'    => $intent->id,
            ],
        ]);

        return response()->json([
            'client_secret' => $intent->client_secret,
            'deposit'       => $deposit,
            'nights'        => $nights,
            'room_total'    => $roomTotal,
        ]);
    }

    // GET /book/history
    public function history()
    {
        $guest = Auth::guard('customer')->user();

        $bookings = $guest->bookings()
            ->with(['room.roomType', 'payments', 'invoice'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('guest.history', compact('guest', 'bookings'));
    }

    // GET /book/complete?payment_intent=xxx
    public function complete(Request $request)
    {
        $pending = session('guest_booking');

        if (!$pending || !$request->payment_intent) {
            return redirect()->route('guest.booking')->with('error', 'Session expired. Please try again.');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        try {
            $intent = PaymentIntent::retrieve($request->payment_intent);
        } catch (\Exception $e) {
            return redirect()->route('guest.booking')->with('error', 'Payment verification failed.');
        }

        if ($intent->status !== 'succeeded') {
            return redirect()->route('guest.booking')->with('error', 'Payment was not completed. Please try again.');
        }

        // Idempotency: if already processed for this intent, just show confirmation
        $existing = Booking::where('stripe_intent_id', $intent->id)->first();
        if ($existing) {
            session()->forget('guest_booking');
            return view('guest.confirmation', ['booking' => $existing]);
        }

        // Create or find customer
        $customer = Customer::firstOrCreate(
            ['email' => $pending['email']],
            [
                'name'        => $pending['name'],
                'phone'       => $pending['phone'],
                'address'     => $pending['address'],
                'nationality' => $pending['nationality'],
                'nationality' => '',
                'status'      => 'active',
            ]
        );

        // Create the booking
        $booking = Booking::create([
            'customer_id'      => $customer->id,
            'room_id'          => $pending['room_id'],
            'check_in_date'    => $pending['check_in'],
            'check_out_date'   => $pending['check_out'],
            'nights'           => $pending['nights'],
            'room_price'       => round($pending['room_total'] / $pending['nights'], 2),
            'room_total'       => $pending['room_total'],
            'booking_source'   => 'online',
            'status'           => 'confirmed',
            'special_requests' => $pending['requests'],
            'stripe_intent_id' => $intent->id,
            'booking_number'   => 'BK-' . strtoupper(Str::random(10)),
            'created_by'       => null,
        ]);

        // Record the deposit payment
        Payment::create([
            'booking_id'   => $booking->id,
            'amount'       => $pending['deposit'],
            'payment_type' => 'deposit',
            'method'       => 'stripe',
            'status'       => 'paid',
            'paid_at'      => now(),
            'reference'    => $intent->id,
            'notes'        => 'Online deposit via Stripe',
            'recorded_by'  => null,
        ]);

        session()->forget('guest_booking');

        return view('guest.confirmation', compact('booking', 'customer', 'pending'));
    }
}