<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CoachMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (\Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->isCoach()) {
            $user = \Illuminate\Support\Facades\Auth::user();
            // Coaches are auto-approved on registration; only declined accounts are blocked
            if ($user->status !== 'declined') {
                return $next($request);
            }
            return redirect('/login')->with('error', 'Your account has been declined. Please contact The Commission Apparel.');
        }

        return redirect('/login')->with('error', 'You do not have access to the coach portal.');
    }
}
