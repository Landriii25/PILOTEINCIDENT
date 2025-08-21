@extends('adminlte::page')

@section('title', 'Rapport de l’incident '.$incident->code)

@section('content_header')
    <div class="d-flex align-items-center justify-content-between">
        <div>
            <h1 class="mb-0">Rapport d’intervention</h1>
            <div class="text-muted small mt-1">
                Incident <strong>{{ $incident->code }}</strong> •
                Appli : {{ optional($incident->application)->nom ?? '—' }} •
                Priorité :
                <span class="badge badge-{{ ['Critique'=>'danger','Haute'=>'warning','Moyenne'=>'info','Basse'=>'secondary'][$incident->priorite] ?? 'secondary' }}">
                    {{ $incident->priorite ?? '—' }}
                </span>
            </div>
        </div>
        <a href="{{ route('incidents.show', $incident) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left mr-1"></i> Retour à l’incident
        </a>
    </div>
@endsection

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Oups…</strong> merci de corriger les erreurs ci‑dessous.
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('reports.store_for_incident', $incident) }}">
        @csrf

        <div class="card">
            <div class="card-header py-2">
                <h3 class="card-title mb-0">Informations d’intervention</h3>
            </div>
            <div class="card-body">
                {{-- Description globale --}}
                <div class="form-group">
                    <label class="mb-1">Description de l’intervention <span class="text-danger">*</span></label>
                    <textarea name="description" rows="3" required
                             class="form-control @error('description') is-invalid @enderror"
                             placeholder="Décrire brièvement l’intervention (contexte, périmètre)…">{{ old('description') }}</textarea>
                    @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Grille 3 colonnes : Constats, Causes, Actions --}}
                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label class="mb-1">Constats <span class="text-danger">*</span></label>
                        <textarea name="constats" rows="4" required
                                 class="form-control @error('constats') is-invalid @enderror"
                                 placeholder="Symptômes observés, logs, métriques…">{{ old('constats') }}</textarea>
                        @error('constats') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group col-md-4">
                        <label class="mb-1">Causes <span class="text-danger">*</span></label>
                        <textarea name="causes" rows="4" required
                                 class="form-control @error('causes') is-invalid @enderror"
                                 placeholder="Racine du problème (RCA), éléments déclencheurs…">{{ old('causes') }}</textarea>
                        @error('causes') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group col-md-4">
                        <label class="mb-1">Actions menées <span class="text-danger">*</span></label>
                        <textarea name="actions" rows="4" required
                                 class="form-control @error('actions') is-invalid @enderror"
                                 placeholder="Étapes de remédiation, commandes exécutées…">{{ old('actions') }}</textarea>
                        @error('actions') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Impacts + Recommandations --}}
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="mb-1">Impacts <span class="text-danger">*</span></label>
                        <textarea name="impacts" rows="3" required
                                 class="form-control @error('impacts') is-invalid @enderror"
                                 placeholder="Périmètre affecté, durée d’indisponibilité, utilisateurs touchés…">{{ old('impacts') }}</textarea>
                        @error('impacts') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group col-md-6">
                        <label class="mb-1">Recommandations</label>
                        <textarea name="recommendation" rows="3"
                                 class="form-control @error('recommendation') is-invalid @enderror"
                                 placeholder="Prévention, durcissement, automatisation, documentation…">{{ old('recommendation') }}</textarea>
                        @error('recommendation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Timing intervention --}}
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="mb-1">Début de l’intervention <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="started_at" required
                               value="{{ old('started_at') }}"
                               class="form-control @error('started_at') is-invalid @enderror">
                        @error('started_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group col-md-6">
                        <label class="mb-1">Fin de l’intervention <span class="text-danger">*</span></label>
                        <input type="datetime-local" name="ended_at" required
                               value="{{ old('ended_at') }}"
                               class="form-control @error('ended_at') is-invalid @enderror">
                        @error('ended_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                {{-- Durée estimée (live) --}}
                <div class="alert alert-light border mt-2 mb-0" id="durationPreview" style="display:none;">
                    <i class="far fa-clock mr-1"></i>
                    Durée estimée : <strong><span data-role="duration-text">—</span></strong>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-between">
                <a href="{{ route('incidents.show', $incident) }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Retour
                </a>
                <button class="btn btn-success">
                    <i class="fas fa-save mr-1"></i> Enregistrer le rapport
                </button>
            </div>
        </div>
    </form>
@endsection

@push('js')
<script>
    // Petit helper pour afficher la durée entre start/end
    const $start = document.querySelector('input[name="started_at"]');
    const $end   = document.querySelector('input[name="ended_at"]');
    const $wrap  = document.getElementById('durationPreview');
    const $txt   = $wrap?.querySelector('[data-role="duration-text"]');

    function fmt(mins){
        const h = Math.floor(mins/60);
        const m = mins%60;
        if (h <= 0) return ${m} min;
        if (m <= 0) return ${h} h;
        return ${h} h ${m} min;
    }

    function updateDuration(){
        if (!$start || !$end || !$wrap || !$txt) return;
        const s = $start.value ? new Date($start.value) : null;
        const e = $end.value   ? new Date($end.value)   : null;
        if (!s || !e || isNaN(s) || isNaN(e) || e <= s) { $wrap.style.display='none'; return; }
        const diffMin = Math.round((e - s) / 60000);
        $txt.textContent = fmt(diffMin);
        $wrap.style.display = 'block';
    }

    $start?.addEventListener('change', updateDuration);
    $end?.addEventListener('change', updateDuration);
    document.addEventListener('DOMContentLoaded', updateDuration);
</script>
@endpush
