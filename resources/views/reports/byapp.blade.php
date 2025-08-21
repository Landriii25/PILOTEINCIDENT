@extends('adminlte::page')

@section('title','Rapport · Incidents par application')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between">
        <h1 class="m-0">Incidents par application</h1>
        <x-can perm="reports.view">
            <x-action.link href="{{ route('reports.sla') }}" class="btn-secondary" icon="far fa-clock">
                Voir rapport SLA
            </x-action.link>
        </x-can>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-7">
        <div class="card h-100">
            <div class="card-header"><strong>Top applications (volume)</strong></div>
            <div class="card-body">
                <canvas id="byAppChart" height="180"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card h-100">
            <div class="card-header"><strong>Détail</strong></div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Application</th>
                            <th class="text-right">Total</th>
                            <th class="text-right">Ouverts</th>
                            <th class="text-right">Résolus</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $r)
                            <tr>
                                <td>{{ $r->app }}</td>
                                <td class="text-right">{{ $r->total }}</td>
                                <td class="text-right">{{ $r->ouverts }}</td>
                                <td class="text-right">{{ $r->resolus }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted p-3">Aucune donnée.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@stop

@push('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('byAppChart').getContext('2d');
new Chart(ctx, {
  type: 'bar',
  data: {
    labels: @json($labels),
    datasets: [{
      label: 'Incidents',
      data: @json($counts)
    }]
  },
  options: {
    responsive: true,
    plugins: { legend: { display: false }},
    scales: { y: { beginAtZero: true } }
  }
});
</script>
@endpush
@section('css')
    <style>
        .table th, .table td {
            vertical-align: middle;
        }
        .card-header {
            background-color: #f8f9fa;
            font-weight: bold;
        }
    </style>
@stop
