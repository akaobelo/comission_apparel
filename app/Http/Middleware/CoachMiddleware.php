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
            if ($user->status === 'approved') {
                return $next($request);
            }
            // Pending or Declined coach
            return redirect('/')->with('error', 'Your account is not approved yet.');
        }

        return redirect('/login')->with('error', 'You do not have access to the coach portal.');
    }
}
