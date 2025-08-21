@php
    /** @var \App\Models\Service|null $service */
    $chefs   = $chefs ?? collect();
    $submit  = $submit ?? 'Enregistrer';
@endphp

<div class="card-body">
    <div class="form-group">
        <label for="nom">Nom du service <span class="text-danger">*</span></label>
        <input type="text" name="nom" id="nom" class="form-control"
               value="{{ old('nom', $service->nom ?? '') }}" required>
    </div>

    <div class="form-group">
        <label for="description">Description (optionnel)</label>
        <textarea name="description" id="description" rows="3" class="form-control"
                  placeholder="Brève description du périmètre">{{ old('description', $service->description ?? '') }}</textarea>
    </div>

    <div class="form-group">
        <label for="chef_id">Chef de service</label>
        <select name="chef_id" id="chef_id" class="form-control">
            <option value="">— Aucun —</option>
            @foreach($chefs as $u)
                <option value="{{ $u->id }}"
                    @selected(old('chef_id', $service->chef_id ?? null) == $u->id)>
                    {{ $u->name }} ({{ $u->email }})
                </option>
            @endforeach
        </select>
        <small class="form-text text-muted">
            Astuce : filtre “superviseur” côté contrôleur si besoin.
        </small>
    </div>
</div>

<div class="card-footer d-flex justify-content-between">
    <a href="{{ route('services.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left mr-1"></i> Retour
    </a>
    <button type="submit" class="btn btn-primary">
        <i class="fas fa-save mr-1"></i> {{ $submit }}
    </button>
</div>
