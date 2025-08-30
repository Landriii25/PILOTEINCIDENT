@extends('adminlte::page')

@section('title','Nouveau rôle')

@section('content_header')
  <h1>Créer un rôle</h1>
@endsection

@section('content')
  @if($errors->any())
    <div class="alert alert-danger">
      <strong>Erreurs :</strong>
      <ul class="mb-0">
        @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  <div class="card">
    <div class="card-body">
      @include('roles._form', ['permissions'=>$permissions])
    </div>
  </div>
@endsection
