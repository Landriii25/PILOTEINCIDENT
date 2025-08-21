@extends('adminlte::page')

@section('title', 'Incidents')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
        <h1 class="m-0">Incidents</h1>
        @can('create incidents')
            <a href="{{ route('incidents.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle mr-1"></i> Créer un incident
            </a>
        @endcan
    </div>
@endsection

@section('content')

    {{-- Barre de filtres --}}
    <div class="card mb-3">
        <div class="card-body pb-2">
            <form method="GET" action="{{ route('incidents.index') }}" class="form-row align-items-end">
                {{-- Recherche --}}
                <div class="form-group col-md-3">
                    <label class="mb-1">Recherche</label>
                    <input type="text" name="q" class="form-control" placeholder="Code, titre, description…"
                           value="{{ request('q') }}">
                </div>

                {{-- Priorité --}}
                <div class="form-group col-md-2">
                    <label class="mb-1">Priorité</label>
                    <select name="priorite" class="form-control">
                        <option value="">— Toutes —</option>
                        @foreach($priorites as $p)
                            <option value="{{ $p }}" @selected(request('priorite')===$p)>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Statut --}}
                <div class="form-group col-md-2">
                    <label class="mb-1">Statut</label>
                    <select name="statut" class="form-control">
                        <option value="">— Tous —</option>
                        @foreach($statuts as $s)
                            <option value="{{ $s }}" @selected(request('statut')===$s)>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Application --}}
                <div class="form-group col-md-3">
                    <label class="mb-1">Application</label>
                    <select name="application_id" class="form-control">
                        <option value="">— Toutes —</option>
                        @foreach($applications as $app)
                            <option value="{{ $app->id }}" @selected(request('application_id')==$app->id)>{{ $app->nom }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Technicien --}}
                <div class="form-group col-md-2">
                    <label class="mb-1">Technicien</label>
                    <select name="technicien_id" class="form-control">
                        <option value="">— Tous —</option>
                        @foreach($techniciens as $t)
                            <option value="{{ $t->id }}" @selected(request('technicien_id')==$t->id)>{{ $t->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Switchs rapides --}}
                <div class="form-group col-md-3 mt-2">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="open" name="open" value="1" {{ request('open')?'checked':'' }}>
                        <label class="custom-control-label" for="open">Uniquement ouverts</label>
                    </div>
                    <div class="custom-control custom-checkbox mt-1">
                        <input type="checkbox" class="custom-control-input" id="mine" name="mine" value="1" {{ request('mine')?'checked':'' }}>
                        <label class="custom-control-label" for="mine">Mes demandes</label>
                    </div>
                </div>

                {{-- Boutons --}}
                <div class="form-group col-md-3 ml-auto d-flex gap-2 justify-content-end">
                    <a href="{{ route('incidents.index') }}" class="btn btn-light border mr-2">
                        <i class="fas fa-undo mr-1"></i> Réinitialiser
                    </a>
                    <button class="btn btn-primary">
                        <i class="fas fa-filter mr-1"></i> Filtrer
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Tableau réutilisable --}}
    <div class="card">
        <div class="card-body p-0">
            <x-incidents.table :incidents="$incidents"
                               :compact="false"
                               :showAssignee="true"
                               :showCreatedAt="true"
                               :showStatus="true"
                               :showActions="true" />
        </div>
    </div>
@endsection
