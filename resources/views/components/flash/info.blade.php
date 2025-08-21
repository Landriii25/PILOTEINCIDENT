@props([
    'message' => session('info'),
    'timeout' => 3000,
])

@if($message)
<div {{ $attributes->merge(['class'=>'alert alert-info alert-dismissible fade show shadow-sm mb-3','role'=>'alert']) }}
     @if($timeout>0) data-timeout="{{ $timeout }}" @endif>
    <i class="fas fa-info-circle mr-1"></i>
    {!! e($message) !!}
    <button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
        <span aria-hidden="true">&times;</span>
    </button>
</div>

@push('js')
<script>
document.querySelectorAll('.alert[data-timeout]').forEach(function(el){
    const t = parseInt(el.getAttribute('data-timeout'), 10);
    if(t>0){ setTimeout(function(){ $(el).alert('close'); }, t); }
});
</script>
@endpush
@endif
