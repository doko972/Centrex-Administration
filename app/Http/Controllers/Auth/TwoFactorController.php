<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\LoginVerificationCode;
use App\Models\LoginCode;
use App\Models\TrustedDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class TwoFactorController extends Controller
{
    /**
     * Afficher le formulaire de saisie du code
     */
    public function show()
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Si déjà vérifié, rediriger vers le dashboard
        if (session('two_factor_verified')) {
            return redirect($this->dashboardRoute());
        }

        $loginCode = LoginCode::where('user_id', Auth::id())->first();
        $codeExpiresAt = $loginCode?->expires_at;

        return view('auth.verify-code', compact('codeExpiresAt'));
    }

    /**
     * Vérifier le code saisi
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ], [
            'code.required' => 'Veuillez saisir le code reçu par email.',
            'code.size'     => 'Le code doit contenir exactement 6 chiffres.',
        ]);

        $user = Auth::user();
        $loginCode = LoginCode::where('user_id', $user->id)->first();

        // Pas de code actif
        if (!$loginCode) {
            return back()->withErrors(['code' => 'Aucun code actif. Veuillez vous reconnecter.']);
        }

        // Code expiré
        if ($loginCode->isExpired()) {
            $loginCode->delete();
            return back()->withErrors(['code' => 'Ce code a expiré. Veuillez vous reconnecter.']);
        }

        // Trop de tentatives
        if ($loginCode->hasExceededAttempts()) {
            $loginCode->delete();
            Auth::logout();
            $request->session()->invalidate();
            return redirect()->route('login')
                ->withErrors(['email' => 'Trop de tentatives incorrectes. Veuillez vous reconnecter.']);
        }

        // Code incorrect
        if (!Hash::check($request->code, $loginCode->code)) {
            $loginCode->increment('attempts');
            $remaining = 3 - $loginCode->fresh()->attempts;
            return back()->withErrors([
                'code' => "Code incorrect. $remaining tentative(s) restante(s).",
            ]);
        }

        // Code valide ✓
        $loginCode->delete();
        session(['two_factor_verified' => true]);

        $response = redirect($this->dashboardRoute())
            ->with('success', 'Connexion sécurisée avec succès.');

        // Se souvenir de cet appareil 30 jours
        if ($request->boolean('remember_device')) {
            $token = Str::random(64);
            TrustedDevice::create([
                'user_id'    => $user->id,
                'token'      => Hash::make($token),
                'expires_at' => now()->addDays(30),
            ]);
            $cookieValue = Crypt::encryptString($user->id . '|' . $token);
            $response = $response->cookie(
                'trusted_device',
                $cookieValue,
                60 * 24 * 30, // 30 jours en minutes
                '/',
                null,
                false,
                true // httpOnly
            );
        }

        return $response;
    }

    /**
     * Renvoyer un nouveau code
     */
    public function resend(Request $request)
    {
        $user = Auth::user();

        // Anti-spam : 1 renvoi par minute max
        $existing = LoginCode::where('user_id', $user->id)->first();
        if ($existing && $existing->created_at->diffInSeconds(now()) < 60) {
            return back()->withErrors(['code' => 'Veuillez attendre avant de renvoyer un nouveau code.']);
        }

        $this->generateAndSendCode($user);

        return back()->with('resent', 'Un nouveau code a été envoyé à votre adresse email.');
    }

    /**
     * Générer et envoyer un code 2FA
     */
    public static function generateAndSendCode($user): void
    {
        // Supprimer l'ancien code
        LoginCode::where('user_id', $user->id)->delete();

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        LoginCode::create([
            'user_id'    => $user->id,
            'code'       => Hash::make($code),
            'expires_at' => now()->addMinutes(10),
            'attempts'   => 0,
        ]);

        Mail::to($user->email)->send(new LoginVerificationCode($user, $code));
    }

    /**
     * Vérifier si l'appareil est de confiance
     */
    public static function hasTrustedDevice(Request $request, $user): bool
    {
        $cookieValue = $request->cookie('trusted_device');
        if (!$cookieValue) {
            return false;
        }

        try {
            $decrypted = Crypt::decryptString($cookieValue);
            [$userId, $token] = explode('|', $decrypted, 2);

            if ($userId != $user->id) {
                return false;
            }

            $devices = TrustedDevice::where('user_id', $user->id)
                ->where('expires_at', '>', now())
                ->get();

            foreach ($devices as $device) {
                if (Hash::check($token, $device->token)) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            return false;
        }

        return false;
    }

    private function dashboardRoute(): string
    {
        $user = Auth::user();
        if ($user->isAdmin()) return route('admin.dashboard');
        if ($user->isSuperClient()) return route('superclient.dashboard');
        return route('client.dashboard');
    }
}
