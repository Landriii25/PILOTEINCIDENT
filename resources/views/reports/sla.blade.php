@extends('adminlte::page')

@section('title','Rapport · SLA')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between">
        <h1 class="m-0">SLA</h1>
        <div class="d-flex gap-2">
            <span class="badge badge-danger p-2 mr-2">En retard: {{ $overdueCount }}</span>
            <span class="badge badge-warning p-2">Imminent (&lt;4h): {{ $soonCount }}</span>
        </div>
    </div>
@stop

@section('content')
<div class="card">
    <div class="card-header"><strong>Incidents à risque / en retard</strong></div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Titre</th>
                    <th>Application</th>
                    <th>Technicien</th>
                    <th>Priorité</th>
                    <th>Échéance</th>
                    <th class="text-right" style="width:220px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($list as $i)
                    <tr>
                        <td><span class="badge badge-dark">{{ $i->code }}</span></td>
                        <td>{{ $i->titre }}</td>
                        <td>{{ $i->application->nom ?? '—' }}</td>
                        <td>{{ $i->technicien->name ?? '—' }}</td>
                        <td>{{ $i->priorite ?? '—' }}</td>
                        <td>
                            @if($i->due_at)
                                <span class="badge badge-{{ $i->is_late ? 'danger':'warning' }}">
                                    {{ $i->due_at->diffForHumans() }}
                                </span>
                            @else
                                —
                            @endif
                        </td>
                        <td class="text-right">
                            <x-action.link href="{{ route('incidents.show',$i) }}" class="btn-info btn-sm" icon="far fa-eye">Voir</x-action.link>
                            @can('assign', $i)
                                <x-action.link href="{{ route('incidents.edit',$i) }}" class="btn-warning btn-sm" icon="fas fa-user-check">Réassigner</x-action.link>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-muted p-3">RAS.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@stop
@section('css')

