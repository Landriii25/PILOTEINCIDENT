@props([
    'title' => 'Aucun élément',
    'message' => 'Rien à afficher pour le moment.',
    'icon' => 'far fa-folder-open',
    'ctaText' => null,
    'ctaUrl' => null,
])

<div class="card border-0 shadow-sm">
    <div class="card-body text-center py-5">
        <div class="mb-3">
            <span class="d-inline-flex align-items-center justify-content-center rounded-circle bg-light"
                  style="width:72px;height:72px;">
                <i class="{{ $icon }} text-secondary" style="font-size:28px;"></i>
            </span>
        </div>
        <h5 class="mb-2">{{ $title }}</h5>
        <p class="text-muted mb-3">{{ $message }}</p>

        @if($ctaText && $ctaUrl)
            <a href="{{ $ctaUrl }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i> {{ $ctaText }}
            </a>
        @endif

        {{-- Slot facultatif pour contenu additionnel --}}
        {{ $slot }}
    </div>
</div>
