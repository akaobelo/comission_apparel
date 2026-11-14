<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminOrSalesRepMiddleware
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
            if ($user->isAdmin() || $user->isSalesRep()) {
                if ($user->status !== 'declined') {
                    return $next($request);
                }
                return redirect('/login')->with('error', 'Your account has been deactivated. Please contact The Commission Apparel.');
            }
        }

        return redirect('/login')->with('error', 'You do not have access to design management.');
    }
}
