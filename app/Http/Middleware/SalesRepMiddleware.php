<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SalesRepMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            if ($user->isSalesRep() || $user->isAdmin()) {
                if ($user->status !== 'declined') {
                    return $next($request);
                }
                return redirect('/login')->with('error', 'Your sales partner account has been deactivated. Please contact The Commission Apparel.');
            }
        }

        return redirect('/login')->with('error', 'You do not have access to the sales representative portal.');
    }
}
