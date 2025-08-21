@props([
    'action',                // URL de suppression (obligatoire)
    'method'   => 'DELETE',  // Méthode spoofée
    'confirm'  => "Confirmer la suppression ?", // Message confirm()
    'small'    => false,     // Bouton compact
    'icon'     => false,     // Icône seule (pas de texte)
    'label'    => 'Supprimer',
])

@php
$btnSize = $small ? 'btn-sm' : '';
$btnCls  = 'btn btn-outline-danger '.$btnSize;
@endphp

<form action="{{ $action }}" method="POST" class="d-inline-block"
      onsubmit="return confirm(@js($confirm));">
    @csrf
    @method($method)

    @if($icon)
        <button type="submit" {{ $attributes->merge(['class' => $btnCls, 'title' => $label]) }}>
            <i class="far fa-trash-alt"></i>
        </button>
    @else
        <button type="submit" {{ $attributes->merge(['class' => $btnCls]) }}>
            <i class="far fa-trash-alt mr-1"></i>{{ $label }}
        </button>
    @endif
</form>
