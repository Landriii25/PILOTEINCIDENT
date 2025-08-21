{{-- Affiche un message flash d’erreur (session('error')) --}}
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-3">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        {!! session('error') !!}
        <button type="button" class="close" data-dismiss="alert" aria-label="Fermer">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif
