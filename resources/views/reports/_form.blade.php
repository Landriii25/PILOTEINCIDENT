@php
    $editing = isset($report);
@endphp

@csrf
@if($editing)
    @method('PUT')
@endif

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" class="form-control" rows="4" required>{{ old('description', $report->description ?? '') }}</textarea>
        </div>
    </div>
    <div class="col-md-6">
        <label>Constats</label>
        <textarea name="constats" class="form-control" rows="4" required>{{ old('constats', $report->constats ?? '') }}</textarea>
    </div>

    <div class="col-md-6">
        <label>Causes</label>
        <textarea name="causes" class="form-control" rows="4" required>{{ old('causes', $report->causes ?? '') }}</textarea>
    </div>
    <div class="col-md-6">
        <label>Plan d’actions & remédiation</label>
        <textarea name="actions" class="form-control" rows="4" required>{{ old('actions', $report->actions ?? '') }}</textarea>
    </div>

    <div class="col-md-6">
        <label>Impacts</label>
        <textarea name="impacts" class="form-control" rows="4" required>{{ old('impacts', $report->impacts ?? '') }}</textarea>
    </div>
    <div class="col-md-6">
        <label>Recommandation (optionnel)</label>
        <textarea name="recommendation" class="form-control" rows="4">{{ old('recommendation', $report->recommendation ?? '') }}</textarea>
    </div>

    <div class="col-md-4">
        <label>Début incident</label>
        <input type="datetime-local" name="started_at" class="form-control"
               value="{{ old('started_at', isset($report->started_at) ? $report->started_at->format('Y-m-d\TH:i') : '') }}" required>
    </div>
    <div class="col-md-4">
        <label>Fin incident</label>
        <input type="datetime-local" name="ended_at" class="form-control"
               value="{{ old('ended_at', isset($report->ended_at) ? $report->ended_at->format('Y-m-d\TH:i') : '') }}" required>
    </div>
    <div class="col-md-4">
        <label>Durée (calculée)</label>
        <input type="text" class="form-control" id="durationDisplay" value="{{ isset($report->duration_minutes) ? floor($report->duration_minutes/60).'h '.($report->duration_minutes%60).'m' : '—' }}" readonly>
        <small class="text-muted">Sera recalculée à l’enregistrement.</small>
    </div>
</div>

<div class="mt-3 d-flex justify-content-end">
    <a href="{{ url()->previous() }}" class="btn btn-light mr-2">Annuler</a>
    <button class="btn btn-primary">
        <i class="fas fa-save mr-1"></i> Enregistrer le rapport
    </button>
</div>

@push('js')
<script>
(function(){
    function recompute() {
        const s = document.querySelector('[name="started_at"]').value;
        const e = document.querySelector('[name="ended_at"]').value;
        const out = document.getElementById('durationDisplay');
        if(!s || !e){ out.value = '—'; return; }
        const start = new Date(s);
        const end   = new Date(e);
        const diffM = Math.max(0, Math.round((end - start)/60000));
        const h = Math.floor(diffM/60), m = diffM % 60;
        out.value = h+'h '+m+'m';
    }
    document.querySelector('[name="started_at"]').addEventListener('change',recompute);
    document.querySelector('[name="ended_at"]').addEventListener('change',recompute);
})();
</script>
@endpush
