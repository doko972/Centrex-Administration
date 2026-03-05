<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && !session('two_factor_verified')) {
            if (!$request->routeIs('two-factor.*') && !$request->routeIs('logout')) {
                return redirect()->route('two-factor.verify');
            }
        }

        return $next($request);
    }
}
