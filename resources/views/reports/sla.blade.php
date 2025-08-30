@extends('adminlte::page')

@section('title','Incidents SLA')

@section('content_header')
    <h1 class="mb-0">Incidents SLA à risque</h1>
@endsection

@section('content')
<div class="row mb-3">
    <div class="col-md-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $overdueCount }}</h3>
                <p>En retard</p>
            </div>
            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $soonCount }}</h3>
                <p>Échéance &lt; 4h</p>
            </div>
            <div class="icon"><i class="fas fa-hourglass-half"></i></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">À risque (paginé)</h3>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="thead-light">
                    <tr>
                        <th class="text-nowrap" style="width:140px">Code</th>
                        <th>Application</th>
                        <th>Technicien</th>
                        <th style="width:120px">Priorité</th>
                        <th style="width:120px">Statut</th>
                        <th style="width:140px">Échéance</th>
                        <th class="text-right" style="width:100px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($list as $incident)
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
                            <td class="text-monospace font-weight-bold text-nowrap">
                                {{ $incident->code }}
                            </td>
                            <td>{{ optional($incident->application)->nom ?? '—' }}</td>
                            <td>{{ optional($incident->technicien)->name ?? 'Non assigné' }}</td>
                            <td><span class="badge badge-{{ $prioColor }}">{{ $incident->priorite }}</span></td>
                            <td><span class="badge badge-{{ $statutColor }}">{{ $incident->statut }}</span></td>
                            <td>
                                @if($incident->due_at)
                                    <span class="badge badge-{{ $late ? 'danger' : 'secondary' }}">
                                        {{ $incident->due_at->diffForHumans() }}
                                    </span>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-right pr-2">
                                <a href="{{ route('incidents.show',$incident) }}"
                                   class="btn btn-sm btn-outline-primary" title="Voir">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @can('update',$incident)
                                    <a href="{{ route('incidents.edit',$incident) }}"
                                       class="btn btn-sm btn-outline-warning" title="Réassigner / Éditer">
                                        <i class="fas fa-user-edit"></i>
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Aucun incident SLA à risque.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if(method_exists($list,'links'))
        <div class="card-footer">
            {{ $list->links('pagination::bootstrap-4') }}
        </div>
    @endif
</div>
@endsection

@push('css')
<style>
  .text-nowrap {
      white-space: nowrap !important;
  }
</style>
@endpush
