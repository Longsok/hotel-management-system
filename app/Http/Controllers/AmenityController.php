<?php

namespace App\Http\Controllers;

use App\Models\Amenity;
use App\Models\Room;
use Illuminate\Http\Request;

class AmenityController extends Controller
{
    // GET /amenities  [admin only]
    public function index()
    {
        $amenities = Amenity::withCount('rooms')->orderBy('name')->paginate(20);

        return view('amenities.index', compact('amenities'));
    }

    // GET /amenities/create  [admin only]
    public function create()
    {
        return view('amenities.create');
    }

    // POST /amenities  [admin only]
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255', 'unique:amenities,name'],
            'description' => ['nullable', 'string'],
            'icon'        => ['nullable', 'string', 'max:100'],
        ]);

        Amenity::create($data);

        return redirect()->route('amenities.index')
            ->with('success', 'Amenity created.');
    }

    // GET /amenities/:id/edit  [admin only]
    public function edit(Amenity $amenity)
    {
        return view('amenities.edit', compact('amenity'));
    }

    // PUT /amenities/:id  [admin only]
    public function update(Request $request, Amenity $amenity)
    {
        $data = $request->validate([
            'name'        => ["sometimes", "string", "max:255", "unique:amenities,name,{$amenity->id}"],
            'description' => ['nullable', 'string'],
            'icon'        => ['nullable', 'string', 'max:100'],
        ]);

        $amenity->update($data);

        return redirect()->route('amenities.index')
            ->with('success', 'Amenity updated.');
    }

    // DELETE /amenities/:id  [admin only]
    public function destroy(Amenity $amenity)
    {
        $name = $amenity->name;
        $amenity->rooms()->detach();  // clean pivot before deleting
        $amenity->delete();

        return redirect()->route('amenities.index')
            ->with('success', "Amenity \"{$name}\" deleted.");
    }

    // POST /rooms/:room/amenities  [admin only] — sync amenities from room edit form
    public function syncRoom(Request $request, Room $room)
    {
        $data = $request->validate([
            'amenity_ids'   => ['nullable', 'array'],
            'amenity_ids.*' => ['exists:amenities,id'],
        ]);

        $room->amenities()->sync($data['amenity_ids'] ?? []);

        return back()->with('success', 'Room amenities updated.');
    }
}
