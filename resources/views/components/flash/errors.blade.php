@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-3">
        <strong class="mr-1">Veuillez corriger les erreurs :</strong>
        <ul class="mb-0 pl-3">
            @foreach ($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
        <button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif
