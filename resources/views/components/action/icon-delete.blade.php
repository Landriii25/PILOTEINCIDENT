@props([
    'action'  => '#',
    'icon'    => 'far fa-trash-alt',
    'title'   => 'Supprimer',
    'confirm' => 'Supprimer cet élément ?',
    'color'   => 'danger',
])

<form method="POST" action="{{ $action }}" class="d-inline">
    @csrf
    @method('DELETE')
    <button type="submit"
            onclick="return confirm(@js($confirm));"
            title="{{ $title }}"
            data-toggle="tooltip"
            {{ $attributes->merge(['class' => "btn btn-sm btn-outline-$color"]) }}>
        <i class="{{ $icon }}"></i>
    </button>
</form>
