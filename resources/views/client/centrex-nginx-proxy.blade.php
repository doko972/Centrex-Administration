@extends('layouts.app')

@section('content')
<div style="margin-bottom: 1rem; display: flex; justify-content: space-between; align-items: center;">
    <div style="display: flex; align-items: center; gap: 1rem;">
        <a href="{{ route('client.dashboard') }}" class="btn btn-outline btn-sm">← Retour</a>
        <div>
            <h2 style="margin: 0;">{{ $centrex->name }}</h2>
            <p style="font-size: 0.875rem; color: var(--text-secondary); margin: 0;">
                {{ $centrex->ip_address }}
                @if($centrex->status === 'online')
                    <span style="background-color: var(--color-success); color: white; padding: 0.125rem 0.5rem; border-radius: 12px; font-size: 0.75rem; margin-left: 0.5rem;">● En ligne</span>
                @endif
            </p>
        </div>
    </div>
</div>

<div class="card" style="padding: 0; overflow: hidden; height: calc(100vh - 200px); min-height: 600px;">
    <iframe 
        src="http://54.38.1.103:8080/proxy/{{ $centrex->ip_address }}/admin"
        style="width: 100%; height: 100%; border: none;"
        title="{{ $centrex->name }}"
    ></iframe>
</div>

<div style="margin-top: 1rem; padding: 1rem; background-color: var(--bg-tertiary); border-radius: var(--border-radius); text-align: center;">
    <p style="font-size: 0.875rem; color: var(--text-secondary); margin: 0;">
        🔒 Connexion sécurisée centralisée. Seule l'IP du dashboard est autorisée sur les centrex.
    </p>
</div>
@endsection