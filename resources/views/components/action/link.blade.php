@props([
    'href',
    'icon' => null,
    'size' => 'sm',
    'class' => 'btn-primary',
    'title' => null,
])

<a href="{{ $href }}" @class(["btn","btn-{$size}", $class]) title="{{ $title }}">
    @if($icon)<i class="{{ $icon }} mr-1"></i>@endif
    {{ $slot }}
</a>

