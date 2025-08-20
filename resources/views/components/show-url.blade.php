@props(['url' => null, 'full' => false, 'clickable' => false])

@if ($url)
    @php
        $url = $full ? $url : \Illuminate\Support\Str::limit($url, 50);
    @endphp

    <div class="flex flex-row items-center justify-start gap-2" role="group" aria-label="Lien externe">
        <flux:icon.link class="size-4" aria-hidden="true" />

        @if ($clickable)
            <a class="underline" href="{{ $url }}" target="_blank" rel="noopener noreferrer">
                {{ $url }}
            </a>
        @else
            <p class="underline">
                {{ $url }}
            </p>
        @endif
    </div>
@endif
