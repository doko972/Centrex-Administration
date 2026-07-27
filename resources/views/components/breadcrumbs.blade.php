@props(['items' => []])

<nav class="breadcrumbs" aria-label="Fil d'Ariane">
    @foreach($items as $index => $item)
        @if($index > 0)
            <span class="breadcrumbs-separator">/</span>
        @endif

        @if(!empty($item['url']) && $index < count($items) - 1)
            <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
        @else
            <span class="breadcrumbs-current">{{ $item['label'] }}</span>
        @endif
    @endforeach
</nav>
