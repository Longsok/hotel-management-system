<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class GuestAuthController extends Controller
{
    // GET /book/login
    public function showLogin()
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('guest.booking');
        }
        return view('guest.login');
    }

    // POST /book/login
    public function login(Request $request)
    {
        $data = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (Auth::guard('customer')->attempt([
            'email'  => $data['email'],
            'password' => $data['password'],
            'status' => 'active',
        ], $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('guest.booking'));
        }

        return back()->withErrors([
            'email' => 'Invalid email or password.',
        ])->withInput($request->only('email'));
    }

    // GET /book/register
    public function showRegister()
    {
        if (Auth::guard('customer')->check()) {
            return redirect()->route('guest.booking');
        }
        return view('guest.register');
    }

    // POST /book/register
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'                  => ['required', 'string', 'max:100'],
            'email'                 => ['required', 'email', 'unique:customers,email'],
            'phone'                 => ['required', 'string', 'max:30'],
            'password'              => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $customer = Customer::create([
            'name'        => $data['name'],
            'email'       => $data['email'],
            'phone'       => $data['phone'],
            'password'    => Hash::make($data['password']),
            'nationality' => '',
            'status'      => 'active',
        ]);

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();

        return redirect()->route('guest.booking')
            ->with('success', 'Account created! Welcome, ' . $customer->name . '.');
    }

    // POST /book/logout
    public function logout(Request $request)
    {
        Auth::guard('customer')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('guest.booking');
    }
}
