@extends('layouts.app')

@section('content')
<div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
    <h1>Gestion des Centrex</h1>
    <div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline">← Retour</a>
        <a href="{{ route('admin.centrex.create') }}" class="btn btn-primary">+ Nouveau Centrex</a>
    </div>
</div>

@if(session('success'))
    <div style="background-color: var(--color-success); color: white; padding: 1rem; border-radius: var(--border-radius); margin-bottom: 1.5rem;">
        {{ session('success') }}
    </div>
@endif

<div class="card">
    @if($centrex->count() > 0)
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem;">
            @foreach($centrex as $item)
                <div style="border: 1px solid var(--border-color); border-radius: var(--border-radius); padding: 1.5rem; transition: all 0.3s ease;">
                    @if($item->image)
                        <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" style="width: 100%; height: 150px; object-fit: cover; border-radius: var(--border-radius); margin-bottom: 1rem;">
                    @else
                        <div style="width: 100%; height: 150px; background-color: var(--bg-tertiary); border-radius: var(--border-radius); margin-bottom: 1rem; display: flex; align-items: center; justify-content: center; color: var(--text-tertiary);">
                            Aucune image
                        </div>
                    @endif

                    <h3 style="margin-bottom: 0.5rem;">{{ $item->name }}</h3>
                    <p style="font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 1rem;">
                        {{ $item->ip_address }}:{{ $item->port }}
                    </p>

                    <div style="margin-bottom: 1rem;">
                        @if($item->status === 'online')
                            <span style="background-color: var(--color-success); color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.875rem;">● En ligne</span>
                        @elseif($item->status === 'offline')
                            <span style="background-color: var(--color-danger); color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.875rem;">● Hors ligne</span>
                        @else
                            <span style="background-color: var(--color-warning); color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.875rem;">● Maintenance</span>
                        @endif

                        @if(!$item->is_active)
                            <span style="background-color: var(--color-danger); color: white; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.875rem; margin-left: 0.5rem;">Inactif</span>
                        @endif
                    </div>

                    <div style="font-size: 0.875rem; color: var(--text-secondary); margin-bottom: 1rem;">
                        <strong>Clients associés:</strong> {{ $item->clients->count() }}
                    </div>

                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                        <a href="{{ route('admin.centrex.show', $item) }}" class="btn btn-sm btn-primary">Voir</a>
                        <a href="{{ route('admin.centrex.edit', $item) }}" class="btn btn-sm btn-secondary">Modifier</a>
                        <form method="POST" action="{{ route('admin.centrex.destroy', $item) }}" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce centrex ?')">Supprimer</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-secondary" style="text-align: center; padding: 2rem;">Aucun centrex pour le moment.</p>
    @endif
</div>
@endsection