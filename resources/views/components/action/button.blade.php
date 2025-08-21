@props([
    // Autorisation (permission Spatie ou Gate ability). Si null => pas de filtre.
    'permission' => null,

    // Lien direct OU action de formulaire (un seul des deux)
    'href'   => null,
    'action' => null,

    // Méthode HTTP pour le formulaire
    'method' => 'POST',

    // Apparence
    'icon'   => null,             // ex: 'fas fa-eye'
    'label'  => null,             // ex: 'Voir'
    'variant'=> 'primary',        // primary|success|info|warning|danger|secondary|outline
    'size'   => 'sm',             // xs|sm|md|lg

    // UX
    'tooltip'=> null,             // text du title (active le tooltip)
    'confirm'=> null,             // message de confirmation JS, ex: "Confirmer ?"
    'disabled' => false,
])

@php
    // map taille
    $sizeClass = match($size) {
        'xs' => 'btn-xs',
        'sm' => 'btn-sm',
        'lg' => 'btn-lg',
        default => '',
    };

    // map variante
    $variantClass = match($variant) {
        'success'   => 'btn-success',
        'info'      => 'btn-info',
        'warning'   => 'btn-warning',
        'danger'    => 'btn-danger',
        'secondary' => 'btn-secondary',
        'outline'   => 'btn-outline-secondary',
        default     => 'btn-primary',
    };

    $classes = trim("btn {$variantClass} {$sizeClass} ".($attributes->get('class')));
    $title   = $tooltip ?? $label;
@endphp

{{-- Si permission fournie et refusée -> ne rien rendre --}}
@if($permission && ! auth()->user()?->can($permission))
    {{-- Rien --}}
@else
    @if($href)
        <a href="{{ $href }}"
           @if($tooltip) title="{{ $title }}" data-toggle="tooltip" @endif
           class="{{ $classes }}"
           @if($disabled) aria-disabled="true" tabindex="-1" @endif
        >
            @if($icon)<i class="{{ $icon }} mr-1"></i>@endif
            {{ $label }}
        </a>
    @elseif($action)
        <form action="{{ $action }}" method="POST" class="d-inline">
            @csrf
            @php($upper = strtoupper($method))
            @if(!in_array($upper, ['GET','POST'])) @method($upper) @endif

            <button type="submit"
                    class="{{ $classes }}"
                    @if($tooltip) title="{{ $title }}" data-toggle="tooltip" @endif
                    @if($disabled) disabled @endif
                    @if($confirm) onclick="return confirm(@js($confirm))" @endif
            >
                @if($icon)<i class="{{ $icon }} mr-1"></i>@endif
                {{ $label }}
            </button>
        </form>
    @endif
@endif

@once
    @push('js')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                if (window.$ && typeof $.fn.tooltip === 'function') {
                    $('[data-toggle="tooltip"]').tooltip();
                }
            });
        </script>
    @endpush
@endonce
