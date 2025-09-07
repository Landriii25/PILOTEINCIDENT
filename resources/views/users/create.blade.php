@extends('adminlte::page')

@section('title','Nouvel utilisateur')

@section('content_header')
    <h1 class="m-0">Créer un utilisateur</h1>
@stop

@section('content')
    {{-- Messages flash succès/erreur --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- Erreurs de validation --}}
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
        <form method="POST" action="{{ route('users.store') }}" autocomplete="off">
            @csrf
            <div class="card-body">

                <div class="form-group">
                    <label>Nom <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name') }}" required>
                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label>Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                           value="{{ old('email') }}" required>
                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="form-group">
                    <label>Fonction / Titre</label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                           value="{{ old('title') }}">
                    @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Service (optionnel) --}}
                <div class="form-group">
                    <label>Service</label>
                    <select name="service_id" class="form-control @error('service_id') is-invalid @enderror">
                        <option value="">— Aucun —</option>
                        @foreach($services as $s)
                            <option value="{{ $s->id }}" @selected(old('service_id') == $s->id)>{{ $s->nom }}</option>
                        @endforeach
                    </select>
                    @error('service_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

              {{-- Rôle --}}
                <div class="form-group">
                    <label>Rôle <span class="text-danger">*</span></label>
                    <select name="role_id" required class="form-control @error('role_id') is-invalid @enderror">

                        {{-- L'option par défaut est maintenant non sélectionnable --}}
                        <option value="" disabled selected>— Sélectionner un rôle —</option>

                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('role_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                {{-- Mot de passe --}}
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Mot de passe <span class="text-danger">*</span></label>
                        <input type="password" name="password"
                               class="form-control @error('password') is-invalid @enderror" required>
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="form-group col-md-6">
                        <label>Confirmer le mot de passe <span class="text-danger">*</span></label>
                        <input type="password" name="password_confirmation" class="form-control" required>
                    </div>
                </div>

            </div>
            <div class="card-footer d-flex justify-content-end">
                <a href="{{ route('users.index') }}" class="btn btn-outline-secondary mr-2">
                    <i class="far fa-times-circle mr-1"></i> Annuler
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save mr-1"></i> Enregistrer
                </button>
            </div>
        </form>
    </div>
@stop
