<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Auth\TwoFactorController;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && !session('two_factor_verified')) {
            if (TwoFactorController::hasTrustedDevice($request, Auth::user())) {
                session(['two_factor_verified' => true]);
            } elseif (!$request->routeIs('two-factor.*') && !$request->routeIs('logout')) {
                return redirect()->route('two-factor.verify');
            }
        }

        return $next($request);
    }
}
