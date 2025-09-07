@extends('adminlte::page')

@section('title','Modifier utilisateur')

@section('content_header')
    <h1>Modifier : {{ $user->name }}</h1>
@stop

@section('content')

{{-- AJOUT 1 : Affichage du résumé des erreurs de validation --}}
@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Merci de corriger les erreurs suivantes :</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card">
    <form action="{{ route('users.update', $user) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="card-body">

            {{-- Nom --}}
            <div class="form-group">
                <label>Nom <span class="text-danger">*</span></label>
                {{-- AJOUT 2 : Ajout des classes et du bloc @error --}}
                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                       required value="{{ old('name', $user->name) }}">
                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Email --}}
            <div class="form-group">
                <label>Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                       required value="{{ old('email', $user->email) }}">
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Titre --}}
            <div class="form-group">
                <label>Titre / Fonction</label>
                <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                       value="{{ old('title', $user->title) }}">
                @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Service --}}
            <div class="form-group">
                <label>Service</label>
                <select name="service_id" class="form-control @error('service_id') is-invalid @enderror">
                    <option value="">— Aucun —</option>
                    @foreach($services as $s)
                        {{-- La logique ici était déjà correcte --}}
                        <option value="{{ $s->id }}" @selected(old('service_id', $user->service_id) == $s->id)>{{ $s->nom }}</option>
                    @endforeach
                </select>
                @error('service_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Rôle --}}
            <div class="form-group">
                <label>Rôle <span class="text-danger">*</span></label>
                {{-- CORRECTION 1 : Le name doit être "role_id" et non "role" --}}
                <select name="role_id" class="form-control @error('role_id') is-invalid @enderror" required>
                    @foreach($roles as $r)
                        {{-- CORRECTION 2 : La value doit être l'ID du rôle, pas son nom --}}
                        {{-- CORRECTION 3 : La comparaison pour @selected doit se faire sur les ID --}}
                        <option value="{{ $r->id }}" @selected(old('role_id', $user->roles->first()->id ?? null) == $r->id)>
                            {{ $r->name }}
                        </option>
                    @endforeach
                </select>
                @error('role_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            {{-- Mot de passe --}}
            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Nouveau mot de passe (optionnel)</label>
                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror">
                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="form-group col-md-6">
                    <label>Confirmer le mot de passe</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>
            </div>

        </div>
        <div class="card-footer d-flex justify-content-end">
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary mr-2">Annuler</a>
            {{-- SIMPLIFICATION : Utilisation d'un bouton standard, plus simple et plus fiable --}}
            <button type="submit" class="btn btn-primary">Mettre à jour</button>
        </div>
    </form>
</div>
@stop
