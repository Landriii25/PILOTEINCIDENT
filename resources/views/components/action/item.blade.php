@props([
    // Lien direct
    'href'    => null,

    // Action de formulaire (si pas de href)
    'action'  => null,
    'method'  => 'post',    // post|delete|put|patch

    // Apparence & accessibilité
    'icon'    => null,      // ex: "fas fa-edit"
    'color'   => null,      // ex: "text-danger" ou "text-primary"
    'title'   => null,      // tooltip
    'confirm' => null,      // texte de confirmation JS
])

@php
    $classes = 'dropdown-item d-flex align-items-center';
    // Espace entre icône et texte si texte présent
    $hasText = trim($slot) !== '';
@endphp

@if($href)
    <a href="{{ $href }}"
       {{ $attributes->merge(['class' => $classes . ($color ? ' '.$color : '')]) }}
       @if($title) data-toggle="tooltip" title="{{ $title }}" @endif
       @if($confirm) onclick="return confirm(@js($confirm));" @endif>
        @if($icon)
            <i class="{{ $icon }} {{ $hasText ? 'mr-2' : '' }}"></i>
        @endif
        {{ $slot }}
    </a>
@else
    <form action="{{ $action }}" method="POST" class="m-0 p-0 d-inline">
        @csrf
        @if(in_array(strtolower($method), ['delete','put','patch']))
            @method(strtoupper($method))
        @endif

        <button type="submit"
                {{ $attributes->merge(['class' => $classes . ($color ? ' '.$color : '')]) }}
                @if($title) data-toggle="tooltip" title="{{ $title }}" @endif
                @if($confirm) onclick="return confirm(@js($confirm));" @endif>
            @if($icon)
                <i class="{{ $icon }} {{ $hasText ? 'mr-2' : '' }}"></i>
            @endif
            {{ $slot }}
        </button>
    </form>
@endif
