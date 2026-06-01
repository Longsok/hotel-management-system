<?php

namespace App\Http\Controllers;

use App\Models\RoomType;
use Illuminate\Http\Request;

class RoomTypeController extends Controller
{
    // GET /room-types
    public function index()
    {
        $roomTypes = RoomType::withCount('rooms')->orderBy('name')->paginate(15);

        return view('room-types.index', compact('roomTypes'));
    }

    // GET /room-types/create  [admin only]
    public function create()
    {
        return view('room-types.create');
    }

    // POST /room-types  [admin only]
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255', 'unique:room_types,name'],
            'description' => ['nullable', 'string'],
            'base_price'  => ['required', 'numeric', 'min:0'],
            'max_people'  => ['required', 'integer', 'min:1'],
        ]);

        $roomType = RoomType::create($data);

        return redirect()->route('room-types.index')
            ->with('success', "Room type \"{$roomType->name}\" created.");
    }

    // GET /room-types/:id
    public function show(RoomType $roomType)
    {
        $roomType->load(['rooms' => fn($q) => $q->with('amenities')->orderBy('room_number')]);

        return view('room-types.show', compact('roomType'));
    }

    // GET /room-types/:id/edit  [admin only]
    public function edit(RoomType $roomType)
    {
        return view('room-types.edit', compact('roomType'));
    }

    // PUT /room-types/:id  [admin only]
    public function update(Request $request, RoomType $roomType)
    {
        $data = $request->validate([
            'name'        => ["sometimes", "string", "max:255", "unique:room_types,name,{$roomType->id}"],
            'description' => ['nullable', 'string'],
            'base_price'  => ['sometimes', 'numeric', 'min:0'],
            'max_people'  => ['sometimes', 'integer', 'min:1'],
        ]);

        $roomType->update($data);

        return redirect()->route('room-types.show', $roomType)
            ->with('success', 'Room type updated.');
    }

    // DELETE /room-types/:id  [admin only]
    public function destroy(RoomType $roomType)
    {
        if ($roomType->rooms()->exists()) {
            return back()->with('error', 'Cannot delete a room type that has rooms assigned.');
        }

        $name = $roomType->name;
        $roomType->delete();

        return redirect()->route('room-types.index')
            ->with('success', "Room type \"{$name}\" deleted.");
    }
}
