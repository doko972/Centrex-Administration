@extends('layouts.guest')

@section('content')
<h1 class="auth-title">Vérification en 2 étapes</h1>
<p class="auth-subtitle">Un code à 6 chiffres a été envoyé à <strong>{{ Auth::user()->email }}</strong></p>

@if ($errors->any())
    <div class="alert alert-danger mb-lg">
        <span class="alert-icon">!</span>
        <div class="alert-content">
            @foreach ($errors->all() as $error)
                <p class="alert-message mb-0">{{ $error }}</p>
            @endforeach
        </div>
    </div>
@endif

@if (session('resent'))
    <div class="alert alert-success mb-lg">
        <span class="alert-icon">✓</span>
        <div class="alert-content">
            <p class="alert-message mb-0">{{ session('resent') }}</p>
        </div>
    </div>
@endif

<form method="POST" action="{{ route('two-factor.verify.submit') }}" id="verify-form">
    @csrf

    <div class="form-group">
        <label class="form-label" style="text-align: center; display: block; margin-bottom: 1rem;">
            Saisissez le code reçu par email
        </label>

        {{-- 6 inputs individuels --}}
        <div id="code-inputs" style="display: flex; gap: 0.5rem; justify-content: center; margin-bottom: 0.75rem;">
            @for ($i = 0; $i < 6; $i++)
                <input
                    type="text"
                    inputmode="numeric"
                    maxlength="1"
                    pattern="[0-9]"
                    class="code-digit"
                    autocomplete="off"
                    style="width: 48px; height: 56px; text-align: center; font-size: 1.5rem; font-weight: 700; border: 2px solid var(--border-color); border-radius: var(--border-radius-md); background: var(--bg-primary); color: var(--text-primary); transition: border-color 0.2s;"
                >
            @endfor
        </div>

        {{-- Champ caché qui reçoit le code complet --}}
        <input type="hidden" name="code" id="code-value">

        @error('code')
            <p class="form-error" style="text-align: center;">{{ $message }}</p>
        @enderror
    </div>

    <div class="form-group" style="display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
        <label class="form-check" style="cursor: pointer;">
            <input type="checkbox" name="remember_device" value="1">
            <span class="form-check-label" style="font-size: 0.875rem;">
                Se souvenir de cet appareil pendant 30 jours
            </span>
        </label>
    </div>

    <button type="submit" id="submit-btn" class="btn btn-primary" style="width: 100%; justify-content: center; margin-top: 0.25rem;" disabled>
        Vérifier
    </button>
</form>

{{-- Renvoyer le code --}}
<div style="text-align: center; margin-top: 1.25rem;">
    <p style="font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 0.5rem;">
        Vous n'avez pas reçu le code ?
    </p>
    <form method="POST" action="{{ route('two-factor.resend') }}" style="display: inline;">
        @csrf
        <button type="submit" id="resend-btn" disabled style="background: none; border: none; cursor: pointer; color: var(--color-primary); font-size: 0.875rem; font-weight: 600; text-decoration: underline; opacity: 0.5;">
            Renvoyer (<span id="countdown">60</span>s)
        </button>
    </form>
</div>

<div style="text-align: center; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--border-color);">
    <form method="POST" action="{{ route('logout') }}" style="display: inline;">
        @csrf
        <button type="submit" style="background: none; border: none; cursor: pointer; color: var(--text-secondary); font-size: 0.85rem; text-decoration: underline;">
            Ce n'est pas moi — Se déconnecter
        </button>
    </form>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var inputs = document.querySelectorAll('.code-digit');
    var hidden  = document.getElementById('code-value');
    var submitBtn = document.getElementById('submit-btn');
    var form    = document.getElementById('verify-form');

    // Focus premier input au chargement
    if (inputs[0]) inputs[0].focus();

    // Mise à jour du champ caché + activation du bouton
    function updateCode() {
        var code = '';
        inputs.forEach(function (inp) { code += inp.value; });
        hidden.value = code;
        submitBtn.disabled = code.length < 6;
        // Highlight actifs
        inputs.forEach(function (inp) {
            inp.style.borderColor = inp.value ? 'var(--color-primary)' : 'var(--border-color)';
        });
    }

    inputs.forEach(function (input, index) {
        input.addEventListener('input', function (e) {
            var val = e.target.value.replace(/[^0-9]/g, '');
            e.target.value = val.slice(-1);
            updateCode();
            if (val && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
            // Auto-submit quand le 6e chiffre est saisi
            if (index === 5 && val) {
                setTimeout(function () { form.submit(); }, 120);
            }
        });

        input.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace' && !e.target.value && index > 0) {
                inputs[index - 1].focus();
                inputs[index - 1].value = '';
                updateCode();
            }
        });

        // Support du collé (paste)
        input.addEventListener('paste', function (e) {
            e.preventDefault();
            var pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
            for (var i = 0; i < 6 && i < pasted.length; i++) {
                inputs[i].value = pasted[i];
            }
            updateCode();
            var nextEmpty = Math.min(pasted.length, 5);
            inputs[nextEmpty].focus();
            if (pasted.length >= 6) {
                setTimeout(function () { form.submit(); }, 120);
            }
        });
    });

    // Countdown renvoyer le code (60 secondes)
    var resendBtn   = document.getElementById('resend-btn');
    var countdownEl = document.getElementById('countdown');
    var seconds     = 60;

    var timer = setInterval(function () {
        seconds--;
        countdownEl.textContent = seconds;
        if (seconds <= 0) {
            clearInterval(timer);
            resendBtn.disabled = false;
            resendBtn.style.opacity = '1';
            resendBtn.innerHTML = 'Renvoyer le code';
        }
    }, 1000);
})();
</script>
@endpush
