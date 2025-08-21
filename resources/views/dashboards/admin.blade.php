@extends('adminlte::page')

@section('title', 'Dashboard')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">Tableau de bord — Administrateur</h1>

        @can('incidents.create')
        <a href="{{ route('incidents.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle mr-1"></i> Créer un incident
        </a>
        @endcan
    </div>
@endsection

@section('content')
    @php
        $priorites  = $priorites  ?? ['Critique','Haute','Moyenne','Basse'];
        $byPriority = $byPriority ?? [0,0,0,0];
        $appLabels  = $appLabels  ?? [];
        $appCounts  = $appCounts  ?? [];
    @endphp

    {{-- KPI --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $incidentsOpen ?? 0 }}</h3>
                    <p>Incidents ouverts</p>
                </div>
                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
                <a href="{{ route('incidents.index') }}?open=1" class="small-box-footer">Voir <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $slaAtRisk ?? 0 }}</h3>
                    <p>SLA à risque</p>
                </div>
                <div class="icon"><i class="fas fa-stopwatch"></i></div>
                <a href="{{ route('incidents.sla') }}" class="small-box-footer">Détails <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $resolved30 ?? 0 }}</h3>
                    <p>Résolus (30j)</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
                <a href="{{ route('incidents.index') }}?status=resolved" class="small-box-footer">Historique <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $appsCount ?? 0 }}</h3>
                    <p>Applications</p>
                </div>
                <div class="icon"><i class="fas fa-th-large"></i></div>
                <a href="{{ route('applications.index') }}" class="small-box-footer">Gérer <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    {{-- Graphiques compacts --}}
    <div class="row">
        <div class="col-md-6">
            <div class="card card-compact">
                <div class="card-header py-2"><h3 class="card-title mb-0">Incidents par priorité</h3></div>
                <div class="card-body">
                    <canvas id="chartPriority" class="chart-xs"></canvas>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-compact">
                <div class="card-header py-2"><h3 class="card-title mb-0">Incidents par application (Top 10)</h3></div>
                <div class="card-body">
                    <canvas id="chartApps" class="chart-xs"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Tendance 30 jours --}}
    <div class="row">
        <div class="col-12">
            <div class="card card-compact">
                <div class="card-header py-2 d-flex align-items-center justify-content-between">
                    <h3 class="card-title mb-0">Tendance des incidents (30 jours)</h3>
                    <div class="custom-control custom-switch mb-0">
                        <input type="checkbox" class="custom-control-input" id="toggleResolved" checked>
                        <label class="custom-control-label" for="toggleResolved">Afficher “Résolus”</label>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="chartTrend" class="chart-xs"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
    {{-- Bloc Temps moyen de prise en charge --}}
    <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Temps moyen de prise en charge (h) — 30j</h3>
                </div>
                <div class="card-body">
                    <canvas id="chartAvgPickup" height="160"></canvas>
                </div>
            </div>
        </div>

        {{-- Bloc Utilisateurs --}}
        <div class="col-md-6">
            <div class="card card-compact">
                <div class="card-header py-2">
                    <h3 class="card-title mb-0">Utilisateurs</h3>
                </div>
                <div class="card-body d-flex align-items-center">
                    <i class="fas fa-users fa-2x mr-3 text-muted"></i>
                    <div>
                        <div class="h4 mb-0">{{ $usersCount ?? 0 }}</div>
                        <small class="text-muted">Comptes enregistrés</small>
                    </div>
                    <a href="{{ route('users.index') }}" class="btn btn-link ml-auto">Gérer</a>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('css')
<style>
  .card.card-compact .card-body{ padding:.75rem 1rem; }
  .chart-xs{ height:220px !important; }
</style>
@endpush

@section('js')
    {{-- Chart.js CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- Réglages globaux compacts (une fois suffit si déjà chargés ailleurs) --}}
    <script>
    if (window.Chart) {
      Chart.defaults.font.size = 11;
      Chart.defaults.plugins.legend.display = true;
      Chart.defaults.plugins.legend.position = 'bottom';
      Chart.defaults.plugins.legend.labels.boxWidth = 12;
      Chart.defaults.plugins.tooltip.enabled = true;
      Chart.defaults.elements.point.radius = 2;
      Chart.defaults.elements.point.hoverRadius = 3;
      Chart.defaults.elements.line.tension = 0.25;
      Chart.defaults.maintainAspectRatio = false;
    }
    </script>

    <script>
        const PRIORITY_LABELS = @json($priorites);
        const PRIORITY_DATA   = @json($byPriority);
        const APP_LABELS      = @json($appLabels);
        const APP_DATA        = @json($appCounts);

        // Doughnut : Incidents par priorité (compact)
        new Chart(document.getElementById('chartPriority'), {
            type: 'doughnut',
            data: {
                labels: PRIORITY_LABELS,
                datasets: [{
                    data: PRIORITY_DATA,
                    backgroundColor: ['#dc3545','#fd7e14','#17a2b8','#6c757d'],
                    borderWidth: 0
                }]
            },
            options: { cutout: '60%' }
        });

        // Barres horizontales : Top applications
        new Chart(document.getElementById('chartApps'), {
            type: 'bar',
            data: {
                labels: APP_LABELS,
                datasets: [{
                    label: 'Incidents',
                    data: APP_DATA,
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
                    y: { ticks: { autoSkip: true, maxTicksLimit: 8 } }
                },
                plugins: { legend: { display: false } }
            }
        });
    </script>

    <script>
        // Tendance 30 jours (barres + toggle “Résolus”)
        const TREND_LABELS   = @json($trendLabels ?? []);
        const TREND_CREATED  = @json($trendCreated ?? []);
        const TREND_RESOLVED = @json($trendResolved ?? []);

        const trendChart = new Chart(document.getElementById('chartTrend'), {
            type: 'bar',
            data: {
                labels: TREND_LABELS,
                datasets: [
                    {
                        label: 'Créés',
                        data: TREND_CREATED,
                        backgroundColor: 'rgba(23,162,184,.85)',
                        borderColor: '#17a2b8',
                        borderWidth: 1,
                        maxBarThickness: 18
                    },
                    {
                        label: 'Résolus',
                        data: TREND_RESOLVED,
                        backgroundColor: 'rgba(40,167,69,.8)',
                        borderColor: '#28a745',
                        borderWidth: 1,
                        maxBarThickness: 18
                    }
                ]
            },
            options: {
                interaction: { mode: 'index', intersect: false },
                scales: {
                    x: { stacked: false, ticks: { maxTicksLimit: 10 } },
                    y: { beginAtZero: true, ticks: { precision: 0 } }
                }
            }
        });

        document.getElementById('toggleResolved')?.addEventListener('change', function () {
            trendChart.data.datasets[1].hidden = !this.checked;
            trendChart.update();
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
