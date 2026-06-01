<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    // GET /customers
    public function index(Request $request)
    {
        $customers = Customer::query()
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('name',     'like', "%{$request->search}%")
                  ->orWhere('email',  'like', "%{$request->search}%")
                  ->orWhere('phone',  'like', "%{$request->search}%")
                  ->orWhere('passport', 'like', "%{$request->search}%")
                  ->orWhere('id_card',  'like', "%{$request->search}%");
            }))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->withCount('bookings')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('customers.index', compact('customers'));
    }

    // GET /customers/create
    public function create()
    {
        return view('customers.create');
    }

    // POST /customers
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'email', 'unique:customers,email'],
            'phone'       => ['nullable', 'string', 'max:20'],
            'nationality' => ['required', 'string'],
            'id_card'     => ['nullable', 'string'],
            'passport'    => ['nullable', 'string'],
            'address'     => ['nullable', 'string'],
        ]);

        // Password is intentionally null for staff-created customers.
        // When the online booking feature is implemented, a password
        // (or invitation flow) can be added here.
        $data['password'] = null;

        $customer = Customer::create($data);

        return redirect()->route('customers.show', $customer)
            ->with('success', "Customer \"{$customer->name}\" added.");
    }

    // GET /customers/:id
    public function show(Customer $customer)
    {
        $customer->load(['bookings' => fn($q) => $q->with('room.roomType')->latest()->take(10)]);

        return view('customers.show', compact('customer'));
    }

    // GET /customers/:id/edit
    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    // PUT /customers/:id
    public function update(Request $request, Customer $customer)
    {
        $data = $request->validate([
            'name'        => ['sometimes', 'string', 'max:255'],
            'email'       => ["sometimes", "email", "unique:customers,email,{$customer->id}"],
            'phone'       => ['nullable', 'string', 'max:20'],
            'nationality' => ['sometimes', 'string'],
            'id_card'     => ['nullable', 'string'],
            'passport'    => ['nullable', 'string'],
            'address'     => ['nullable', 'string'],
            'status'      => ['in:active,inactive'],
        ]);

        $customer->update($data);

        return redirect()->route('customers.show', $customer)
            ->with('success', 'Customer updated.');
    }

    // DELETE /customers/:id  [admin only]
    public function destroy(Customer $customer)
    {
        if ($customer->bookings()->whereIn('status', ['confirmed', 'checked_in'])->exists()) {
            return back()->with('error', 'Cannot delete a customer with active bookings.');
        }

        $name = $customer->name;
        $customer->delete();

        return redirect()->route('customers.index')
            ->with('success', "Customer \"{$name}\" deleted.");
    }
}