<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    // GET /bookings
    public function index(Request $request)
    {
        $bookings = Booking::with([
                'customer:id,name,phone,email',
                'room:id,room_number,floor,room_type_id',
                'room.roomType:id,name',
            ])
            ->when($request->search, fn($q) => $q->where(function($q) use ($request) {
                $q->whereHas('customer', fn($q) => $q->where('name', 'like', "%{$request->search}%"))
                  ->orWhere('booking_number', 'like', "%{$request->search}%");
            }))
            ->when($request->status,    fn($q) => $q->where('status', $request->status))
            ->when($request->source,    fn($q) => $q->where('booking_source', $request->source))
            ->when($request->check_in,  fn($q) => $q->whereDate('check_in_date', '>=', $request->check_in))
            ->when($request->check_out, fn($q) => $q->whereDate('check_out_date', '<=', $request->check_out))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $statuses = ['pending','confirmed','checked_in','checked_out','cancelled','no_show'];

        return view('bookings.index', compact('bookings', 'statuses'));
    }

    // GET /bookings/create
    public function create(Request $request)
    {
        $customers = Customer::active()->orderBy('name')->get(['id','name','phone','email']);
        $rooms     = Room::with('roomType')->where('status','available')->orderBy('floor')->orderBy('room_number')->get();

        return view('bookings.create', compact('customers', 'rooms'));
    }

    // POST /bookings
    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_id'      => ['required', 'exists:customers,id'],
            'room_id'          => ['required', 'exists:rooms,id'],
            'check_in_date'    => ['required', 'date', 'after_or_equal:today'],
            'check_out_date'   => ['required', 'date', 'after:check_in_date'],
            'booking_source'   => ['nullable', 'in:walk_in,online'],
            'special_requests' => ['nullable', 'string'],
        ]);

        $room = Room::with('roomType')->findOrFail($data['room_id']);

        // Double-booking guard
        $overlap = Booking::where('room_id', $data['room_id'])
            ->whereIn('status', ['confirmed','checked_in'])
            ->where(function ($q) use ($data) {
                $q->whereBetween('check_in_date',  [$data['check_in_date'], $data['check_out_date']])
                  ->orWhereBetween('check_out_date', [$data['check_in_date'], $data['check_out_date']])
                  ->orWhere(fn($q) => $q->where('check_in_date', '<=', $data['check_in_date'])
                                        ->where('check_out_date', '>=', $data['check_out_date']));
            })->exists();

        if ($overlap) {
            return back()->withInput()->with('error', 'This room is already booked for the selected dates.');
        }

        if (! $room->isAvailable()) {
            return back()->withInput()->with('error', "Room {$room->room_number} is not available (status: {$room->status}).");
        }

        $booking = DB::transaction(function () use ($data, $room) {
            $booking = Booking::create([
                ...$data,
                'room_price'     => $room->roomType->base_price,
                'status'         => 'pending',
                'booking_source' => $data['booking_source'] ?? 'walk_in',
                'created_by'     => auth()->id(),
            ]);
            $room->update(['status' => 'reserved']);
            return $booking;
        });

        return redirect()->route('bookings.show', $booking)
            ->with('success', "Booking {$booking->booking_number} created successfully.");
    }

    // GET /bookings/available-rooms  (AJAX)
    public function availableRooms(Request $request)
    {
        $request->validate([
            'check_in'  => ['required','date'],
            'check_out' => ['required','date','after:check_in'],
        ]);

        $bookedIds = Booking::whereIn('status', ['confirmed','checked_in'])
            ->where(function ($q) use ($request) {
                $q->whereBetween('check_in_date',  [$request->check_in, $request->check_out])
                  ->orWhereBetween('check_out_date', [$request->check_in, $request->check_out])
                  ->orWhere(fn($q) => $q->where('check_in_date', '<=', $request->check_in)
                                        ->where('check_out_date', '>=', $request->check_out));
            })->pluck('room_id');

        $rooms = Room::with('roomType')
            ->where('status','available')
            ->whereNotIn('id', $bookedIds)
            ->orderBy('floor')->orderBy('room_number')
            ->get();

        return response()->json($rooms);
    }

    // GET /bookings/:id
    public function show(Booking $booking)
    {
        $booking->load([
            'customer',
            'room.roomType',
            'room.amenities',
            'checkIn.checkedInBy:id,name',
            'checkOut.checkedOutBy:id,name',
            'bookingServices.service',
            'payments',
            'invoice',
            'createdBy:id,name',
        ]);

        return view('bookings.show', compact('booking'));
    }

    // GET /bookings/:id/edit
    public function edit(Booking $booking)
    {
        if (in_array($booking->status, ['checked_in','checked_out','cancelled'])) {
            return back()->with('error', "Cannot edit a booking with status: {$booking->status}.");
        }
        $booking->load(['customer','room.roomType']);
        return view('bookings.edit', compact('booking'));
    }

    // PUT /bookings/:id
    public function update(Request $request, Booking $booking)
    {
        if (in_array($booking->status, ['checked_in','checked_out','cancelled'])) {
            return back()->with('error', "Cannot edit a booking with status: {$booking->status}.");
        }

        $data = $request->validate([
            'check_in_date'    => ['sometimes','date'],
            'check_out_date'   => ['sometimes','date','after:check_in_date'],
            'special_requests' => ['nullable','string'],
        ]);

        $booking->update($data);

        return redirect()->route('bookings.show', $booking)->with('success', 'Booking updated.');
    }

    // POST /bookings/:id/confirm
    public function confirm(Booking $booking)
    {
        if ($booking->status !== 'pending') {
            return back()->with('error', 'Only pending bookings can be confirmed.');
        }
        $booking->update(['status' => 'confirmed']);
        return back()->with('success', "Booking {$booking->booking_number} confirmed.");
    }

    // POST /bookings/:id/cancel
    public function cancel(Booking $booking)
    {
        if (in_array($booking->status, ['checked_in','checked_out'])) {
            return back()->with('error', 'Cannot cancel a checked-in or completed booking.');
        }
        DB::transaction(function () use ($booking) {
            $booking->update(['status' => 'cancelled']);
            $booking->room->update(['status' => 'available']);
        });
        return back()->with('success', 'Booking cancelled. Room is now available.');
    }
}
