{{-- resources/views/partials/flash.blade.php --}}
@php
    $map = [
        'success' => 'alert-success',
        'error'   => 'alert-danger',
        'warning' => 'alert-warning',
        'info'    => 'alert-info',
    ];
@endphp

@foreach ($map as $key => $class)
    @if(session($key))
        <div class="alert {{ $class }} alert-dismissible fade show" role="alert" style="border-radius:10px;">
            {!! session($key) !!}
            <button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif
@endforeach

@push('js')
<script>
  // Auto-fermeture des flashs après 4s
  setTimeout(function(){
    $('.alert.alert-dismissible').alert('close');
  }, 4000);
</script>
@endpush
