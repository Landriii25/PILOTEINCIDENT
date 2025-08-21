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
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                <tr>
                    <th style="width:140px">Code</th>
                    <th>Titre</th>
                    <th>Application</th>
                    <th style="width:120px">Priorité</th>
                    <th style="width:120px">Statut</th>
                    <th>Assigné à</th>
                    <th style="width:140px">Échéance (SLA)</th>
                    <th style="width:120px" class="text-right">Actions</th>
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
                            <span class="font-weight-bold">{{ $incident->code }}</span>
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
                        <td class="align-middle text-right">
                            {{-- Voir --}}
                            <a class="btn btn-sm btn-outline-secondary" title="Voir"
                               href="{{ route('incidents.show', $incident) }}">
                                <i class="far fa-eye"></i>
                            </a>

                            {{-- Clore (visible si Résolu + créateur ou admin) --}}
                            @if($incident->statut === 'Résolu' && (auth()->id()===$incident->user_id || auth()->user()->hasRole('admin')))
                                <form action="{{ route('incidents.close', $incident) }}" method="POST"
                                      class="d-inline">
                                    @csrf @method('PUT')
                                    <button class="btn btn-sm btn-success" title="Clore">
                                        <i class="fas fa-check"></i>
                                    </button>
                                </form>

                                {{-- Ré-ouvrir vers le même technicien --}}
                                <form action="{{ route('incidents.reopen_to_tech', $incident) }}" method="POST"
                                      class="d-inline">
                                    @csrf @method('PUT')
                                    <button class="btn btn-sm btn-warning" title="Ré-ouvrir">
                                        <i class="fas fa-redo"></i>
                                    </button>
                                </form>
                            @endif
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
            {{-- Pagination Bootstrap 4 (globale si tu as déjà mis Paginator::useBootstrap()) --}}
            {{ $incidents->links('pagination::bootstrap-4') }}
        </div>
    @endif
</div>
@endsection
