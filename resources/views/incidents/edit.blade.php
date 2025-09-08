@extends('adminlte::page')

@section('title', "Modifier {$incident->code}")

@section('content_header')
    <div class="d-flex align-items-center justify-content-between">
        <h1 class="mb-0">Modifier l’incident</h1>
        <span class="badge badge-dark" style="font-size:1rem;padding:.5rem .7rem;letter-spacing:.5px">
            {{ $incident->code }}
        </span>
    </div>
@endsection

@section('content')
<div class="card">
    <div class="card-body">

        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Oups…</strong> Merci de corriger les erreurs ci-dessous.
                <ul class="mt-2 mb-0">
                    @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('incidents.update', $incident) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="titre">Titre <span class="text-danger">*</span></label>
                <input type="text" class="form-control @error('titre') is-invalid @enderror" name="titre" id="titre" required
                       value="{{ old('titre', $incident->titre) }}">
                @error('titre') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="form-group">
                <label for="description">Description</label>
                <textarea class="form-control @error('description') is-invalid @enderror" name="description" id="description" rows="4">{{ old('description', $incident->description) }}</textarea>
                @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="priorite">Priorité <span class="text-danger">*</span></label>
                    <select class="form-control @error('priorite') is-invalid @enderror" name="priorite" id="priorite" required>
                        @foreach(\App\Models\Incident::PRIORITES as $p)
                            <option value="{{ $p }}" @selected(old('priorite', $incident->priorite)===$p)>{{ $p }}</option>
                        @endforeach
                    </select>
                    @error('priorite') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group col-md-4">
                    <label for="statut">Statut <span class="text-danger">*</span></label>
                    <select class="form-control @error('statut') is-invalid @enderror" name="statut" id="statut" required>
                        @php $statuts=['Ouvert','En cours','Résolu','Fermé']; @endphp
                        @foreach($statuts as $s)
                            <option value="{{ $s }}" @selected(old('statut',$incident->statut)===$s)>{{ $s }}</option>
                        @endforeach
                    </select>
                    @error('statut') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group col-md-4">
                    <label for="application_id">Application <span class="text-danger">*</span></label>
                    <select class="form-control @error('application_id') is-invalid @enderror" name="application_id" id="application_id" required>
                        @foreach($apps as $app)
                            <option value="{{ $app->id }}" @selected(old('application_id', $incident->application_id)==$app->id)>
                                {{ $app->nom }}
                            </option>
                        @endforeach
                    </select>
                    @error('application_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Service (déduit de l’application)</label>
                    <input type="text" class="form-control" id="service_name" readonly>
                    {{-- CORRECTION 1 : Ajout du champ caché pour envoyer le service_id --}}
                    <input type="hidden" name="service_id" id="service_id" value="{{ old('service_id', $incident->service_id) }}">
                </div>
                <div class="form-group col-md-6">
                    <label for="technicien_id">Technicien assigné</label>
                    <select class="form-control @error('technicien_id') is-invalid @enderror" name="technicien_id" id="technicien_id">
                        <option value="">— Aucun —</option>
                        @foreach($techs as $t)
                            <option value="{{ $t->id }}" data-service="{{ $t->service_id ?? '' }}" @selected(old('technicien_id', $incident->technicien_id)==$t->id)>
                                {{ $t->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('technicien_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i> Mettre à jour
            </button>
            <a href="{{ route('incidents.show', $incident) }}" class="btn btn-outline-secondary ml-1">Annuler</a>
        </form>
    </div>
</div>
@endsection

@section('js')
<script>
// CORRECTION 2 : On utilise la même "map" que sur la page de création pour plus de robustesse
const APP_SERVICES = @json($mapAppServices ?? []);

const appSelect = document.getElementById('application_id');
const serviceNameInput = document.getElementById('service_name');
const serviceIdInput = document.getElementById('service_id');
const techSelect = document.getElementById('technicien_id');

function refreshServiceAndTech() {
    const appId = appSelect.value;
    const service = APP_SERVICES[appId] || null;

    // Mettre à jour les champs du service
    if (service && service.id) {
        serviceNameInput.value = service.nom || 'Service non trouvé';
        serviceIdInput.value = service.id;
    } else {
        serviceNameInput.value = '—';
        serviceIdInput.value = '';
    }

    // Filtrer la liste des techniciens
    const serviceId = service ? String(service.id) : null;
    techSelect.querySelectorAll('option').forEach(option => {
        if (!option.value) return;
        const technicienServiceId = option.getAttribute('data-service');
        const shouldBeVisible = !serviceId || (technicienServiceId === serviceId);
        option.style.display = shouldBeVisible ? '' : 'none';
        if (!shouldBeVisible && option.selected) {
            techSelect.value = '';
        }
    });
}

appSelect.addEventListener('change', refreshServiceAndTech);
document.addEventListener('DOMContentLoaded', refreshServiceAndTech);
</script>
@endsection
