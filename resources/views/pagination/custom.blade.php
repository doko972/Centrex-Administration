@if ($paginator->hasPages())
    <nav style="display: flex; align-items: center; justify-content: space-between; gap: 1rem; margin-top: 1.5rem; flex-wrap: wrap;">
        <p style="margin: 0; font-size: 0.875rem; color: var(--text-secondary);">
            {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} sur {{ $paginator->total() }}
        </p>

        <div style="display: flex; gap: 0.5rem; align-items: center;">
            @if ($paginator->onFirstPage())
                <span class="btn btn-ghost btn-sm" style="opacity: 0.5; pointer-events: none;">← Précédent</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-ghost btn-sm">← Précédent</a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span style="padding: 0 0.35rem; color: var(--text-tertiary);">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="btn btn-primary btn-sm">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="btn btn-ghost btn-sm">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-ghost btn-sm">Suivant →</a>
            @else
                <span class="btn btn-ghost btn-sm" style="opacity: 0.5; pointer-events: none;">Suivant →</span>
            @endif
        </div>
    </nav>
@endif
