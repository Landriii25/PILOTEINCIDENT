@extends('adminlte::page')
@section('title','Rapport '.$report->ref)

@section('content_header')
    <div class="d-flex align-items-center justify-content-between">
        <h1 class="m-0">Rapport d’incident — {{ $report->ref }}</h1>
        <div>
            <a href="{{ route('incidents.show',$report->incident) }}" class="btn btn-light mr-2">
                <i class="fas fa-ticket-alt mr-1"></i> Incident
            </a>
            <a href="{{ route('reports.edit',$report) }}" class="btn btn-primary">
                <i class="fas fa-edit mr-1"></i> Éditer
            </a>
        </div>
    </div>
@endsection

@section('content')
<div class="card">
    <div class="card-body">
        <div class="mb-3">
            <span class="badge badge-primary">DI : {{ $report->ref }}</span>
            <span class="badge badge-info">Durée :
                @if($report->duration_minutes !== null)
                    {{ floor($report->duration_minutes/60) }}h {{ $report->duration_minutes % 60 }}m
                @else
                    —
                @endif
            </span>
        </div>

        <div class="row">
            <div class="col-md-6">
                <h5>Description</h5>
                <p>{{ $report->description }}</p>
            </div>
            <div class="col-md-6">
                <h5>Constats</h5>
                <p>{{ $report->constats }}</p>
            </div>
            <div class="col-md-6">
                <h5>Causes</h5>
                <p>{{ $report->causes }}</p>
            </div>
            <div class="col-md-6">
                <h5>Plan d’actions & remédiation</h5>
                <p>{{ $report->actions }}</p>
            </div>
            <div class="col-md-6">
                <h5>Impacts</h5>
                <p>{{ $report->impacts }}</p>
            </div>
            <div class="col-md-6">
                <h5>Recommandation</h5>
                <p>{{ $report->recommendation ?: '—' }}</p>
            </div>
        </div>

        <hr>
        <div class="row text-muted">
            <div class="col-md-4"><strong>Début :</strong> {{ optional($report->started_at)->format('d/m/Y H:i') }}</div>
            <div class="col-md-4"><strong>Fin :</strong>   {{ optional($report->ended_at)->format('d/m/Y H:i') }}</div>
            <div class="col-md-4"><strong>Auteur :</strong> {{ optional($report->author)->name ?? '—' }}</div>
        </div>
    </div>
</div>
@endsection
