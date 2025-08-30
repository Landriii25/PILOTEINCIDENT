{{-- resources/views/kb/categories/edit.blade.php --}}
@extends('adminlte::page')

@section('title', 'Éditer catégorie — KB')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">Éditer : {{ $category->nom }}</h1>

        <div class="btn-group">
            <a href="{{ route('kb.categories.show', $category) }}" class="btn btn-info">
                <i class="far fa-eye mr-1"></i> Voir
            </a>
            <a href="{{ route('kb.categories') }}" class="btn btn-secondary">
                <i class="fas fa-list mr-1"></i> Liste
            </a>
        </div>
    </div>
@endsection

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Erreurs :</strong>
            <ul class="mb-0">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
        </div>
    @endif

    <div class="card">
        <form method="POST" action="{{ route('kb.categories.update', $category) }}">
            @method('PUT')
            <div class="card-body">
                @include('kb.categories._form', ['category' => $category])
            </div>
        </form>
    </div>
@endsection
