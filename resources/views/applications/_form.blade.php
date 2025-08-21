@csrf
<div class="form-row">
    <div class="form-group col-md-6">
        <label>Nom</label>
        <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror"
               value="{{ old('nom', $application->nom ?? '') }}" required>
        @error('nom')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="form-group col-md-6">
        <label>Statut</label>
        <select name="statut" class="form-control @error('statut') is-invalid @enderror" required>
            @foreach(['Actif','En maintenance','Retirée'] as $s)
                <option value="{{ $s }}" @selected(old('statut', $application->statut ?? '') === $s)>{{ $s }}</option>
            @endforeach
        </select>
        @error('statut')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>

<div class="form-row">
    <div class="form-group col-md-6">
        <label>Service</label>
        <select name="service_id" class="form-control @error('service_id') is-invalid @enderror">
            <option value="">— Aucun —</option>
            @foreach($services as $s)
                <option value="{{ $s->id }}" @selected((int)old('service_id', $application->service_id ?? 0) === $s->id)>
                    {{ $s->nom }}
                </option>
            @endforeach
        </select>
        @error('service_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="form-group col-md-6">
        <label>Logo (png/jpg/webp, max 2 Mo)</label>
        <input type="file" name="logo" class="form-control-file @error('logo') is-invalid @enderror" accept="image/*">
        @error('logo')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        @isset($application)
            @if($application->logo_url)
                <div class="mt-2">
                    <img src="{{ $application->thumb_url }}" alt="" class="rounded" style="width:48px;height:48px;object-fit:cover">
                    <small class="text-muted ml-2">Logo actuel</small>
                </div>
            @endif
        @endisset
    </div>
</div>

<div class="form-group">
    <label>Description</label>
    <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $application->description ?? '') }}</textarea>
    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
</div>

<div class="text-right">
    <a href="{{ url()->previous() }}" class="btn btn-light mr-2">
        <i class="fas fa-arrow-left mr-1"></i> Annuler
    </a>
    <button class="btn btn-primary">
        <i class="fas fa-save mr-1"></i> Enregistrer
    </button>
</div>
