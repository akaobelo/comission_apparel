<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\PasswordResetLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class InternalPasswordResetController extends Controller
{
    /**
     * Show the identity verification form.
     */
    public function showVerifyForm()
    {
        return view('auth.passwords.verify');
    }

    /**
     * Verify the user's identity based on Email, Phone, and Organization.
     */
    public function verifyIdentity(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'phone' => ['required', 'string'],
            'organization' => ['required', 'string'],
        ]);

        // Find user by email
        $user = User::where('email', $request->email)->first();

        // Check if user exists and if phone and organization match exactly
        if (!$user || 
            strtolower(trim($user->phone)) !== strtolower(trim($request->phone)) || 
            strtolower(trim($user->organization)) !== strtolower(trim($request->organization))) {
            
            throw ValidationException::withMessages([
                'email' => ['The provided identity details do not match our records.'],
            ]);
        }

        // Store a secure temporary session flag allowing them to reset
        // We will store the verified user's ID for 15 minutes.
        $request->session()->put('verified_reset_user_id', $user->id);
        $request->session()->put('verified_reset_expires_at', now()->addMinutes(15)->timestamp);

        return redirect()->route('password.reset.form');
    }

    /**
     * Show the form to input the new password.
     */
    public function showResetForm(Request $request)
    {
        $userId = $request->session()->get('verified_reset_user_id');
        $expiresAt = $request->session()->get('verified_reset_expires_at');

        if (!$userId || !$expiresAt || now()->timestamp > $expiresAt) {
            $request->session()->forget(['verified_reset_user_id', 'verified_reset_expires_at']);
            return redirect()->route('password.verify.form')
                ->with('error', 'Your password reset session has expired or is invalid. Please verify your identity again.');
        }

        return view('auth.passwords.reset');
    }

    /**
     * Update the password and log the audit trail.
     */
    public function updatePassword(Request $request)
    {
        $userId = $request->session()->get('verified_reset_user_id');
        $expiresAt = $request->session()->get('verified_reset_expires_at');

        if (!$userId || !$expiresAt || now()->timestamp > $expiresAt) {
            $request->session()->forget(['verified_reset_user_id', 'verified_reset_expires_at']);
            return redirect()->route('password.verify.form')
                ->with('error', 'Your password reset session has expired. Please verify your identity again.');
        }

        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::findOrFail($userId);

        $user->forceFill([
            'password' => Hash::make($request->password)
        ])->save();

        // Create the Audit Log
        PasswordResetLog::create([
            'user_id' => $user->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Clear the reset session
        $request->session()->forget(['verified_reset_user_id', 'verified_reset_expires_at']);

        return redirect()->route('login')
            ->with('success', 'Your password has been successfully reset. You may now log in.');
    }
}
