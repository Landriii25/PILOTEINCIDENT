{{-- resources/views/kb/categories/create.blade.php --}}
@extends('adminlte::page')

@section('title', 'Nouvelle catégorie — KB')

@section('content_header')
    <h1>Nouvelle catégorie</h1>
@endsection

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Erreurs :</strong>
            <ul class="mb-0">@foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach</ul>
        </div>
    @endif

    <div class="card">
        <form method="POST" action="{{ route('kb.categories.store') }}">
            <div class="card-body">
                @include('kb.categories._form')
            </div>
        </form>
    </div>
@endsection
