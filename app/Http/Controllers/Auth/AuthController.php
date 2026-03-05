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
            $request->session()->regenerate();

            $user = Auth::user();

            // Logger la connexion réussie
            Log::channel('auth')->info('Connexion réussie', [
                'email' => $credentials['email'],
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'timestamp' => now(),
            ]);

            // Vérifier si l'appareil est de confiance → passer le 2FA
            if (TwoFactorController::hasTrustedDevice($request, $user)) {
                session(['two_factor_verified' => true]);

                if ($user->isAdmin()) return redirect()->intended('/admin/dashboard');
                if ($user->isSuperClient()) return redirect()->intended('/superclient/dashboard');
                return redirect()->intended('/client/dashboard');
            }

            // Générer et envoyer le code 2FA
            TwoFactorController::generateAndSendCode($user);

            return redirect()->route('two-factor.verify');
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
