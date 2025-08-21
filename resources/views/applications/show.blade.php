{{-- resources/views/applications/show.blade.php --}}
@extends('adminlte::page')

@section('title', $application->nom)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">{{ $application->nom }}</h1>
        <div>
            <a href="{{ route('applications.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Retour à la liste
            </a>
            @can('applications.update')
                <a href="{{ route('applications.edit', $application) }}" class="btn btn-warning">
                    <i class="far fa-edit mr-1"></i> Éditer
                </a>
            @endcan
            @can('applications.delete')
                <x-action.delete :action="route('applications.destroy', $application)" />
            @endcan
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body text-center">
                    @if($application->logo_url)
                        <img src="{{ asset('storage/'.$application->logo_url) }}" class="rounded shadow-sm mb-3" style="width:160px;height:160px;object-fit:cover" alt="logo">
                    @else
                        <div class="rounded bg-light d-inline-flex align-items-center justify-content-center" style="width:160px;height:160px;">
                            <i class="far fa-image fa-2x text-muted"></i>
                        </div>
                    @endif

                    <div class="mt-2">
                        @php
                            $color = match($application->statut){
                                'Actif' => 'success',
                                'En maintenance' => 'warning',
                                'Retirée' => 'secondary',
                                default => 'light'
                            };
                        @endphp
                        <span class="badge badge-{{ $color }}">{{ $application->statut }}</span>
                    </div>
                </div>
                <div class="card-footer text-muted small">
                    <div><strong>Service :</strong> {{ optional($application->service)->nom ?? '—' }}</div>
                    <div><strong>Créée le :</strong> {{ optional($application->created_at)->format('d/m/Y H:i') }}</div>
                    <div><strong>Maj le :</strong> {{ optional($application->updated_at)->format('d/m/Y H:i') }}</div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header"><strong>Description</strong></div>
                <div class="card-body">
                    <p class="mb-0">{{ $application->description ?: '—' }}</p>
                </div>
            </div>
        </div>
    </div>
@endsection
