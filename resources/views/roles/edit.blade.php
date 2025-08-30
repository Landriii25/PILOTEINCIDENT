@extends('adminlte::page')

@section('title','Éditer rôle')

@section('content_header')
  <div class="d-flex justify-content-between align-items-center">
    <h1 class="mb-0">Éditer le rôle : {{ $role->name }}</h1>
    <a href="{{ route('roles.index') }}" class="btn btn-secondary">
      <i class="fas fa-arrow-left mr-1"></i> Retour
    </a>
  </div>
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
      @include('roles._form', [
        'role'=>$role,
        'permissions'=>$permissions,
        'rolePermissions'=>$rolePermissions ?? []
      ])
    </div>
  </div>
@endsection
