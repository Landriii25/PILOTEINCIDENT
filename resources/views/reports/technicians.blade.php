@extends('adminlte::page')

@section('title','Rapport · Techniciens')

@section('content_header')
    <h1 class="m-0">Techniciens</h1>
@stop

@section('content')
<div class="row">
    <div class="col-md-7">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>Charge & performance</strong>
                <div class="custom-control custom-switch">
                    <input type="checkbox" class="custom-control-input" id="toggleResolved" checked>
                    <label class="custom-control-label" for="toggleResolved">Afficher “Résolus (30j)”</label>
                </div>
            </div>
            <div class="card-body">
                <canvas id="techChart" height="180"></canvas>
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
                            <th>Technicien</th>
                            <th class="text-right">Ouverts</th>
                            <th class="text-right">Résolus 30j</th>
                            <th class="text-right">SLA à risque</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $r)
                            <tr>
                                <td>{{ $r->tech }}</td>
                                <td class="text-right">{{ $r->ouverts }}</td>
                                <td class="text-right">{{ $r->resolus30 }}</td>
                                <td class="text-right">{{ $r->sla_risk }}</td>
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
const ctx = document.getElementById('techChart').getContext('2d');
const labels   = @json($labels);
const openD    = @json($openData);
const solvedD  = @json($resolvedData);

const dsOpen = { label: 'Ouverts',  data: openD,  backgroundColor: 'rgba(54,162,235,0.6)' };
const dsSolv = { label: 'Résolus (30j)', data: solvedD, backgroundColor: 'rgba(75,192,192,0.6)' };

const techChart = new Chart(ctx, {
  type: 'bar',
  data: { labels, datasets: [dsOpen, dsSolv] },
  options: {
    responsive: true,
    plugins: { legend: { position: 'top' } },
    scales: { y: { beginAtZero: true } }
  }
});

document.getElementById('toggleResolved').addEventListener('change', (e)=>{
    if(e.target.checked){
        if(!techChart.data.datasets.find(d=>d.label===dsSolv.label)){
          techChart.data.datasets.push(dsSolv);
        }
    } else {
        techChart.data.datasets = techChart.data.datasets.filter(d=>d.label!==dsSolv.label);
    }
    techChart.update();
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
