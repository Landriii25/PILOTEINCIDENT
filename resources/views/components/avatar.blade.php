@props([
  'name' => null,
  'url'  => null,
  'size' => 36,
  'class'=> ''
])

@php
  $src = $url;
  if(!$src) {
      $label = urlencode($name ?? 'U');
      $src = "https://ui-avatars.com/api/?name={$label}&size=128&background=4f46e5&color=ffffff&format=png";
  }
@endphp

<img src="{{ $src }}" width="{{ $size }}" height="{{ $size }}"
     class="rounded-circle shadow-sm {{ $class }}" alt="{{ $name ??'Avatar'}}">
