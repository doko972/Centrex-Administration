@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1 class="page-title">
        Nouveau Client
        <small>Créer un nouveau compte client</small>
    </h1>
    <div class="page-actions">
        <a href="{{ route('admin.clients.index') }}" class="btn btn-ghost">
            ← Retour
        </a>
    </div>
</div>

<div class="card" style="max-width: 900px;">
    @if ($errors->any())
        <div class="alert alert-danger mb-lg">
            <span class="alert-icon">!</span>
            <div class="alert-content">
                <p class="alert-title">Erreurs de validation</p>
                <ul class="mt-sm" style="margin-left: 1rem; list-style: disc;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.clients.store') }}">
        @csrf

        <div class="section">
            <h3 class="section-title mb-lg" style="padding-bottom: 0.75rem; border-bottom: 2px solid var(--border-color);">
                Informations de connexion
            </h3>

            <div class="form-group">
                <label for="name" class="form-label">
                    Nom complet <span class="required">*</span>
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    required
                    class="form-input"
                    placeholder="Jean Dupont"
                >
            </div>

            <div class="form-group">
                <label for="email" class="form-label">
                    Email <span class="required">*</span>
                </label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    class="form-input"
                    placeholder="jean.dupont@entreprise.com"
                >
            </div>

            <div class="form-group">
                <label for="password" class="form-label">
                    Mot de passe <span class="required">*</span>
                </label>
                <div style="position: relative;">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        class="form-input"
                        placeholder="Minimum 8 caractères"
                        style="padding-right: 3rem;"
                        oninput="checkPasswordStrength(this.value)"
                    >
                    <button type="button" onclick="togglePassword('password', this)" aria-label="Afficher/Masquer le mot de passe" style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; color: var(--text-secondary); padding: 0.25rem;">
                        <svg class="eye-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="eye-off-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                    </button>
                </div>

                {{-- Indicateur de force du mot de passe --}}
                <div id="password-strength" style="margin-top: 0.5rem; display: none;">
                    <div style="height: 4px; border-radius: 2px; background: var(--border-color); overflow: hidden;">
                        <div id="strength-bar" style="height: 100%; width: 0; border-radius: 2px; transition: width 0.3s, background-color 0.3s;"></div>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.35rem;">
                        <span id="strength-label" style="font-size: 0.75rem; font-weight: 600;"></span>
                        <span id="strength-hints" style="font-size: 0.7rem; color: var(--text-tertiary);"></span>
                    </div>
                </div>

                <p class="form-help">Le mot de passe doit contenir au moins 8 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial (@$!%*?&#).</p>
            </div>
        </div>

        <div class="section">
            <h3 class="section-title mb-lg" style="padding-bottom: 0.75rem; border-bottom: 2px solid var(--border-color);">
                Informations entreprise
            </h3>

            <div class="form-group">
                <label for="company_name" class="form-label">
                    Nom de l'entreprise <span class="required">*</span>
                </label>
                <input
                    type="text"
                    id="company_name"
                    name="company_name"
                    value="{{ old('company_name') }}"
                    required
                    class="form-input"
                    placeholder="Ma Société SAS"
                >
            </div>

            <div class="grid grid-2">
                <div class="form-group">
                    <label for="contact_name" class="form-label">Nom du contact</label>
                    <input
                        type="text"
                        id="contact_name"
                        name="contact_name"
                        value="{{ old('contact_name') }}"
                        class="form-input"
                        placeholder="Personne à contacter"
                    >
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">Téléphone</label>
                    <input
                        type="text"
                        id="phone"
                        name="phone"
                        value="{{ old('phone') }}"
                        class="form-input"
                        placeholder="+33 1 23 45 67 89"
                    >
                </div>
            </div>

            <div class="form-group">
                <label for="address" class="form-label">Adresse</label>
                <textarea
                    id="address"
                    name="address"
                    rows="3"
                    class="form-textarea"
                    placeholder="123 Rue de l'Exemple, 75001 Paris"
                >{{ old('address') }}</textarea>
            </div>
        </div>

        {{-- Section Types de connexion --}}
        <div class="section">
            <h3 class="section-title mb-lg" style="padding-bottom: 0.75rem; border-bottom: 2px solid var(--border-color);">
                Types de connexion
            </h3>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 0.75rem;">
                @foreach($connectionTypes as $type)
                    <label style="display: flex; align-items: center; cursor: pointer; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: var(--border-radius);">
                        <input
                            type="checkbox"
                            name="connection_types[]"
                            value="{{ $type->id }}"
                            {{ in_array($type->id, old('connection_types', [])) ? 'checked' : '' }}
                            style="margin-right: 0.5rem; width: auto;"
                        >
                        <span>{{ $type->name }}</span>
                    </label>
                @endforeach
            </div>
            @if($connectionTypes->isEmpty())
                <p class="text-secondary">Aucun type de connexion disponible. <a href="{{ route('admin.connection-types.create') }}">Créer un type</a></p>
            @endif
        </div>

        {{-- Section Fournisseurs --}}
        <div class="section">
            <h3 class="section-title mb-lg" style="padding-bottom: 0.75rem; border-bottom: 2px solid var(--border-color);">
                Fournisseurs
            </h3>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 0.75rem;">
                @foreach($providers as $provider)
                    <label style="display: flex; align-items: center; cursor: pointer; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: var(--border-radius);">
                        <input
                            type="checkbox"
                            name="providers[]"
                            value="{{ $provider->id }}"
                            {{ in_array($provider->id, old('providers', [])) ? 'checked' : '' }}
                            style="margin-right: 0.5rem; width: auto;"
                        >
                        <span>
                            {{ $provider->name }}
                            @if($provider->url)
                                <a href="{{ $provider->url }}" target="_blank" style="margin-left: 0.25rem; font-size: 0.75rem;">🔗</a>
                            @endif
                        </span>
                    </label>
                @endforeach
            </div>
            @if($providers->isEmpty())
                <p class="text-secondary">Aucun fournisseur disponible. <a href="{{ route('admin.providers.create') }}">Créer un fournisseur</a></p>
            @endif
        </div>

        {{-- Section Matériels --}}
        <div class="section">
            <h3 class="section-title mb-lg" style="padding-bottom: 0.75rem; border-bottom: 2px solid var(--border-color);">
                Matériels en place
            </h3>

            @php
                $equipmentByCategory = $equipment->groupBy('category');
            @endphp

            @foreach($equipmentByCategory as $category => $items)
                <div style="margin-bottom: 1rem;">
                    <h4 style="font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 0.5rem;">
                        {{ $category ?: 'Sans catégorie' }}
                    </h4>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        @foreach($items as $eq)
                            <div style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: var(--border-radius); flex-wrap: wrap;">
                                <input
                                    type="checkbox"
                                    id="equipment_{{ $eq->id }}"
                                    onchange="toggleEquipmentFields({{ $eq->id }})"
                                    {{ old("equipment.{$eq->id}.id") ? 'checked' : '' }}
                                    style="width: auto;"
                                >
                                <input type="hidden" name="equipment[{{ $eq->id }}][id]" value="{{ $eq->id }}" disabled id="equipment_id_{{ $eq->id }}">
                                <label for="equipment_{{ $eq->id }}" style="min-width: 150px; cursor: pointer;">{{ $eq->name }}</label>
                                <input
                                    type="number"
                                    name="equipment[{{ $eq->id }}][quantity]"
                                    value="{{ old("equipment.{$eq->id}.quantity", 1) }}"
                                    min="1"
                                    style="width: 60px;"
                                    disabled
                                    id="equipment_qty_{{ $eq->id }}"
                                    placeholder="Qté"
                                >
                                <input
                                    type="text"
                                    name="equipment[{{ $eq->id }}][notes]"
                                    value="{{ old("equipment.{$eq->id}.notes") }}"
                                    style="flex: 1; min-width: 200px;"
                                    disabled
                                    id="equipment_notes_{{ $eq->id }}"
                                    placeholder="Modèle / Détails (ex: TP-Link T54W)"
                                    class="form-input"
                                >
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            @if($equipment->isEmpty())
                <p class="text-secondary">Aucun équipement disponible. <a href="{{ route('admin.equipment.create') }}">Créer un équipement</a></p>
            @endif

            <div class="form-group" style="margin-top: 1rem;">
                <label for="custom_equipment" class="form-label">Équipements personnalisés</label>
                <input
                    type="text"
                    id="custom_equipment"
                    name="custom_equipment"
                    value="{{ old('custom_equipment') }}"
                    class="form-input"
                    placeholder="Ex: Modem spécial, Antenne wifi (séparés par des virgules)"
                >
                <p class="form-help">Séparez les équipements par des virgules pour en ajouter plusieurs.</p>
            </div>
        </div>

        {{-- Section Backup 4G/5G --}}
        <div class="section">
            <h3 class="section-title mb-lg" style="padding-bottom: 0.75rem; border-bottom: 2px solid var(--border-color);">
                Backup 4G/5G
            </h3>

            <div class="form-group">
                <label style="display: flex; align-items: center; cursor: pointer;">
                    <input
                        type="checkbox"
                        id="has_4g5g_backup"
                        name="has_4g5g_backup"
                        value="1"
                        {{ old('has_4g5g_backup') ? 'checked' : '' }}
                        onchange="toggleBackupFields()"
                        style="margin-right: 0.5rem; width: auto;"
                    >
                    <span style="font-weight: 500;">Ce client dispose d'un backup 4G/5G</span>
                </label>
            </div>

            <div id="backup_fields" style="display: {{ old('has_4g5g_backup') ? 'block' : 'none' }}; margin-top: 1rem; padding: 1rem; background: var(--bg-secondary); border-radius: var(--border-radius);">
                <div class="grid grid-2">
                    <div class="form-group">
                        <label for="backup_operator" class="form-label">Opérateur</label>
                        <input
                            type="text"
                            id="backup_operator"
                            name="backup_operator"
                            value="{{ old('backup_operator') }}"
                            class="form-input"
                            placeholder="Ex: Orange, SFR, Bouygues..."
                        >
                    </div>

                    <div class="form-group">
                        <label for="backup_phone_number" class="form-label">Numéro de téléphone</label>
                        <input
                            type="text"
                            id="backup_phone_number"
                            name="backup_phone_number"
                            value="{{ old('backup_phone_number') }}"
                            class="form-input"
                            placeholder="+33 6 12 34 56 78"
                        >
                    </div>
                </div>

                <div class="form-group">
                    <label for="backup_sim_number" class="form-label">Numéro de carte SIM</label>
                    <input
                        type="text"
                        id="backup_sim_number"
                        name="backup_sim_number"
                        value="{{ old('backup_sim_number') }}"
                        class="form-input"
                        placeholder="ICCID de la carte SIM"
                    >
                </div>

                <div class="form-group">
                    <label for="backup_notes" class="form-label">Notes</label>
                    <textarea
                        id="backup_notes"
                        name="backup_notes"
                        rows="2"
                        class="form-textarea"
                        placeholder="Informations supplémentaires sur le backup..."
                    >{{ old('backup_notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.clients.index') }}" class="btn btn-ghost">Annuler</a>
            <button type="submit" class="btn btn-primary">
                Créer le client
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function togglePassword(inputId, button) {
    const input = document.getElementById(inputId);
    const eyeIcon = button.querySelector('.eye-icon');
    const eyeOffIcon = button.querySelector('.eye-off-icon');

    if (input.type === 'password') {
        input.type = 'text';
        eyeIcon.style.display = 'none';
        eyeOffIcon.style.display = 'inline';
    } else {
        input.type = 'password';
        eyeIcon.style.display = 'inline';
        eyeOffIcon.style.display = 'none';
    }
}

function toggleBackupFields() {
    const checkbox = document.getElementById('has_4g5g_backup');
    const fields = document.getElementById('backup_fields');
    fields.style.display = checkbox.checked ? 'block' : 'none';
}

function toggleEquipmentFields(equipmentId) {
    const checkbox = document.getElementById('equipment_' + equipmentId);
    const hiddenInput = document.getElementById('equipment_id_' + equipmentId);
    const qtyInput = document.getElementById('equipment_qty_' + equipmentId);
    const notesInput = document.getElementById('equipment_notes_' + equipmentId);

    hiddenInput.disabled = !checkbox.checked;
    qtyInput.disabled = !checkbox.checked;
    notesInput.disabled = !checkbox.checked;
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    // Re-enable checked equipment inputs
    document.querySelectorAll('[id^="equipment_"]:checked').forEach(function(checkbox) {
        const id = checkbox.id.replace('equipment_', '');
        toggleEquipmentFields(id);
    });
});

