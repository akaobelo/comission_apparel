<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();

            if ($user->isAdmin()) {
                // Ignore any intended coach routes if they are an admin
                return redirect('/admin/dashboard');
            }

            if ($user->isCoach()) {
                if ($user->status === 'pending') {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return back()->with('error', 'Your account is pending administrator approval.');
                }
                if ($user->status === 'declined') {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return back()->with('error', 'Your account has been declined.');
                }
                return redirect('/coach/dashboard');
            }
            
            return redirect('/');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'organization' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'sport' => ['required', 'string', 'max:100'],
            'logo' => ['nullable', 'image', 'max:2048'], // 2MB Max
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'min:8', 'confirmed'],
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            // Using public disk so it's accessible via asset()
            $logoPath = $request->file('logo')->store('organization_logos', 'public');
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'organization' => $validated['organization'],
            'phone' => $validated['phone'],
            'sport' => $validated['sport'],
            'logo_path' => $logoPath,
            'role' => 'coach',
            'status' => 'pending', // Starts as pending
        ]);

        \Illuminate\Support\Facades\Notification::send(
            \App\Models\User::where('role', 'admin')->get(),
            new \App\Notifications\CoachRegistered($user)
        );

        // We do not log them in automatically because they require an admin approval first
        return redirect('/login')->with('success', 'Account created successfully! Please wait for administrator approval before logging in.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
