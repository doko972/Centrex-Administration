<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => \App\Http\Middleware\IsAdmin::class,
            'client' => \App\Http\Middleware\IsClient::class,
            'superclient' => \App\Http\Middleware\IsSuperClient::class,
        ]);

        // Rediriger vers /login quand la session expire
        $middleware->redirectGuestsTo('/login');

        // Exclure les routes du proxy de la vérification CSRF
        $middleware->validateCsrfTokens(except: [
            'client/centrex/*/proxy',
            'client/centrex/*/proxy/*',
            'admin/centrex/*/proxy',
            'admin/centrex/*/proxy/*',
            'admin/ipbx/*/proxy',
            'admin/ipbx/*/proxy/*',
            'client/ipbx/*/proxy',
            'client/ipbx/*/proxy/*',
            'superclient/centrex/*/proxy',
            'superclient/centrex/*/proxy/*',
            'superclient/ipbx/*/proxy',
            'superclient/ipbx/*/proxy/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Gérer les sessions expirées - rediriger vers login
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Session expirée. Veuillez vous reconnecter.'], 401);
            }

            return redirect()->guest(route('login'))
                ->with('error', 'Votre session a expiré. Veuillez vous reconnecter.');
        });
    })->create();
