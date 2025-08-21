@extends('adminlte::page')

@section('title', 'Modifier catégorie — KB')

@section('content_header')
    <h1>Modifier la catégorie</h1>
@stop

@section('content')
<div class="card">
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">
                @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
            </ul></div>
        @endif

        <form method="POST" action="{{ route('kb.categories.update', $category) }}">
            @csrf @method('PUT')
            <div class="form-group">
                <label>Nom</label>
                <input type="text" name="nom" class="form-control" required value="{{ old('nom',$category->nom) }}">
            </div>
            <div class="form-group">
                <label>Slug (optionnel)</label>
                <input type="text" name="slug" class="form-control" value="{{ old('slug',$category->slug) }}">
            </div>
            <div class="form-group">
                <label>Description (optionnel)</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description',$category->description) }}</textarea>
            </div>
            <div class="form-group">
                <label>Position (ordre d’affichage)</label>
                <input type="number" name="position" min="0" class="form-control" value="{{ old('position',$category->position ?? 0) }}">
            </div>

            <button class="btn btn-primary"><i class="fas fa-save"></i> Mettre à jour</button>
            <a href="{{ route('kb.categories') }}" class="btn btn-secondary">Annuler</a>
        </form>

        <div class="mt-3">
            <form method="POST" action="{{ route('kb.categories.destroy', $category) }}"
                  onsubmit="return confirm('Supprimer cette catégorie ?');">
                @csrf @method('DELETE')
                <button class="btn btn-danger"><i class="fas fa-trash"></i> Supprimer</button>
            </form>
        </div>
    </div>
</div>
@stop
