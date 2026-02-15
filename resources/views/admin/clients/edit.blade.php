@extends('layouts.app')

@section('content')
<div class="page-header">
    <h1 class="page-title">
        Modifier le Client
        <small>{{ $client->company_name }}</small>
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

    <form method="POST" action="{{ route('admin.clients.update', $client) }}">
        @csrf
        @method('PUT')

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
                    value="{{ old('name', $client->user->name) }}"
                    required
                    class="form-input"
                >
            </div>

            <div class="form-group">
                <label class="form-label">Email</label>
                <input
                    type="email"
                    value="{{ $client->email }}"
                    disabled
                    class="form-input"
                    style="opacity: 0.6; cursor: not-allowed;"
                >
                <p class="form-help">L'email ne peut pas être modifié</p>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Nouveau mot de passe</label>
                <div style="position: relative;">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-input"
                        placeholder="Laisser vide pour conserver l'actuel"
                        style="padding-right: 3rem;"
                    >
                    <button
                        type="button"
                        onclick="togglePassword('password', this)"
                        style="position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); background: none; border: none; cursor: pointer; font-size: 1.25rem; padding: 0.25rem;"
                        title="Afficher/Masquer le mot de passe"
                    >
                        <span class="eye-icon">👁️</span>
                        <span class="eye-off-icon" style="display: none;">🔒</span>
                    </button>
                </div>
                <p class="form-help">Min. 8 caractères, majuscule, minuscule, chiffre et caractère spécial.</p>
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
                    value="{{ old('company_name', $client->company_name) }}"
                    required
                    class="form-input"
                >
            </div>

            <div class="grid grid-2">
                <div class="form-group">
                    <label for="contact_name" class="form-label">Nom du contact</label>
                    <input
                        type="text"
                        id="contact_name"
                        name="contact_name"
                        value="{{ old('contact_name', $client->contact_name) }}"
                        class="form-input"
                    >
                </div>

                <div class="form-group">
                    <label for="phone" class="form-label">Téléphone</label>
                    <input
                        type="text"
                        id="phone"
                        name="phone"
                        value="{{ old('phone', $client->phone) }}"
                        class="form-input"
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
                >{{ old('address', $client->address) }}</textarea>
            </div>

            <div class="form-group">
                <label style="display: flex; align-items: center; cursor: pointer;">
                    <input
                        type="checkbox"
                        name="is_active"
                        {{ old('is_active', $client->is_active) ? 'checked' : '' }}
                        style="margin-right: 0.5rem; width: auto;"
                    >
                    <span style="font-weight: 500;">Client actif</span>
                </label>
            </div>
        </div>

        {{-- Section Types de connexion --}}
        @php
            $clientConnectionTypeIds = old('connection_types', $client->connectionTypes->pluck('id')->toArray());
        @endphp
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
                            {{ in_array($type->id, $clientConnectionTypeIds) ? 'checked' : '' }}
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
        @php
            $clientProviderIds = old('providers', $client->providers->pluck('id')->toArray());
        @endphp
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
                            {{ in_array($provider->id, $clientProviderIds) ? 'checked' : '' }}
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
        @php
            $clientEquipment = $client->equipment->keyBy('id');
            $equipmentByCategory = $equipment->groupBy('category');
        @endphp
        <div class="section">
            <h3 class="section-title mb-lg" style="padding-bottom: 0.75rem; border-bottom: 2px solid var(--border-color);">
                Matériels en place
            </h3>

            @foreach($equipmentByCategory as $category => $items)
                <div style="margin-bottom: 1rem;">
                    <h4 style="font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 0.5rem;">
                        {{ $category ?: 'Sans catégorie' }}
                    </h4>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        @foreach($items as $eq)
                            @php
                                $isChecked = $clientEquipment->has($eq->id);
                                $quantity = $isChecked ? $clientEquipment->get($eq->id)->pivot->quantity : 1;
                                $notes = $isChecked ? $clientEquipment->get($eq->id)->pivot->notes : '';
                            @endphp
                            <div style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem; border: 1px solid var(--border-color); border-radius: var(--border-radius); flex-wrap: wrap;">
                                <input
                                    type="checkbox"
                                    id="equipment_{{ $eq->id }}"
                                    onchange="toggleEquipmentFields({{ $eq->id }})"
                                    {{ $isChecked ? 'checked' : '' }}
                                    style="width: auto;"
                                >
                                <input type="hidden" name="equipment[{{ $eq->id }}][id]" value="{{ $eq->id }}" {{ $isChecked ? '' : 'disabled' }} id="equipment_id_{{ $eq->id }}">
                                <label for="equipment_{{ $eq->id }}" style="min-width: 150px; cursor: pointer;">{{ $eq->name }}</label>
                                <input
                                    type="number"
                                    name="equipment[{{ $eq->id }}][quantity]"
                                    value="{{ old("equipment.{$eq->id}.quantity", $quantity) }}"
                                    min="1"
                                    style="width: 60px;"
                                    {{ $isChecked ? '' : 'disabled' }}
                                    id="equipment_qty_{{ $eq->id }}"
                                    placeholder="Qté"
                                >
                                <input
                                    type="text"
                                    name="equipment[{{ $eq->id }}][notes]"
                                    value="{{ old("equipment.{$eq->id}.notes", $notes) }}"
                                    style="flex: 1; min-width: 200px;"
                                    {{ $isChecked ? '' : 'disabled' }}
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
                <label for="custom_equipment" class="form-label">Ajouter des équipements personnalisés</label>
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
                        {{ old('has_4g5g_backup', $client->has_4g5g_backup) ? 'checked' : '' }}
                        onchange="toggleBackupFields()"
                        style="margin-right: 0.5rem; width: auto;"
                    >
                    <span style="font-weight: 500;">Ce client dispose d'un backup 4G/5G</span>
                </label>
            </div>

            <div id="backup_fields" style="display: {{ old('has_4g5g_backup', $client->has_4g5g_backup) ? 'block' : 'none' }}; margin-top: 1rem; padding: 1rem; background: var(--bg-secondary); border-radius: var(--border-radius);">
                <div class="grid grid-2">
                    <div class="form-group">
                        <label for="backup_operator" class="form-label">Opérateur</label>
                        <input
                            type="text"
                            id="backup_operator"
                            name="backup_operator"
                            value="{{ old('backup_operator', $client->backup_operator) }}"
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
                            value="{{ old('backup_phone_number', $client->backup_phone_number) }}"
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
                        value="{{ old('backup_sim_number', $client->backup_sim_number) }}"
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
                    >{{ old('backup_notes', $client->backup_notes) }}</textarea>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a href="{{ route('admin.clients.index') }}" class="btn btn-ghost">Annuler</a>
            <button type="submit" class="btn btn-primary">
                Mettre à jour
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
</script>
@endpush
