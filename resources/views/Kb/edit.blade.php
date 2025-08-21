@extends('adminlte::page')

@section('title', 'Éditer article — Base de connaissances')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">Éditer : {{ $article->title }}</h1>
        <div>
            <a href="{{ route('kb.show', $article) }}" class="btn btn-info">
                <i class="fas fa-eye"></i> Voir
            </a>
            <a href="{{ route('kb.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Erreurs :</strong>
                <ul class="mb-0">
                    @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('kb.update', $article) }}">
            @csrf @method('PUT')

            <div class="form-group">
                <label>Titre *</label>
                <input type="text" name="title" class="form-control" required
                       value="{{ old('title', $article->title) }}">
            </div>

            <div class="form-group">
                <label>Slug (optionnel)</label>
                <input type="text" name="slug" class="form-control"
                       value="{{ old('slug', $article->slug) }}">
            </div>

            <div class="form-group">
                <label>Catégorie *</label>
                <select name="kb_category_id" class="form-control" required>
                    <option value="">— Sélectionner —</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}"
                            @selected(old('kb_category_id', $article->kb_category_id) == $c->id)>
                            {{ $c->nom }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Résumé (optionnel)</label>
                <textarea name="summary" class="form-control" rows="3">{{ old('summary', $article->summary) }}</textarea>
            </div>

            <div class="form-group">
                <label>Contenu *</label>
                <textarea name="content" class="form-control" rows="10" required>{{ old('content', $article->content) }}</textarea>
                <small class="text-muted">HTML ou texte simple.</small>
            </div>

            @php
                $tagsCurrent = is_array($article->tags) ? $article->tags : (json_decode($article->tags, true) ?: []);
            @endphp
            <div class="form-group">
                <label>Tags (séparés par des virgules)</label>
                <input type="text" name="tags_csv" class="form-control"
                       value="{{ old('tags_csv', implode(', ', $tagsCurrent)) }}">
            </div>

            <div class="form-group form-check">
                <input type="checkbox" name="is_published" id="is_published" class="form-check-input"
                       value="1" {{ old('is_published', $article->is_published) ? 'checked' : '' }}>
                <label for="is_published" class="form-check-label">Publié</label>
            </div>

            <button class="btn btn-primary">
                <i class="fas fa-save"></i> Mettre à jour
            </button>

            @can('kb.delete', $article)
                <form action="{{ route('kb.destroy', $article) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Supprimer cet article ?');">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger">
                        <i class="fas fa-trash"></i> Supprimer
                    </button>
                </form>
            @endcan
        </form>
    </div>
</div>
@stop
