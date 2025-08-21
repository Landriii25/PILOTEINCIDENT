@csrf
<div class="form-group">
    <label>Catégorie</label>
    <select name="kb_category_id" class="form-control">
        <option value="">— Aucune —</option>
        @foreach($categories as $c)
            <option value="{{ $c->id }}" @selected(old('kb_category_id', $article->kb_category_id ?? null) == $c->id)>
                {{ $c->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="form-group">
    <label>Titre</label>
    <input type="text" name="title" class="form-control" required
           value="{{ old('title', $article->title ?? '') }}">
</div>

<div class="form-group">
    <label>Résumé</label>
    <textarea name="summary" class="form-control" rows="3">{{ old('summary', $article->summary ?? '') }}</textarea>
</div>

<div class="form-group">
    <label>Contenu</label>
    <textarea name="content" class="form-control" rows="8">{{ old('content', $article->content ?? '') }}</textarea>
</div>

<div class="form-group">
    <label>Mots‑clés (séparés par des virgules)</label>
    @php
        $tagsCsv = old('tags', isset($article->tags) ? implode(', ', (array) $article->tags) : '');
    @endphp
    <input type="text" name="tags" class="form-control" value="{{ $tagsCsv }}">
</div>

<div class="form-group form-check">
    <input type="checkbox" name="is_published" class="form-check-input" id="is_published"
           value="1" @checked(old('is_published', $article->is_published ?? true))>
    <label class="form-check-label" for="is_published">Publié</label>
</div>

<div class="d-flex gap-2">
    <button class="btn btn-primary mr-2">
        <i class="fas fa-save"></i> Enregistrer
    </button>
    <a href="{{ route('kb.index') }}" class="btn btn-secondary">Annuler</a>
</div>
