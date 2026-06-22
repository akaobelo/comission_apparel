<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);
        
        // Ensure email is lowercase for case-insensitive matching
        $credentials['email'] = strtolower($credentials['email']);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            if ($user->isAdmin()) {
                return redirect('/admin/dashboard');
            }

            if ($user->isCoach()) {
                // Only declined coaches are blocked
                if ($user->status === 'declined') {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return back()->with('error', 'Your account has been declined. Please contact The Commission Apparel for assistance.');
                }
                return redirect('/coach/dashboard')->with('activeCoachTab', 'overview');
            }

            return redirect('/');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        $sports = config('sports.categories');

        return view('auth.register', compact('sports'));
    }

    public function register(Request $request)
    {
        // Normalize email to lowercase before validation to ensure unique checks are case-insensitive
        $request->merge([
            'email' => strtolower($request->input('email')),
            'email_confirmation' => strtolower($request->input('email_confirmation')),
        ]);

        $validated = $request->validate([
            'first_name'            => ['required', 'string', 'max:255'],
            'last_name'             => ['required', 'string', 'max:255'],
            'organization'          => ['required', 'string', 'max:255'],
            'phone'                 => ['required', 'string', 'max:20'],
            'sport'                 => ['required', 'string', 'max:100'],
            'logo'                  => ['nullable', 'image', 'max:10240'], // Increased to 10MB
            'email'                 => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'email_confirmation'    => ['required', 'same:email'],
            'password'              => ['required', 'min:8', 'confirmed'],
            'sales_rep'             => ['nullable', 'string', 'max:255'],
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('organization_logos', 'public');
        }

        $user = User::create([
            'first_name'   => $validated['first_name'],
            'last_name'    => $validated['last_name'],
            'email'        => $validated['email'],
            'password'     => Hash::make($validated['password']),
            'organization' => $validated['organization'],
            'phone'        => $validated['phone'],
            'sport'        => $validated['sport'],
            'logo_path'    => $logoPath,
            'role'         => 'coach',
            'status'       => 'active', // Auto-approved — no admin gate
            'sales_rep'    => $validated['sales_rep'] ?? null,
        ]);

        // Notify admin of new coach registration (for their awareness)
        \Illuminate\Support\Facades\Notification::send(
            \App\Models\User::where('role', 'admin')->get(),
            new \App\Notifications\CoachRegistered($user)
        );

        // Log them in immediately
        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/coach/dashboard')->with('success', 'Welcome to The Commission Apparel! Your coach account is active.')->with('activeCoachTab', 'overview');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
