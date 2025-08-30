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
<div class="row">
  <div class="col-lg-8">
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

        <form method="POST" action="{{ route('incidents.update', $incident) }}">
          @csrf
          @method('PUT')

          <div class="form-group">
            <label for="titre">Titre <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="titre" id="titre" required
                   value="{{ old('titre', $incident->titre) }}">
          </div>

          <div class="form-group">
            <label for="description">Description</label>
            <textarea class="form-control" name="description" id="description" rows="4">{{ old('description', $incident->description) }}</textarea>
          </div>

          <div class="form-row">
            <div class="form-group col-md-4">
              <label for="priorite">Priorité <span class="text-danger">*</span></label>
              <select class="form-control" name="priorite" id="priorite" required>
                @foreach(\App\Models\Incident::PRIORITES as $p)
                  <option value="{{ $p }}" @selected(old('priorite', $incident->priorite)===$p)>{{ $p }}</option>
                @endforeach
              </select>
            </div>

            <div class="form-group col-md-4">
              <label for="statut">Statut</label>
              <select class="form-control" name="statut" id="statut">
                @php $statuts=['Ouvert','En cours','Résolu','Fermé']; @endphp
                @foreach($statuts as $s)
                  <option value="{{ $s }}" @selected(old('statut',$incident->statut)===$s)>{{ $s }}</option>
                @endforeach
              </select>
            </div>

            <div class="form-group col-md-4">
              <label for="application_id">Application <span class="text-danger">*</span></label>
              <select class="form-control" name="application_id" id="application_id" required>
                @foreach($applications as $apps)
                  <option value="{{ $app->id }}"
                          data-service="{{ $app->service_id ?? '' }}"
                          @selected(old('application_id', $incident->application_id)==$app->id)>
                      {{ $app->nom }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Service (déduit de l’application)</label>
              <input type="text" class="form-control" id="service_label" value="—" readonly>
            </div>
            <div class="form-group col-md-6">
              <label for="technicien_id">Technicien assigné</label>
              <select class="form-control" name="technicien_id" id="technicien_id">
                <option value="">— Aucun —</option>
                @foreach($techniciens as $t)
                  <option value="{{ $t->id }}"
                          data-service="{{ $t->service_id ?? '' }}"
                          @selected(old('technicien_id', $incident->technicien_id)==$t->id)>
                      {{ $t->name }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>

          <button type="submit" class="btn btn-primary">
            <i class="fas fa-save mr-1"></i> Mettre à jour
          </button>
          <a href="{{ route('incidents.show', $incident) }}" class="btn btn-outline-secondary ml-1">Annuler</a>
        </form>

      </div>
    </div>
  </div>
</div>
@endsection

@section('js')
<script>
(function () {
    const $appSel   = document.getElementById('application_id');
    const $svcLabel = document.getElementById('service_label');
    const $techSel  = document.getElementById('technicien_id');

    const serviceNames = {}; // optionnel

    function refreshFromApp() {
        const opt = $appSel.options[$appSel.selectedIndex];
        const svc = opt ? opt.getAttribute('data-service') : '';
        $svcLabel.value = svc ? (serviceNames[svc] || ('Service #' + svc)) : '—';
        filterTechs(svc);
    }

    function filterTechs(svcId) {
        for (const opt of $techSel.options) {
            if (!opt.value) { opt.hidden=false; continue; }
            const techSvc = opt.getAttribute('data-service') || '';
            opt.hidden = (svcId && techSvc && techSvc !== String(svcId));
        }
        const current = $techSel.options[$techSel.selectedIndex];
        if (current && current.hidden) $techSel.value = '';
    }

    $appSel.addEventListener('change', refreshFromApp);
    // Init avec les valeurs existantes
    refreshFromApp();
})();
</script>
@endsection
