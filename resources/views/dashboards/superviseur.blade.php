@extends('adminlte::page')
@section('title','Dashboard Superviseur')

@section('content_header')
  <div class="d-flex justify-content-between align-items-center">
    <h1 class="mb-0">Tableau de bord — Superviseur</h1>

    @can('incidents.create')
      <a href="{{ route('incidents.create') }}" class="btn btn-primary">
        <i class="fas fa-plus-circle mr-1"></i> Créer un incident
      </a>
    @endcan
  </div>
@endsection

@section('content')
@php
    $techLabels = $techLabels ?? [];
    $techCounts = $techCounts ?? [];
@endphp

{{-- KPI --}}
<div class="row">
  <div class="col-lg-4 col-12">
    <div class="small-box bg-info">
      <div class="inner">
        <h3>{{ $openCount ?? 0 }}</h3>
        <p>Incidents ouverts</p>
      </div>
      <div class="icon"><i class="fas fa-folder-open"></i></div>
      <a href="{{ route('incidents.index') }}?open=1" class="small-box-footer">
        Voir <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>
  <div class="col-lg-4 col-12">
    <div class="small-box bg-danger">
      <div class="inner">
        <h3>{{ $slaAtRisk ?? 0 }}</h3>
        <p>SLA à risque</p>
      </div>
      <div class="icon"><i class="fas fa-stopwatch"></i></div>
      <a href="{{ route('incidents.sla') }}" class="small-box-footer">
        Détails <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>
  <div class="col-lg-4 col-12">
    <div class="small-box bg-warning">
      <div class="inner">
        <h3>{{ $reassign30 ?? 0 }}</h3>
        <p>Réaffectations (30j)</p>
      </div>
      <div class="icon"><i class="fas fa-exchange-alt"></i></div>
      <a href="{{ route('incidents.index') }}" class="small-box-footer">
        Historique <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>
</div>

<div class="row">
  {{-- Graphe par technicien (compact) --}}
  <div class="col-md-6">
    <div class="card card-compact">
      <div class="card-header py-2"><h3 class="card-title mb-0">Incidents par technicien (ouverts)</h3></div>
      <div class="card-body">
        <canvas id="chartByTech" class="chart-xs"></canvas>
      </div>
    </div>
  </div>
   <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Temps moyen de prise en charge (h) — 30j</h3></div>
                <div class="card-body">
                    <canvas id="chartAvgPickup" height="160"></canvas>
                </div>
            </div>
        </div>
    </div>
  {{-- SLA à risque --}}
  <div class="col-md-6">
    <div class="card card-compact">
      <div class="card-header py-2"><h3 class="card-title mb-0">SLA à risque (Top 10)</h3></div>
      <div class="card-body p-0">
        <table class="table table-striped table-sm mb-0">
          <thead class="thead-light">
            <tr>
              <th style="width:105px">Code</th>
              <th>Appli</th>
              <th>Technicien</th>
              <th style="width:120px">Échéance</th>
              <th class="text-right" style="width:92px">Actions</th>
            </tr>
          </thead>
          <tbody>
            @forelse($slaList as $it)
            <tr>
              <td class="text-monospace">{{ $it->code }}</td>
              <td class="text-truncate" title="{{ optional($it->application)->nom }}">
                {{ optional($it->application)->nom }}
              </td>
              <td>{{ optional($it->technicien)->name ?? 'Non assigné' }}</td>
              <td>{{ $it->due_at ? $it->due_at->diffForHumans() : '—' }}</td>
              <td class="text-right pr-2">
                <a href="{{ route('incidents.show',$it) }}" class="btn btn-xs btn-outline-primary" title="Voir">
                  <i class="fas fa-eye"></i>
                </a>
                @can('update incidents')
                <a href="{{ route('incidents.edit',$it) }}" class="btn btn-xs btn-outline-warning" title="Réassigner / Éditer">
                  <i class="fas fa-user-edit"></i>
                </a>
                @endcan
              </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center text-muted py-3">Aucun incident à risque.</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection

@push('css')
<style>
  .card.card-compact .card-body{ padding:.75rem 1rem; }
  .chart-xs{ height:220px !important; }
  .text-monospace{ font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
  .table td, .table th{ vertical-align: middle; }
</style>
@endpush

@section('js')
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    if (window.Chart) {
      Chart.defaults.font.size = 11;
      Chart.defaults.plugins.legend.display = false;
      Chart.defaults.maintainAspectRatio = false;
    }

    const TECH_LABELS = @json($techLabels);
    const TECH_DATA   = @json($techCounts);

    new Chart(document.getElementById('chartByTech'), {
      type: 'bar',
      data: {
        labels: TECH_LABELS,
        datasets: [{
          label: 'Incidents ouverts',
          data: TECH_DATA,
          backgroundColor: 'rgba(0,123,255,.75)',
          borderColor: '#007bff',
          borderWidth: 1,
          maxBarThickness: 20
        }]
      },
      options: {
        indexAxis: 'y',
        scales: {
          x: { beginAtZero: true, ticks: { precision: 0 } },
          y: { ticks: { autoSkip: true, maxTicksLimit: 10 } }
        }
      }
    });
  </script>
  <script>
  const AVG_TECH_LABELS = @json($avgTechLabels ?? []);
  const AVG_TECH_HOURS  = @json($avgTechHours ?? []);

  if (document.getElementById('chartAvgPickup')) {
    const ctxAP = document.getElementById('chartAvgPickup').getContext('2d');
    new Chart(ctxAP, {
      type: 'bar',
      data: {
        labels: AVG_TECH_LABELS,
        datasets: [{
          label: 'Heures (moyenne)',
          data: AVG_TECH_HOURS,
          backgroundColor: '#6610f2', // violet
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false },
          tooltip: {
            callbacks: {
              label: (ctx) => ${ctx.parsed.y} h
            }
          }
        },
        scales: {
          y: { beginAtZero: true }
        }
      }
    });
  }
</script>

@endsection
