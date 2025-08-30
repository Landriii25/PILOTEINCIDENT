{{-- resources/views/kb/categories/_form.blade.php --}}
@csrf

<div class="form-group">
    <label>Nom <span class="text-danger">*</span></label>
    <input type="text" name="nom" class="form-control" required
           value="{{ old('nom', $category->nom ?? '') }}">
</div>

<div class="form-group">
    <label>Description</label>
    <textarea name="description" rows="3" class="form-control">{{ old('description', $category->description ?? '') }}</textarea>
</div>

<div class="form-group">
    <label>Position (ordre d’affichage)</label>
    <input type="number" name="position" class="form-control"
           value="{{ old('position', $category->position ?? 0) }}">
</div>

<div class="d-flex justify-content-between">
    <a href="{{ route('kb.categories') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left mr-1"></i> Annuler
    </a>
    <button class="btn btn-success">
        <i class="fas fa-check mr-1"></i> Enregistrer
    </button>
</div>
