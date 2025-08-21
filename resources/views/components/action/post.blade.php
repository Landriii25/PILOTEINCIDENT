@props([
    'action',                   // URL ou route() déjà résolue
    'method' => 'POST',         // POST | PUT | PATCH | DELETE (spoof si ≠ POST)
    'params' => [],             // tableau clé => valeur pour <input type="hidden">
    'icon' => 'fas fa-check',   // ex: 'fas fa-check'
    'text' => 'Valider',        // libellé du bouton
    'size' => 'sm',             // xs|sm|md|lg (AdminLTE: 'btn-sm' par défaut)
    'class' => 'btn-primary',   // classes bouton (ex: 'btn-success')
    'confirm' => null,          // message de confirmation (null = pas de confirm)
    'title' => null,            // title sur le bouton (tooltip natif)
    'disabled' => false,        // bool
])

@php
    $formMethod = strtoupper($method ?? 'POST');
    $needsSpoof = $formMethod !== 'POST';
@endphp

<form action="{{ $action }}" method="POST" class="d-inline-block"
      @if($confirm) onsubmit="return confirm(@js($confirm))" @endif>
    @csrf
    @if($needsSpoof)
        @method($formMethod)
    @endif

    @foreach($params as $k => $v)
        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
    @endforeach

    <button type="submit"
            @class(['btn', "btn-{$size}", $class])
            @if($title) title="{{ $title }}" @endif
            @if($disabled) disabled @endif>
        @if($icon)<i class="{{ $icon }} mr-1"></i>@endif
        {{ $text }}
    </button>
</form>
