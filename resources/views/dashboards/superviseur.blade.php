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

{{-- Graphes côte à côte --}}
<div class="row">
  {{-- Incidents par technicien --}}
  <div class="col-md-6">
    <div class="card card-compact">
      <div class="card-header py-2">
        <h3 class="card-title mb-0">Incidents par technicien (ouverts)</h3>
      </div>
      <div class="card-body">
        @if(!empty($techLabels) && collect($techLabels)->filter()->count() > 0)
          <canvas id="chartByTech" class="chart-xs"></canvas>
        @else
          <div class="text-muted small">Aucune donnée à afficher pour le moment.</div>
        @endif
      </div>
    </div>
  </div>

  {{-- Temps moyen de prise en charge --}}
  <div class="col-md-6">
    <div class="card card-compact">
      <div class="card-header py-2">
        <h3 class="card-title mb-0">Temps moyen de prise en charge (h) — 30j</h3>
      </div>
      <div class="card-body">
        @if(!empty($avgTechLabels) && collect($avgTechLabels)->filter()->count() > 0)
          <canvas id="chartAvgPickup" class="chart-xs"></canvas>
        @else
          <div class="text-muted small">Aucune donnée à afficher pour le moment.</div>
        @endif
      </div>
    </div>
  </div>
</div>

{{-- SLA à risque --}}
<div class="row">
  <div class="col-12">
    <div class="card card-compact">
      <div class="card-header py-2">
        <h3 class="card-title mb-0">SLA à risque (Top 10)</h3>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-striped table-sm mb-0 sla-table">
            <thead class="thead-light">
              <tr>
                <th class="col-code">Code</th>
                <th>Appli</th>
                <th class="col-tech">Technicien</th>
                <th class="col-due">Échéance</th>
                <th class="text-right col-actions">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse($slaList as $it)
                <tr>
                  <td class="text-monospace nowrap">{{ $it->code }}</td>
                  <td class="text-truncate" title="{{ optional($it->application)->nom }}">
                    {{ optional($it->application)->nom }}
                  </td>
                  <td class="text-truncate">{{ optional($it->technicien)->name ?? 'Non assigné' }}</td>
                  <td class="nowrap">{{ $it->due_at ? $it->due_at->diffForHumans() : '—' }}</td>
                  <td class="text-right pr-2">
                    <a href="{{ route('incidents.show',$it) }}" class="btn btn-xs btn-outline-primary" title="Voir">
                      <i class="fas fa-eye"></i>
                    </a>
                    @can('incidents.update.any')
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
</div>

@endsection

@push('css')
<style>
    card.card-compact .card-body{ padding:.75rem 1rem; }
    .chart-xs{ height:240px !important; }              /* Hauteur explicite des canvases */
    .text-monospace{
    font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono","Courier New", monospace;
    }
    .nowrap{ white-space: nowrap; }                    /* Pas de retour à la ligne */
    .sla-table .col-code{ width:112px; }              /* Code compact & stable */
    .sla-table .col-tech{ width:220px; }              /* Place pour nom complet */
    .sla-table .col-due{ width:120px; }               /* Échéance */
    .sla-table .col-actions{ width:90px; }            /* Deux icônes */
    .table td, .table th{ vertical-align:middle;}
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

  // Incidents par technicien
  (function(){
    const labels = @json($techLabels ?? []);
    const data   = @json($techCounts ?? []);
    const el = document.getElementById('chartByTech');
    if (!el || !labels.length) return;

    new Chart(el.getContext('2d'), {
      type: 'bar',
      data: {
        labels,
        datasets: [{
          label: 'Incidents ouverts',
          data,
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
  })();

  // Temps moyen de prise en charge
  (function(){
    const labels = @json($avgTechLabels ?? []);
    const data   = @json($avgTechHours ?? []);
    const el = document.getElementById('chartAvgPickup');
    if (!el || !labels.length) return;

    new Chart(el.getContext('2d'), {
      type: 'bar',
      data: {
        labels,
        datasets: [{
          label: 'Heures (moyenne)',
          data,
          backgroundColor: '#6610f2',
          borderWidth: 1
        }]
      },
      options: {
        plugins: {
          legend: { display: false },
          tooltip: { callbacks: { label: (ctx) => ${ctx.parsed.y} h } }
        },
        scales: { y: { beginAtZero: true } }
      }
    });
  })();
</script>
@endsection
