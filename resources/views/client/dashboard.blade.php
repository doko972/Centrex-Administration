@extends('layouts.app')

@section('content')
    <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h1>Mes Centrex</h1>
            <p style="color: var(--text-secondary); margin-top: 0.5rem;">{{ $client->company_name }}</p>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-danger">Déconnexion</button>
        </form>
    </div>

    <div class="card" style="margin-bottom: 2rem;">
        <h3>Bienvenue, {{ Auth::user()->name }} !</h3>
        <p>Voici la liste de vos centrex FreePBX. Cliquez sur un centrex pour y accéder.</p>
    </div>

    @if ($centrex->count() > 0)
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem;">
            @foreach ($centrex as $item)
                <div class="card" style="cursor: pointer; transition: all 0.3s ease;"
                    onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    @if ($item->image)
                        <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}"
                            style="width: 100%; height: 180px; object-fit: cover; border-radius: var(--border-radius); margin-bottom: 1rem;">
                    @else
                        <div
                            style="width: 100%; height: 180px; background-color: var(--bg-tertiary); border-radius: var(--border-radius); margin-bottom: 1rem; display: flex; align-items: center; justify-content: center; color: var(--text-tertiary); font-size: 3rem;">
                            📞
                        </div>
                    @endif

                    <h3 style="margin-bottom: 0.5rem;">{{ $item->name }}</h3>

                    @if ($item->description)
                        <p style="font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 1rem;">
                            {{ Str::limit($item->description, 80) }}
                        </p>
                    @endif

                    <div style="margin-bottom: 1rem;">
                        @if ($item->status === 'online')
                            <span
                                style="background-color: var(--color-success); color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.875rem;">●
                                En ligne</span>
                        @elseif($item->status === 'offline')
                            <span
                                style="background-color: var(--color-danger); color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.875rem;">●
                                Hors ligne</span>
                        @else
                            <span
                                style="background-color: var(--color-warning); color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.875rem;">●
                                Maintenance</span>
                        @endif
                    </div>

                    <div style="font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 1.5rem;">
                        <strong>IP:</strong> {{ $item->ip_address }}{{ $item->port != 80 ? ':' . $item->port : '' }}
                    </div>

                    <div style="display: flex; gap: 0.5rem;">
                        <a href="{{ route('client.centrex.nginx-proxy', $item) }}" class="btn btn-primary"
                            style="width: 100%;">
                            Accéder au Centrex 🔒
                        </a>
                        <a href="{{ route('client.centrex.access', $item) }}" class="btn btn-outline btn-sm"
                            style="flex: 1;">
                            Accès direct
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="card">
            <p class="text-secondary" style="text-align: center; padding: 2rem;">
                Aucun centrex n'est actuellement associé à votre compte.<br>
                Veuillez contacter votre administrateur.
            </p>
        </div>
    @endif
@endsection
