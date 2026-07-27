<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Bag d'erreurs dédié pour ne pas mélanger avec le formulaire de mot de passe sur la même page.
     */
    protected $errorBag = 'updateInfo';

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $emailChanging = strtolower((string) $this->input('email')) !== strtolower($this->user()->email);

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            // On exige le mot de passe actuel uniquement quand l'email change réellement,
            // pour éviter qu'une session volée puisse détourner le compte via un email modifié.
            'current_password' => $emailChanging ? ['required', 'current_password'] : ['nullable'],
        ];
    }

    /**
     * Messages d'erreur personnalisés en français.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom est obligatoire.',
            'email.required' => "L'email est obligatoire.",
            'email.email' => 'Veuillez saisir une adresse email valide.',
            'email.unique' => 'Cette adresse email est déjà utilisée par un autre compte.',
            'current_password.required' => 'Votre mot de passe actuel est requis pour changer votre adresse email.',
            'current_password.current_password' => 'Le mot de passe actuel est incorrect.',
        ];
    }
}
