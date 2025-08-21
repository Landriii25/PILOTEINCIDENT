@extends('adminlte::page')

@section('title','Créer un rôle')

@section('content_header')
    <h1>Créer un rôle</h1>
@stop

@section('content')
<x-can perm="roles.manage">
<div class="card">
    <form action="{{ route('roles.store') }}" method="POST">
        @csrf
        <div class="card-body">
            <div class="form-group">
                <label>Nom du rôle</label>
                <input type="text" name="name" class="form-control" required placeholder="ex: superviseur">
            </div>

            <div class="form-group">
                <label>Permissions</label>
                <div class="row">
                    @foreach($permissions as $perm)
                        <div class="col-md-4 mb-1">
                            <label class="mb-0">
                                <input type="checkbox" name="permissions[]" value="{{ $perm->name }}">
                                <span class="text-monospace">{{ $perm->name }}</span>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="card-footer">
            <x-action.post :action="route('roles.store')" text="Enregistrer" class="btn-success" icon="fas fa-check" />
            <a href="{{ route('roles.index') }}" class="btn btn-default">Annuler</a>
        </div>
    </form>
</div>
</x-can>
@cannot('roles.manage')
    <div class="alert alert-warning">Accès non autorisé.</div>
@endcannot
@stop
@section('css')
    <style>
        .form-control {
            width: 100%;
        }
        .form-group label {
            font-weight: bold;
        }
    </style>
@stop
