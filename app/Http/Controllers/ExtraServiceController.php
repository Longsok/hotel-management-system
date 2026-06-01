<?php

namespace App\Http\Controllers;

use App\Models\ExtraService;
use Illuminate\Http\Request;

class ExtraServiceController extends Controller
{
    // GET /extra-services
    public function index(Request $request)
    {
        $services = ExtraService::query()
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('extra-services.index', compact('services'));
    }

    // GET /extra-services/create  [admin only]
    public function create()
    {
        return view('extra-services.create');
    }

    // POST /extra-services  [admin only]
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255', 'unique:extra_services,name'],
            'description' => ['nullable', 'string'],
            'price'       => ['required', 'numeric', 'min:0'],
            // BUG FIX: removed 'unit' — no column in DB, no field in the form.
            'is_active'   => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);

        $service = ExtraService::create($data);

        return redirect()->route('extra-services.index')
            ->with('success', "Service \"{$service->name}\" created.");
    }

    // GET /extra-services/:id/edit  [admin only]
    public function edit(ExtraService $extraService)
    {
        return view('extra-services.edit', compact('extraService'));
    }

    // PUT /extra-services/:id  [admin only]
    public function update(Request $request, ExtraService $extraService)
    {
        $data = $request->validate([
            'name'        => ["sometimes", "string", "max:255", "unique:extra_services,name,{$extraService->id}"],
            'description' => ['nullable', 'string'],
            'price'       => ['sometimes', 'numeric', 'min:0'],
            // BUG FIX: removed 'unit' — no column in DB, no field in the form.
            'is_active'   => ['boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active');

        $extraService->update($data);

        return redirect()->route('extra-services.index')
            ->with('success', "Service \"{$extraService->name}\" updated.");
    }

    // DELETE /extra-services/:id  [admin only]
    public function destroy(ExtraService $extraService)
    {
        if ($extraService->bookingServices()->exists()) {
            return back()->with('error', 'Cannot delete a service that has been used in bookings.');
        }

        $name = $extraService->name;
        $extraService->delete();

        return redirect()->route('extra-services.index')
            ->with('success', "Service \"{$name}\" deleted.");
    }
}
