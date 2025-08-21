@extends('adminlte::page')

@section('title','Modifier rôle')

@section('content_header')
    <h1>Modifier : {{ $role->name }}</h1>
@stop

@section('content')
<x-can perm="roles.manage">
<div class="card">
    <form action="{{ route('roles.update',$role) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card-body">
            <div class="form-group">
                <label>Nom du rôle</label>
                <input type="text" name="name" class="form-control" required value="{{ old('name',$role->name) }}">
            </div>

            <div class="form-group">
                <label>Permissions</label>
                <div class="row">
                    @php $cur = $role->permissions->pluck('name')->toArray(); @endphp
                    @foreach($permissions as $perm)
                        <div class="col-md-4 mb-1">
                            <label class="mb-0">
                                <input type="checkbox" name="permissions[]" value="{{ $perm->name }}" @checked(in_array($perm->name,$cur))>
                                <span class="text-monospace">{{ $perm->name }}</span>
                            </label>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card-footer">
            <x-action.post :action="route('roles.update',$role)" method="PUT" text="Mettre à jour" class="btn-success" icon="fas fa-save" />
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
