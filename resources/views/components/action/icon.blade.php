@props([
    'href'    => '#',
    'icon'    => 'fas fa-question-circle',
    'title'   => '',
    'color'   => 'secondary', // bootstrap color: primary, danger, warning...
])

<a href="{{ $href }}"
   title="{{ $title }}"
   data-toggle="tooltip"
   {{ $attributes->merge(['class' => "btn btn-sm btn-outline-$color"]) }}>
   <i class="{{ $icon }}"></i>
</a>
