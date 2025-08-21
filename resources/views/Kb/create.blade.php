@extends('adminlte::page')

@section('title', 'Nouvel article — Base de connaissances')

@section('content_header')
    <h1>Nouvel article</h1>
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

        <form method="POST" action="{{ route('kb.store') }}">
            @csrf

            <div class="form-group">
                <label>Titre *</label>
                <input type="text" name="title" class="form-control" required
                       value="{{ old('title') }}">
            </div>

            <div class="form-group">
                <label>Slug (optionnel)</label>
                <input type="text" name="slug" class="form-control"
                       value="{{ old('slug') }}">
                <small class="text-muted">
                    Laisser vide pour générer automatiquement à partir du titre.
                </small>
            </div>

            <div class="form-group">
                <label>Catégorie *</label>
                <select name="kb_category_id" class="form-control" required>
                    <option value="">— Sélectionner —</option>
                    @foreach($categories as $c)
                        <option value="{{ $c->id }}" @selected(old('kb_category_id') == $c->id)>
                            {{ $c->nom }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label>Résumé (optionnel)</label>
                <textarea name="summary" class="form-control" rows="3">{{ old('summary') }}</textarea>
            </div>

            <div class="form-group">
                <label>Contenu *</label>
                <textarea name="content" class="form-control" rows="10" required>{{ old('content') }}</textarea>
                <small class="text-muted">Tu peux coller du HTML, ou du texte simple.</small>
            </div>

            <div class="form-group">
                <label>Tags (séparés par des virgules)</label>
                <input type="text" name="tags_csv" class="form-control"
                       value="{{ old('tags_csv') }}"
                       placeholder="ex: supervision, réseau, priorité">
                <small class="text-muted">
                    Le contrôleur transformera cette liste en JSON (tableau).
                </small>
            </div>

            <div class="form-group form-check">
                <input type="checkbox" name="is_published" id="is_published" class="form-check-input"
                       value="1" {{ old('is_published', true) ? 'checked' : '' }}>
                <label for="is_published" class="form-check-label">Publié</label>
            </div>

            <button class="btn btn-primary">
                <i class="fas fa-save"></i> Enregistrer
            </button>
            <a href="{{ route('kb.index') }}" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
</div>
@stop

