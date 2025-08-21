@extends('adminlte::page')

@section('title','SLA à risque')

@section('content_header')
    <h1 class="m-0">Incidents — SLA à risque</h1>
@endsection

@section('content')
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle mr-2"></i>
        Liste des incidents en retard ou dont l’échéance est imminente.
    </div>

    <div class="card">
        <div class="card-body p-0">
            {{-- compact = true, on garde Assigné + Statut, on peut masquer CreatedAt --}}
            <x-incidents.table :incidents="$incidents"
                               :compact="true"
                               :showAssignee="true"
                               :showCreatedAt="false"
                               :showStatus="true"
                               :showActions="true" />
        </div>
    </div>
@endsection
