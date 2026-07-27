<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Mail\EmailChangedMail;
use App\Mail\PasswordChangedMail;
use App\Models\TrustedDevice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Afficher le formulaire de profil
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
            'trustedDevices' => $request->user()->trustedDevices()
                ->where('expires_at', '>', now())
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }

    /**
     * Mettre à jour le nom et l'email
     */
    public function updateInfo(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $oldEmail = $user->email;
        $newEmail = $request->validated()['email'];

        $user->update([
            'name' => $request->validated()['name'],
            'email' => $newEmail,
        ]);

        if ($newEmail !== $oldEmail) {
            Mail::to($oldEmail)->send(new EmailChangedMail($user, $oldEmail, $newEmail));
        }

        return back()->with('success', 'Vos informations ont été mises à jour.');
    }

    /**
     * Changement de mot de passe volontaire
     */
    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validateWithBag('updatePassword', [
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

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Le mot de passe actuel est incorrect.'], 'updatePassword');
        }

        if (Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Le nouveau mot de passe doit être différent de l\'ancien.'], 'updatePassword');
        }

        $user->update(['password' => $request->password]);

        // Déconnecter les autres sessions actives sur ce compte
        Auth::logoutOtherDevices($request->password);

        $request->session()->regenerate();

        Mail::to($user->email)->send(new PasswordChangedMail($user, 'par vous-même depuis votre profil'));

        return back()->with('success', 'Mot de passe mis à jour avec succès.');
    }

    /**
     * Révoquer un appareil de confiance
     */
    public function destroyTrustedDevice(Request $request, TrustedDevice $device): RedirectResponse
    {
        abort_unless($device->user_id === $request->user()->id, 403);

        $device->delete();

        return back()->with('success', 'Appareil retiré de la liste des appareils de confiance.');
    }

    /**
     * Révoquer tous les appareils de confiance
     */
    public function destroyAllTrustedDevices(Request $request): RedirectResponse
    {
        $request->user()->trustedDevices()->delete();

        return back()->with('success', 'Tous les appareils de confiance ont été révoqués.');
    }
}
