<?php

namespace App\Http\Controllers;

use App\Models\Amenity;
use App\Models\Room;
use App\Models\RoomStatusLog;
use App\Models\RoomType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class RoomController extends Controller
{
    // GET /rooms
    public function index(Request $request)
    {
        $rooms = Room::with(['roomType', 'amenities'])
            ->when($request->status, fn($q) =>
                $q->where('status', $request->status)
            )
            ->when($request->floor, fn($q) =>
                $q->where('floor', $request->floor)
            )
            ->when($request->type, fn($q) =>
                $q->whereHas('roomType', fn($q) =>
                    $q->where('name', $request->type)
                )
            )
            ->orderBy('floor')
            ->orderBy('room_number')
            ->paginate(15)
            ->withQueryString();
        
        $roomTypes = RoomType::orderBy('name')->get();

        $statuses = Room::statuses();

        $roomsByFloor = $rooms->getCollection()->groupBy('floor');

        $floors = Room::select('floor')
            ->distinct()
            ->orderBy('floor')
            ->pluck('floor');

        // Count per status across ALL rooms (ignoring active filters)
        $roomCounts = Room::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $totalRooms = Room::count();

        return view('rooms.index', compact(
            'rooms',
            'roomTypes',
            'statuses',
            'roomsByFloor',
            'floors',
            'roomCounts',
            'totalRooms'
        ));
    }

    // GET /rooms/create
    public function create()
    {
        $roomTypes = RoomType::orderBy('name')->get();

        $amenities = Amenity::orderBy('name')->get();

        $floors = [1,2,3,4,5,6,7,8];

        $statuses = Room::statuses();

        return view('rooms.create', compact(
            'roomTypes',
            'amenities',
            'floors',
            'statuses'
        ));
    }

    // POST /rooms
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_number'     => 'required|string|max:50|unique:rooms,room_number',
            'room_type_id'    => 'required|exists:room_types,id',
            'floor'           => 'required|integer',
            'status'          => 'required|string',
            'notes'           => 'nullable|string',
            'image'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'amenity_ids'     => 'nullable|array',
            'amenity_ids.*'   => 'exists:amenities,id',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('rooms', 'public');
        }

        $room = Room::create($validated);

        if ($request->has('amenity_ids')) {
            $room->amenities()->sync($request->amenity_ids);
        }

        return redirect()
            ->route('rooms.index')
            ->with('success', 'Room created successfully.');
    }

    // GET /rooms/:id
    public function show(Room $room)
    {
        $room->load([
            'roomType',
            'amenities',
            'activeBooking.customer',
            'statusLogs' => fn($q) =>
                $q->with('changedBy:id,name')
                    ->latest('changed_at')
                    ->limit(10)
        ]);

        $statuses = Room::statuses();
        return view('rooms.show', compact('room', 'statuses'));
    }

    // GET /rooms/:id/edit
    public function edit(Room $room)
    {
        $room->load('amenities');

        $floors = [1,2,3,4,5,6,7,8];

        $roomTypes = RoomType::orderBy('name')->get();

        $amenities = Amenity::orderBy('name')->get();

        $statuses = Room::statuses();

        return view('rooms.edit', compact(
            'room',
            'roomTypes',
            'amenities',
            'floors',
            'statuses'
        ));
    }

    // PUT /rooms/:id
    public function update(Request $request, Room $room)
    {
        $data = $request->validate([
            'room_number'   => ['sometimes', 'string', 'unique:rooms,room_number,' . $room->id],
            'floor'         => ['sometimes', 'string'],
            'room_type_id'  => ['sometimes', 'exists:room_types,id'],
            'status'        => ['sometimes', 'in:' . implode(',', Room::statuses())],
            'notes'         => ['nullable', 'string'],
            'image'         => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'amenity_ids'   => ['nullable', 'array'],
            'amenity_ids.*' => ['exists:amenities,id'],
        ]);

        if ($request->hasFile('image')) {
            // Delete old image if it exists
            if ($room->image) {
                Storage::disk('public')->delete($room->image);
            }
            $data['image'] = $request->file('image')->store('rooms', 'public');
        }

        if ($request->boolean('remove_image') && $room->image) {
            Storage::disk('public')->delete($room->image);
            $data['image'] = null;
        }

        // Log status change
        if (isset($data['status']) && $data['status'] !== $room->status) {
            RoomStatusLog::create([
                'room_id'    => $room->id,
                'old_status' => $room->status,
                'new_status' => $data['status'],
                'changed_by' => auth()->id(),
                'changed_at' => now(),
            ]);
        }

        $room->update($data);

        if (array_key_exists('amenity_ids', $data)) {
            $room->amenities()->sync($data['amenity_ids'] ?? []);
        }

        return redirect()
            ->route('rooms.show', $room)
            ->with('success', 'Room updated successfully.');
    }

    // DELETE /rooms/:id
    public function destroy(Room $room)
    {
        if ($room->bookings()->active()->exists()) {
            return back()->with(
                'error',
                'Cannot delete a room with active bookings.'
            );
        }

        $roomNumber = $room->room_number;

        $room->delete();

        return redirect()
            ->route('rooms.index')
            ->with('success', "Room {$roomNumber} deleted.");
    }

    // GET /rooms/:id/logs
    public function logs(Room $room)
    {
        $logs = $room->statusLogs()
            ->with('changedBy:id,name')
            ->orderByDesc('changed_at')
            ->paginate(20);

        return view('rooms.logs', compact('room', 'logs'));
    }

    // PATCH /rooms/:id/status
    public function updateStatus(Request $request, Room $room)
    {
        $data = $request->validate([
            'status' => ['required', 'in:' . implode(',', Room::statuses())],
            'notes'  => ['nullable', 'string'],
        ]);

        if ($data['status'] !== $room->status) {
            RoomStatusLog::create([
                'room_id'    => $room->id,
                'old_status' => $room->status,
                'new_status' => $data['status'],
                'changed_by' => auth()->id(),
                'notes'      => $data['notes'] ?? null,
                'changed_at' => now(),
            ]);
        }

        $room->update([
            'status' => $data['status']
        ]);

        return back()->with(
            'success',
            "Room status updated to {$data['status']}."
        );
    }
}