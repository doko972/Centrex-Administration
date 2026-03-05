<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsChanged
{
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->must_change_password) {
            // Ne pas rediriger si on est déjà sur la page de changement de mot de passe
            if (!$request->routeIs('password.force-change') && !$request->routeIs('password.force-change.update')) {
                return redirect()->route('password.force-change')
                    ->with('warning', 'Vous devez définir un nouveau mot de passe avant de continuer.');
            }
        }

        return $next($request);
    }
}
