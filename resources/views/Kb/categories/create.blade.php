@extends('adminlte::page')

@section('title', 'Nouvelle catégorie — KB')

@section('content_header')
    <h1>Nouvelle catégorie (type d’incident)</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">
                @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul></div>
        @endif

        <form method="POST" action="{{ route('kb.categories.store') }}">
            @csrf
            <div class="form-group">
                <label>Nom</label>
                <input type="text" name="nom" class="form-control" required value="{{ old('nom',$category->nom) }}">
            </div>
            <div class="form-group">
                <label>Slug (optionnel)</label>
                <input type="text" name="slug" class="form-control" value="{{ old('slug',$category->slug) }}">
                <small class="text-muted">Ex. “materiel”, “reseau”. Laisser vide pour générer automatiquement.</small>
            </div>
            <div class="form-group">
                <label>Description (optionnel)</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description',$category->description) }}</textarea>
            </div>
            <div class="form-group">
                <label>Position (ordre d’affichage)</label>
                <input type="number" name="position" min="0" class="form-control" value="{{ old('position',$category->position ?? 0) }}">
            </div>

            <button class="btn btn-primary"><i class="fas fa-save"></i> Enregistrer</button>
            <a href="{{ route('kb.categories') }}" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
</div>
@stop
