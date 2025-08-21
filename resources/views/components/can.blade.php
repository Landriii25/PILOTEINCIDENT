@props(['perm'])

@can($perm)
    {{ $slot }}
@endcan
