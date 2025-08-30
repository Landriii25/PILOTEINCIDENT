@extends('adminlte::page')

@section('title', 'Mes incidents')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between">
        <h1 class="mb-0">Mes incidents</h1>

        @can('incidents.create')
            <a href="{{ route('incidents.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i> Créer un incident
            </a>
        @endcan
    </div>
@endsection

@section('content')
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 incidents-list">
                <thead class="thead-light">
                <tr>
                    <th style="width:140px">Code</th>
                    <th>Titre</th>
                    <th>Application</th>
                    <th style="width:120px">Priorité</th>
                    <th style="width:120px">Statut</th>
                    <th>Assigné à</th>
                    <th style="width:160px">Échéance (SLA)</th>
                    <th style="width:80px" class="text-right">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($incidents as $incident)
                    @php
                        $prioColor = [
                            'Critique' => 'danger',
                            'Haute'    => 'warning',
                            'Moyenne'  => 'info',
                            'Basse'    => 'secondary',
                        ][$incident->priorite] ?? 'secondary';

                        $statutColor = match($incident->statut){
                            'Ouvert'   => 'primary',
                            'En cours' => 'info',
                            'Résolu'   => 'success',
                            'Fermé'    => 'secondary',
                            default    => 'secondary',
                        };

                        $late = $incident->due_at && now()->gt($incident->due_at) && is_null($incident->resolved_at);
                    @endphp
                    <tr>
                        <td class="align-middle">
                            <a href="{{ route('incidents.show', $incident) }}" class="font-weight-bold">
                                {{ $incident->code }}
                            </a>
                        </td>
                        <td class="align-middle">
                            <div class="font-weight-600">{{ $incident->titre }}</div>
                            <div class="text-muted small">
                                {{ Str::limit($incident->description, 80) }}
                            </div>
                        </td>
                        <td class="align-middle">
                            {{ $incident->application->nom ?? '—' }}
                        </td>
                        <td class="align-middle">
                            <span class="badge badge-{{ $prioColor }}">{{ $incident->priorite }}</span>
                        </td>
                        <td class="align-middle">
                            <span class="badge badge-{{ $statutColor }}">{{ $incident->statut }}</span>
                        </td>
                        <td class="align-middle">
                            {{ $incident->technicien->name ?? '—' }}
                        </td>
                        <td class="align-middle">
                            @if($incident->due_at)
                                <span class="badge badge-{{ $late ? 'danger' : 'secondary' }}">
                                    {{ $incident->due_at->diffForHumans() }}
                                </span>
                            @else
                                —
                            @endif
                        </td>

                        {{-- Actions via dropdown --}}
                        <td class="align-middle text-right">
                            <div class="btn-group">
                                <button type="button"
                                        class="btn btn-light btn-sm dropdown-toggle"
                                        data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                        title="Actions">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">

                                    {{-- Voir --}}
                                    <a class="dropdown-item" href="{{ route('incidents.show', $incident) }}">
                                        <i class="far fa-eye mr-2 text-muted"></i> Voir
                                    </a>

                                    {{-- Éditer (si autorisé) --}}
                                    @can('update', $incident)
                                        <a class="dropdown-item" href="{{ route('incidents.edit', $incident) }}">
                                            <i class="far fa-edit mr-2 text-muted"></i> Éditer
                                        </a>
                                    @endcan

                                    {{-- Clore (si Résolu + créateur ou admin) --}}
                                    @if($incident->statut === 'Résolu' && (auth()->id()===$incident->user_id || auth()->user()->hasRole('admin')))
                                        <div class="dropdown-divider"></div>
                                        <form action="{{ route('incidents.close', $incident) }}" method="POST"
                                              onsubmit="return confirm('Clore cet incident ?');">
                                            @csrf @method('PUT')
                                            <button class="dropdown-item" type="submit">
                                                <i class="fas fa-check mr-2 text-success"></i> Clore
                                            </button>
                                        </form>

                                        {{-- Ré-ouvrir vers le même technicien --}}
                                        <form action="{{ route('incidents.reopen_to_tech', $incident) }}" method="POST"
                                              onsubmit="return confirm('Ré‑ouvrir et réassigner au même technicien ?');">
                                            @csrf @method('PUT')
                                            <button class="dropdown-item" type="submit">
                                                <i class="fas fa-redo mr-2 text-warning"></i> Ré‑ouvrir
                                            </button>
                                        </form>
                                    @endif

                                    {{-- Rapport d’intervention (si existant / sinon créer) --}}
                                    @if($incident->report)
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item" href="{{ route('reports.show', $incident->report) }}">
                                            <i class="fas fa-file-alt mr-2 text-primary"></i> Voir le rapport
                                        </a>
                                        <a class="dropdown-item" href="{{ route('reports.edit', $incident->report) }}">
                                            <i class="far fa-edit mr-2 text-primary"></i> Éditer le rapport
                                        </a>
                                    @else
                                        @can('update', $incident)
                                            <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="{{ route('reports.create_for_incident', $incident) }}">
                                                <i class="fas fa-file-signature mr-2 text-primary"></i> Rédiger le rapport
                                            </a>
                                        @endcan
                                    @endif

                                    {{-- Supprimer (si autorisé) --}}
                                    @can('delete', $incident)
                                        <div class="dropdown-divider"></div>
                                        <form action="{{ route('incidents.destroy', $incident) }}" method="POST"
                                              onsubmit="return confirm('Supprimer cet incident ?');">
                                            @csrf @method('DELETE')
                                            <button class="dropdown-item text-danger" type="submit">
                                                <i class="far fa-trash-alt mr-2"></i> Supprimer
                                            </button>
                                        </form>
                                    @endcan
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-5">
                            Aucun incident pour l’instant.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if(method_exists($incidents, 'links'))
        <div class="card-footer">
            {{ $incidents->links('pagination::bootstrap-4') }}
        </div>
    @endif
</div>
@endsection

@push('css')
<style>
/* Empêche le wrap de la 1ère colonne (code) + scroll horizontal si besoin */
table.incidents-list td:first-child,
table.incidents-list th:first-child { white-space: nowrap; }
.table-responsive{ overflow-x:auto; }
</style>
@endpush