function togglePassword(fieldId, btn) {
    var field = document.getElementById(fieldId);
    var eyeIcon = btn.querySelector('.eye-icon');
    var eyeOffIcon = btn.querySelector('.eye-off-icon');
    if (field.type === 'password') {
        field.type = 'text';
        eyeIcon.style.display = 'none';
        eyeOffIcon.style.display = 'block';
    } else {
        field.type = 'password';
        eyeIcon.style.display = 'block';
        eyeOffIcon.style.display = 'none';
    }
}

function checkPasswordStrength(password) {
    var wrapper = document.getElementById('password-strength');
    var bar = document.getElementById('strength-bar');
    var label = document.getElementById('strength-label');
    var hints = document.getElementById('strength-hints');

    if (!password) { wrapper.style.display = 'none'; return; }
    wrapper.style.display = 'block';

    var score = 0;
    var missing = [];
    if (password.length >= 8) score++; else missing.push('8 car.');
    if (/[A-Z]/.test(password)) score++; else missing.push('majuscule');
    if (/[a-z]/.test(password)) score++; else missing.push('minuscule');
    if (/[0-9]/.test(password)) score++; else missing.push('chiffre');
    if (/[^A-Za-z0-9]/.test(password)) score++; else missing.push('symbole');

    var levels = [
        { label: 'Très faible', color: '#ef4444', width: '20%' },
        { label: 'Faible',      color: '#f97316', width: '40%' },
        { label: 'Moyen',       color: '#eab308', width: '60%' },
        { label: 'Fort',        color: '#22c55e', width: '80%' },
        { label: 'Très fort',   color: '#16a34a', width: '100%' },
    ];
    var level = levels[Math.max(0, score - 1)];
    bar.style.width = level.width;
    bar.style.backgroundColor = level.color;
    label.textContent = level.label;
    label.style.color = level.color;
    hints.textContent = missing.length ? 'Il manque : ' + missing.join(', ') : '';
}
</script>
@endpush
