@extends('adminlte::page')
@section('title','Mon tableau de bord')

@section('content_header')
<div class="d-flex align-items-center justify-content-between">
  <h1 class="mb-0">Mon tableau de bord</h1>
  @can('incidents.create')
    <a href="{{ route('incidents.create') }}" class="btn btn-primary">
      <i class="fas fa-plus-circle mr-1"></i> Créer un incident
    </a>
  @endcan
</div>
@endsection

@section('content')
@php
    // Garde-fous si le contrôleur n'a pas injecté certaines variables
    $labelsMonths = $labelsMonths ?? [];
    $dataMonths   = $dataMonths ?? [];
    // $aSuivre est une LengthAwarePaginator (paginate(5)) côté contrôleur
@endphp

{{-- KPI --}}
<div class="row">
  <div class="col-lg-4 col-12">
    <div class="small-box bg-info">
      <div class="inner">
        <h3>{{ $myOpen ?? 0 }}</h3>
        <p>Mes incidents ouverts</p>
      </div>
      <div class="icon"><i class="fas fa-folder-open"></i></div>
      <a href="{{ route('incidents.index') }}?me=1&open=1" class="small-box-footer">
        Voir <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>

  <div class="col-lg-4 col-12">
    <div class="small-box bg-danger">
      <div class="inner">
        <h3>{{ $myCritique ?? 0 }}</h3>
        <p>Critiques (ouverts)</p>
      </div>
      <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
      <a href="{{ route('incidents.index') }}?me=1&open=1&priorite=Critique" class="small-box-footer">
        Voir <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>

  <div class="col-lg-4 col-12">
    <div class="small-box bg-success">
      <div class="inner">
        <h3>{{ $myResolved30 ?? 0 }}</h3>
        <p>Résolus (30 derniers jours)</p>
      </div>
      <div class="icon"><i class="fas fa-check-circle"></i></div>
      <a href="{{ route('incidents.index') }}?me=1&status=resolved&range=30" class="small-box-footer">
        Historique <i class="fas fa-arrow-circle-right"></i>
      </a>
    </div>
  </div>
</div>

{{-- Graphe barres : incidents créés par mois (6 mois) --}}
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header"><h3 class="card-title">Mes incidents créés — 6 derniers mois</h3></div>
      <div class="card-body">
        @if(collect($dataMonths)->sum() === 0)
          <div class="text-muted text-center py-4">Pas encore de données ce semestre.</div>
        @else
          <canvas id="chartUserMonthly" height="100"></canvas>
        @endif
      </div>
    </div>
  </div>
</div>

{{-- À suivre --}}
<div class="card">
  <div class="card-header"><h3 class="card-title">À suivre</h3></div>
  <div class="card-body p-0">
    <table class="table table-striped mb-0">
      <thead>
        <tr>
          <th>Code</th>
          <th>Titre</th>
          <th>Appli</th>
          <th>Assigné</th>
          <th>Échéance</th>
          <th class="text-right pr-3">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($aSuivre as $it)
          <tr>
            <td class="text-monospace font-weight-bold">{{ $it->code }}</td>
            <td>{{ \Illuminate\Support\Str::limit($it->titre ?? $it->description, 50) }}</td>
            <td>{{ optional($it->application)->nom }}</td>
            <td>{{ optional($it->technicien)->name ?? '—' }}</td>
            <td>{{ $it->due_at ? $it->due_at->diffForHumans() : '—' }}</td>
            <td class="text-right pr-3">
              <a href="{{ route('incidents.show',$it) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                <i class="fas fa-eye"></i>
              </a>
              @can('incidents.update.own')
              <a href="{{ route('incidents.edit',$it) }}" class="btn btn-sm btn-outline-warning" title="Éditer">
                <i class="fas fa-edit"></i>
              </a>
              @endcan
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-center text-muted p-3">Rien à suivre pour l’instant.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  {{-- Pagination Bootstrap 4 --}}
  <div class="card-footer d-flex justify-content-center">
    {{ $aSuivre->links('pagination::bootstrap-4') }}
  </div>
</div>
@endsection

@section('js')
{{-- Chart.js CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@if(collect($dataMonths)->sum() > 0)
<script>
  const UM_LABELS = @json($labelsMonths);
  const UM_DATA   = @json($dataMonths);

  const ctxUM = document.getElementById('chartUserMonthly').getContext('2d');
  new Chart(ctxUM, {
    type: 'bar',
    data: {
      labels: UM_LABELS,
      datasets: [{
        label: 'Incidents créés',
        data: UM_DATA,
        backgroundColor: '#007bff',
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { display: false }
      },
      scales: {
        y: { beginAtZero: true, ticks: { precision: 0 } }
      }
    }
  });
</script>
@endif
@endsection
