@extends('adminlte::page')
@section('title','Dashboard Technicien')

@section('content_header')
  <h1 class="mb-0">Tableau de bord — Technicien</h1>
@endsection

@section('content')
@php
  $priorites      = $priorites ?? ['Critique','Haute','Moyenne','Basse'];
  $byPriorityMine = $byPriorityMine ?? [0,0,0,0];
@endphp

{{-- KPI --}}
<div class="row">
  <div class="col-lg-4 col-12">
    <div class="small-box bg-warning">
      <div class="inner">
        <h3>{{ $assignedOpen ?? 0 }}</h3>
        <p>Assignés à moi</p>
      </div>
      <div class="icon"><i class="fas fa-user-cog"></i></div>
      <a href="{{ route('incidents.index') }}?assigned=me&open=1" class="small-box-footer">
        Voir <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>
  <div class="col-lg-4 col-12">
    <div class="small-box bg-success">
      <div class="inner">
        <h3>{{ $resolvedByMe30 ?? 0 }}</h3>
        <p>Résolus (30j)</p>
      </div>
      <div class="icon"><i class="fas fa-check-circle"></i></div>
      <a href="{{ route('incidents.index') }}?assigned=me&status=resolved&range=30" class="small-box-footer">
        Historique <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>
  <div class="col-lg-4 col-12">
    <div class="small-box bg-danger">
      <div class="inner">
        <h3>{{ $mySlaRisk ?? 0 }}</h3>
        <p>SLA à risque (moi)</p>
      </div>
      <div class="icon"><i class="fas fa-stopwatch"></i></div>
      <a href="{{ route('incidents.index') }}?assigned=me&open=1" class="small-box-footer">
        Voir <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>
</div>

{{-- Graphe : assignés à moi par priorité --}}
<div class="row">
  <div class="col-md-6">
    <div class="card">
      <div class="card-header"><h3 class="card-title">Mes incidents par priorité</h3></div>
      <div class="card-body">
        <canvas id="chartMinePriority" height="160"></canvas>
      </div>
    </div>
  </div>

  {{-- File priorisée --}}
 <div class="col-md-6">
  <div class="card card-compact">
    <div class="card-header py-2"><h3 class="card-title mb-0">Ma file (échéances proches)</h3></div>
    <div class="card-body p-0">
      <ul class="list-group list-group-flush">
        @forelse($myQueue as $it)
          <li class="list-group-item d-flex align-items-center">
            <div class="flex-grow-1">
              <span class="text-monospace font-weight-bold">{{ $it->code }}</span>
              — {{ \Illuminate\Support\Str::limit($it->titre ?? $it->description, 60) }}
              <div class="small text-muted">
                {{ optional($it->application)->nom }} • Demandeur : {{ optional($it->user)->name ?? '—' }}
              </div>
            </div>
            <div class="text-right">
              <div class="mb-1">
                @if($it->priorite === 'Critique')
                  <span class="badge badge-danger">Critique</span>
                @elseif($it->priorite === 'Haute')
                  <span class="badge badge-warning">Haute</span>
                @elseif($it->priorite === 'Moyenne')
                  <span class="badge badge-info">Moyenne</span>
                @else
                  <span class="badge badge-secondary">Basse</span>
                @endif
              </div>
              <small class="text-muted d-block mb-1">
                @if($it->due_at) Échéance : {{ $it->due_at->diffForHumans() }} @else Échéance : — @endif
              </small>
              <div class="btn-group btn-group-sm">
                <a class="btn btn-outline-primary" title="Ouvrir" href="{{ route('incidents.show',$it) }}">
                  <i class="fas fa-eye"></i>
                </a>
                @can('incidents.resolve.assigned')
                <a class="btn btn-outline-success" title="Résoudre" href="{{ route('incidents.edit',$it) }}">
                  <i class="fas fa-check"></i>
                </a>
                @endcan
              </div>
            </div>
          </li>
        @empty
          <li class="list-group-item text-center text-muted py-3">Aucun ticket.</li>
        @endforelse
      </ul>
    </div>

    {{-- ⬇ Liens de pagination (Bootstrap 4) --}}
    <div class="card-footer pb-2">
      <div class="d-flex justify-content-center">
        {{ $myQueue->links('pagination::bootstrap-4') }}
      </div>
    </div>
    </div>
</div>

</div>
@endsection

@section('js')
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    const MINE_LABELS = @json($priorites);
    const MINE_DATA   = @json($byPriorityMine);

    const ctxM = document.getElementById('chartMinePriority').getContext('2d');
    new Chart(ctxM, {
      type: 'bar',
      data: {
        labels: MINE_LABELS,
        datasets: [{
          label: 'Assignés à moi (ouverts)',
          data: MINE_DATA,
          backgroundColor: ['#dc3545','#fd7e14','#17a2b8','#6c757d'],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false },
          tooltip: { enabled: true }
        },
        scales: {
          y: { beginAtZero: true, ticks: { precision: 0 } }
        }
      }
    });
  </script>
@endsection
