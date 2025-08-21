@props(['title' => 'Veuillez corriger les erreurs suivantes :'])

@if ($errors->any())
<div class="alert alert-danger shadow-sm mb-3" role="alert">
    <div class="d-flex align-items-start">
        <i class="fas fa-exclamation-triangle fa-lg mr-2 mt-1"></i>
        <div>
            <strong>{{ $title }}</strong>
            <ul class="mb-0 mt-2 pl-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endif
