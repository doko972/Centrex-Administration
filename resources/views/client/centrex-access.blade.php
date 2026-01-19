@extends('layouts.app')

@section('content')
    <div style="min-height: 60vh; display: flex; align-items: center; justify-content: center;">
        <div class="card" style="max-width: 600px;">
            @if ($centrex->image)
                <img src="{{ asset('storage/' . $centrex->image) }}" alt="{{ $centrex->name }}"
                    style="width: 150px; height: 150px; object-fit: cover; border-radius: var(--border-radius); margin: 0 auto 1.5rem; display: block;">
            @else
                <div style="font-size: 5rem; margin-bottom: 1rem; text-align: center;">📞</div>
            @endif

            <h2 style="margin-bottom: 0.5rem; text-align: center;">{{ $centrex->name }}</h2>
            <p style="color: var(--text-secondary); margin-bottom: 2rem; text-align: center;">Accès à votre centrex FreePBX
            </p>

            <!-- Identifiants de connexion -->
            <div
                style="background-color: var(--bg-tertiary); padding: 1.5rem; border-radius: var(--border-radius); margin-bottom: 1.5rem;">
                <h3 style="margin-bottom: 1rem; font-size: 1rem;">Identifiants de connexion :</h3>

                <div style="margin-bottom: 1rem;">
                    <label
                        style="display: block; font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 0.5rem;">Login
                        :</label>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <div
                            style="flex: 1; padding: 0.5rem; background-color: var(--bg-primary); border: 1px solid var(--border-color); border-radius: var(--border-radius); font-family: monospace; filter: blur(5px); user-select: none; pointer-events: none;">
                            {{ $centrex->login }}
                        </div>
                        {{-- <button onclick="copyText('{{ $centrex->login }}', this)"
                            class="btn btn-sm btn-secondary">Copier</button> --}}
                    </div>
                </div>

                <div style="margin-bottom: 0;">
                    <label
                        style="display: block; font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 0.5rem;">Mot
                        de passe :</label>
                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                        <div
                            style="flex: 1; padding: 0.5rem; background-color: var(--bg-primary); border: 1px solid var(--border-color); border-radius: var(--border-radius); font-family: monospace; filter: blur(5px); user-select: none; pointer-events: none;">
                            {{ $centrex->password }}
                        </div>
                        {{-- <button onclick="copyText('{{ $centrex->password }}', this)"
                            class="btn btn-sm btn-secondary">Copier</button> --}}
                    </div>
                </div>

                <p style="font-size: 0.75rem; color: var(--text-tertiary); margin-top: 1rem; text-align: center;">
                    🔒 Les identifiants sont masqués pour votre sécurité. Utilisez les boutons "Copier".
                </p>
            </div>

            <!-- Boutons d'action -->
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="{{ route('client.dashboard') }}" class="btn btn-outline">← Retour</a>
                <a href="{{ $url }}" target="_blank" class="btn btn-primary" id="open-centrex">Ouvrir FreePBX →</a>
            </div>

            <p style="font-size: 0.875rem; color: var(--text-tertiary); margin-top: 1.5rem; text-align: center;">
                Cliquez sur "Ouvrir FreePBX", puis utilisez les identifiants ci-dessus pour vous connecter.
            </p>
        </div>
    </div>

    <script>
        function copyText(text, button) {
            // Créer un élément temporaire pour copier le texte
            const tempInput = document.createElement('input');
            tempInput.value = text;
            document.body.appendChild(tempInput);
            tempInput.select();
            document.execCommand('copy');
            document.body.removeChild(tempInput);

            // Feedback visuel
            const originalText = button.textContent;
            button.textContent = '✓ Copié !';
            button.style.backgroundColor = 'var(--color-success)';

            setTimeout(() => {
                button.textContent = originalText;
                button.style.backgroundColor = '';
            }, 2000);
        }

        // Ouvrir automatiquement FreePBX après 1 seconde
        setTimeout(function() {
            window.open('{{ $url }}', '_blank');
        }, 1000);
    </script>
@endsection
