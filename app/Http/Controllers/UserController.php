<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // GET /users  [admin only]
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->role,   fn($q) => $q->where('role', $request->role))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('name',  'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            }))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('users.index', compact('users'));
    }

    // GET /users/create  [admin only]
    public function create()
    {
        return view('users.create');
    }

    // POST /users  [admin only]
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'min:8', 'confirmed'],
            'role'     => ['required', 'in:admin,staff'],
            'status'   => ['in:active,inactive'],
        ]);

        $data['status'] = $data['status'] ?? 'active';

        $user = User::create($data);

        return redirect()->route('users.index')
            ->with('success', "User \"{$user->name}\" created as {$user->role}.");
    }

    // GET /users/:id  [admin only]
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    // GET /users/:id/edit  [admin only]
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    // PUT /users/:id  [admin only]
    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'   => ['sometimes', 'string', 'max:255'],
            'email'  => ['sometimes', 'email', "unique:users,email,{$user->id}"],
            'role'   => ['sometimes', 'in:admin,staff'],
            'status' => ['sometimes', 'in:active,inactive'],
        ]);

        $user->update($data);

        return redirect()->route('users.index')
            ->with('success', "User \"{$user->name}\" updated.");
    }

    // DELETE /users/:id  [admin only]
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', "Staff member \"{$name}\" removed.");
    }
}
