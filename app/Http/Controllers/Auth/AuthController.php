<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Afficher le formulaire de connexion
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Traiter la connexion
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $user = Auth::user();

            // Client désactivé par un administrateur : connexion refusée
            if ($user->isClient() && !$user->client?->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                Log::channel('auth')->warning('Connexion refusée : compte client désactivé', [
                    'email' => $credentials['email'],
                    'ip' => $request->ip(),
                ]);

                return back()->withErrors([
                    'email' => 'Votre compte est désactivé. Veuillez contacter votre administrateur.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();

            // Logger la connexion réussie
            Log::channel('auth')->info('Connexion réussie', [
                'email' => $credentials['email'],
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now(),
            ]);

            // 2FA désactivé temporairement — pour réactiver, restaurer le bloc ci-dessous
            // et retirer les 3 lignes de redirection directe.
            // if (TwoFactorController::hasTrustedDevice($request, $user)) {
            //     session(['two_factor_verified' => true]);
            //     ...
            // }
            // TwoFactorController::generateAndSendCode($user);
            // return redirect()->route('two-factor.verify');
            session(['two_factor_verified' => true]);

            if ($user->isAdmin()) return redirect()->intended('/admin/dashboard');
            if ($user->isSuperClient()) return redirect()->intended('/superclient/dashboard');
            return redirect()->intended('/client/dashboard');
        }

        // Logger la tentative de connexion échouée
        Log::channel('auth')->warning('Tentative de connexion échouée', [
            'email' => $credentials['email'],
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'timestamp' => now(),
        ]);

        return back()->withErrors([
            'email' => 'Les identifiants ne correspondent pas.',
        ])->onlyInput('email');
    }

    /**
     * Déconnexion
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
