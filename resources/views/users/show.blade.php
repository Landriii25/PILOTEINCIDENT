@extends('adminlte::page')

@section('title', 'Détails de l\'utilisateur')

@section('content_header')
    <h1 class="m-0">Détails de : {{ $user->name }}</h1>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <p><strong>Nom :</strong> {{ $user->name }}</p>
                    <p><strong>Email :</strong> {{ $user->email }}</p>
                    <p><strong>Titre / Fonction :</strong> {{ $user->title ?? 'Non défini' }}</p>

                    {{-- On utilise l'opérateur nullsafe (?->) au cas où l'utilisateur n'a pas de service --}}
                    <p><strong>Service :</strong> {{ $user->service?->nom ?? 'Aucun' }}</p>

                    {{-- On affiche le premier rôle de l'utilisateur --}}
                    <p><strong>Rôle :</strong> {{ $user->roles->first()->name ?? 'Aucun' }}</p>

                    <p><strong>Créé le :</strong> {{ $user->created_at->format('d/m/Y H:i') }}</p>
                </div>

                <div class="card-footer d-flex justify-content-end">
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary mr-2">
                        <i class="fas fa-list mr-1"></i> Retour à la liste
                    </a>
                    <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                        <i class="fas fa-pencil-alt mr-1"></i> Modifier
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop
