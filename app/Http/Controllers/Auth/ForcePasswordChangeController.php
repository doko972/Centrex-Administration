<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ForcePasswordChangeController extends Controller
{
    /**
     * Afficher le formulaire de changement de mot de passe obligatoire
     */
    public function show()
    {
        return view('auth.change-password');
    }

    /**
     * Traiter le changement de mot de passe obligatoire
     */
    public function update(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],
        ], [
            'current_password.required' => 'Le mot de passe actuel est obligatoire.',
            'password.required' => 'Le nouveau mot de passe est obligatoire.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        $user = Auth::user();

        // Vérifier que le mot de passe actuel est correct
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Le mot de passe actuel est incorrect.']);
        }

        // Vérifier que le nouveau mot de passe est différent de l'ancien
        if (Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Le nouveau mot de passe doit être différent de l\'ancien.']);
        }

        // Mettre à jour le mot de passe et désactiver le flag
        $user->update([
            'password' => $request->password,
            'must_change_password' => false,
        ]);

        // Déconnecter toutes les autres sessions actives
        Auth::logoutOtherDevices($request->password);

        // Régénérer la session pour sécuriser
        $request->session()->regenerate();

        return redirect()->intended($this->redirectTo($user))
            ->with('success', 'Mot de passe mis à jour avec succès. Bienvenue !');
    }

    /**
     * Rediriger selon le rôle après le changement
     */
    private function redirectTo($user): string
    {
        if ($user->isAdmin()) {
            return route('admin.dashboard');
        }
        if ($user->isSuperClient()) {
            return route('superclient.dashboard');
        }
        return route('client.dashboard');
    }
}
