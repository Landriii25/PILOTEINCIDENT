@extends('adminlte::page')

@section('title','Modifier utilisateur')

@section('content_header')
    <h1>Modifier : {{ $user->name }}</h1>
@stop

@section('content')
<x-can perm="users.update">
<div class="card">
    <form action="{{ route('users.update',$user) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card-body">
            <div class="form-group"><label>Nom</label>
                <input type="text" name="name" class="form-control" required value="{{ old('name',$user->name) }}">
            </div>
            <div class="form-group"><label>Email</label>
                <input type="email" name="email" class="form-control" required value="{{ old('email',$user->email) }}">
            </div>
            <div class="form-group"><label>Nouveau mot de passe (optionnel)</label>
                <input type="password" name="password" class="form-control">
            </div>
            <div class="form-group"><label>Confirmer</label>
                <input type="password" name="password_confirmation" class="form-control">
            </div>
            <div class="form-group"><label>Titre / Fonction</label>
                <input type="text" name="title" class="form-control" value="{{ old('title',$user->title) }}">
            </div>
            <div class="form-group">
                <label>Service</label>
                <select name="service_id" class="form-control">
                    <option value="">— Aucun —</option>
                    @foreach($services as $s)
                        <option value="{{ $s->id }}" @selected(old('service_id',$user->service_id)==$s->id)>{{ $s->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label>Rôle</label>
                <select name="role" class="form-control" required>
                    @foreach($roles as $r)
                        <option value="{{ $r->name }}" @selected(old('role',$user->getRoleNames()->first())==$r->name)>{{ ucfirst($r->name) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="card-footer">
            <x-action.post :action="route('users.update',$user)" method="PUT" text="Mettre à jour" class="btn-success" icon="fas fa-save" />
            <a href="{{ route('users.index') }}" class="btn btn-default">Annuler</a>
        </div>
    </form>
</div>
</x-can>
@cannot('users.update')
    <div class="alert alert-warning">Action non autorisée.</div>
@endcannot
@stop
@section('css')
    <style>
        .form-control {
            width: 100%;
        }
    </style>
@stop
