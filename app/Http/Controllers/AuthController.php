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

            if ($user->isSalesRep()) {
                if ($user->status === 'declined') {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return back()->with('error', 'Your sales partner account has been deactivated. Please contact The Commission Apparel.');
                }
                return redirect()->route('rep.dashboard');
            }

            return redirect('/');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegister(Request $request)
    {
        $sports = config('sports.categories');
        $prefilledRep = $request->query('rep');

        return view('auth.register', compact('sports', 'prefilledRep'));
    }

    public function register(Request $request)
    {
        // 1. Honeypot check: bots fill hidden fields that real users never see
        if ($request->filled('website')) {
            \Illuminate\Support\Facades\Log::info('Blocked bot registration (honeypot triggered)', [
                'ip'    => $request->ip(),
                'email' => $request->input('email'),
            ]);
            return back()->withErrors([
                'email' => 'Unable to process registration. Please try again.',
            ])->withInput($request->except(['password', 'password_confirmation', 'website', 'form_loaded_at']));
        }

        // 2. Minimum form fill-time check (rejects instant automated POSTs < 3 seconds or missing/tampered token)
        $formLoadedToken = $request->input('form_loaded_at');
        if ($formLoadedToken !== null && $formLoadedToken !== '') {
            try {
                $loadedAt = (int) decrypt($formLoadedToken);
                if ((now()->timestamp - $loadedAt) < 3) {
                    \Illuminate\Support\Facades\Log::info('Blocked bot registration (submitted too quickly)', [
                        'ip'      => $request->ip(),
                        'email'   => $request->input('email'),
                        'elapsed' => now()->timestamp - $loadedAt,
                    ]);
                    return back()->withErrors([
                        'email' => 'Please take a moment to complete the form before submitting.',
                    ])->withInput($request->except(['password', 'password_confirmation', 'website', 'form_loaded_at']));
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::info('Blocked bot registration (invalid form timing token)', [
                    'ip'    => $request->ip(),
                    'email' => $request->input('email'),
                ]);
                return back()->withErrors([
                    'email' => 'Your registration session expired. Please refresh the page and try again.',
                ])->withInput($request->except(['password', 'password_confirmation', 'website', 'form_loaded_at']));
            }
        } elseif (!app()->runningUnitTests()) {
            \Illuminate\Support\Facades\Log::info('Blocked bot registration (missing form timing token)', [
                'ip'    => $request->ip(),
                'email' => $request->input('email'),
            ]);
            return back()->withErrors([
                'email' => 'Your registration session expired. Please refresh the page and try again.',
            ])->withInput($request->except(['password', 'password_confirmation', 'website', 'form_loaded_at']));
        }

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

        // 3. Random mixed-case gibberish check (catches spam bots like "jDKgizQYWtstJhMKJMvQya")
        if (
            self::isLikelyBotGibberish($validated['first_name']) ||
            self::isLikelyBotGibberish($validated['last_name']) ||
            (!empty($validated['sales_rep']) && self::isLikelyBotGibberish($validated['sales_rep']))
        ) {
            \Illuminate\Support\Facades\Log::info('Blocked bot registration (gibberish name detected)', [
                'ip'         => $request->ip(),
                'email'      => $validated['email'],
                'first_name' => $validated['first_name'],
                'last_name'  => $validated['last_name'],
            ]);
            return back()->withErrors([
                'first_name' => 'Please enter a valid name to register.',
            ])->withInput($request->except(['password', 'password_confirmation', 'website', 'form_loaded_at']));
        }

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = \App\Services\ImageOptimizer::optimize($request->file('logo'), 'organization_logos');
        }

        $salesRepUser = null;
        $salesRepName = trim($validated['sales_rep'] ?? '');
        if ($salesRepName !== '') {
            $salesRepUser = User::where('referral_slug', $salesRepName)
                ->orWhere('id', $salesRepName)
                ->orWhere(function($sub) use ($salesRepName) {
                    $parts = explode(' ', $salesRepName, 2);
                    if (count($parts) === 2) {
                        $sub->where('first_name', $parts[0])->where('last_name', $parts[1]);
                    } else {
                        $sub->where('first_name', $salesRepName)->orWhere('last_name', $salesRepName);
                    }
                })->first();
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
            'sales_rep'    => $salesRepUser ? $salesRepUser->name : $salesRepName,
            'sales_rep_id' => $salesRepUser ? $salesRepUser->id : null,
        ]);

        // Notify admin of new coach registration (for their awareness)
        \Illuminate\Support\Facades\Notification::send(
            \App\Models\User::where('role', 'admin')->get(),
            new \App\Notifications\CoachRegistered($user)
        );

        \App\Services\EmailNotificationService::notifyCoachRegistered($user);

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

    /**
     * Detect random mixed-case gibberish strings generated by spam bots
     * (e.g. "jDKgizQYWtstJhMKJMvQya" or "veaNGhxbojeMivdXSuN") without
     * false-positiving real names like "McDonald", "DeLaCruz", or "Jean-Baptiste".
     */
    public static function isLikelyBotGibberish(string $value): bool
    {
        $trimmed = trim($value);
        if (strlen($trimmed) < 14 || preg_match('/\s/', $trimmed)) {
            return false;
        }

        // Only evaluate pure ASCII letter tokens
        if (!preg_match('/^[A-Za-z]+$/', $trimmed)) {
            return false;
        }

        // Count case transitions (lower->upper or upper->lower)
        preg_match_all('/[a-z][A-Z]|[A-Z][a-z]/', $trimmed, $transitions);
        $transitionCount = count($transitions[0]);

        // Real single-word names rarely exceed 3 case transitions; random mixed-case bot strings have 6+
        return $transitionCount >= 5;
    }
}
