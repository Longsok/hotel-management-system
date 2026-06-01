<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\CheckIn;
use App\Models\Payment;
use App\Models\RoomStatusLog;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckInController extends Controller
{
    // GET /check-ins
    public function index(Request $request)
    {
        $checkIns = CheckIn::with([
                'booking.customer:id,name,phone',
                'booking.room:id,room_number,floor',
                'checkedInBy:id,name',
            ])
            ->when($request->date, fn($q) => $q->whereDate('check_in_time', $request->date))
            ->orderByDesc('check_in_time')
            ->paginate(15)
            ->withQueryString();

        return view('check-ins.index', compact('checkIns'));
    }

    // GET /check-ins/:id
    public function show(CheckIn $checkIn)
    {
        $checkIn->load(['booking.customer', 'booking.room.roomType', 'checkedInBy']);

        return view('check-ins.show', compact('checkIn'));
    }

    // GET /check-ins/create?booking_id=
    public function create(Request $request)
    {
        $booking = null;

        if ($request->booking_id) {
            $booking = Booking::with(['customer', 'room.roomType'])
                ->findOrFail($request->booking_id);

            if ($booking->status !== 'confirmed') {
                return redirect()->route('bookings.show', $booking)
                    ->with('error', 'Only confirmed bookings can be checked in.');
            }
        }

        // List of today's confirmed bookings for selection
        $confirmedToday = Booking::with(['customer:id,name', 'room:id,room_number'])
            ->where('status', 'confirmed')
            ->whereDate('check_in_date', today())
            ->orderBy('check_in_date')
            ->get();

        return view('check-ins.create', compact('booking', 'confirmedToday'));
    }

    // POST /check-ins
    public function store(Request $request)
    {
        $data = $request->validate([
            'booking_id' => ['required', 'exists:bookings,id'],
            'notes'      => ['nullable', 'string'],
        ]);

        $booking = Booking::with('room.roomType')->findOrFail($data['booking_id']);

        if ($booking->status !== 'confirmed') {
            return back()->withInput()
                ->with('error', 'Only confirmed bookings can be checked in.');
        }

        if ($booking->checkIn()->exists()) {
            return back()->with('error', 'This booking has already been checked in.');
        }

        // Deposit = 50% of room total only — tax is NOT included here.
        // Tax will be applied once on the full bill at final settlement.
        $depositAmount = round($booking->room_total * 0.5, 2);

        $checkIn = DB::transaction(function () use ($booking, $data, $depositAmount) {
            $checkIn = CheckIn::create([
                'booking_id'     => $booking->id,
                'check_in_time'  => now(),
                'deposit_amount' => $depositAmount, // stored for display; actual payment is collected separately
                'checked_in_by'  => auth()->id(),
                'notes'          => $data['notes'] ?? null,
            ]);

            $booking->update(['status' => 'checked_in']);

            RoomStatusLog::create([
                'room_id'    => $booking->room_id,
                'old_status' => $booking->room->status,
                'new_status' => 'occupied',
                'changed_by' => auth()->id(),
                'changed_at' => now(),
            ]);

            $booking->room->update(['status' => 'occupied']);

            return $checkIn;
        });

        // BUG FIX: The old code auto-created a cash Payment record here.
        // This caused a duplicate deposit if the guest paid online via Stripe
        // beforehand, and it forced cash as the method with no choice.
        //
        // Instead, we redirect to the payment form so staff can collect the
        // deposit via cash OR Stripe, and the Payment is created once there.
        //
        // If a deposit was already paid (e.g. online booking), skip collection.
        $depositAlreadyPaid = $booking->fresh()->payments()
            ->where('payment_type', 'deposit')
            ->where('status', 'paid')
            ->exists();

        if ($depositAlreadyPaid) {
            return redirect()->route('check-ins.show', $checkIn)
                ->with('success', "Check-in completed. Deposit was already paid.");
        }

        return redirect()
            ->route('payments.create', ['booking_id' => $booking->id, 'type' => 'deposit'])
            ->with('success', "Check-in recorded. Please collect the deposit of $" . number_format($depositAmount, 2) . " (50% of room total, tax applied at checkout).");
    }
}
