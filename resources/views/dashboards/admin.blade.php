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
        // Valeurs par défaut pour éviter les erreurs si les variables sont vides
        $priorites       = $priorites       ?? ['Critique','Haute','Moyenne','Basse'];
        $byPriority      = $byPriority      ?? [0,0,0,0];
        $appLabels       = $appLabels       ?? [];
        $appCounts       = $appCounts       ?? [];
        $avgPickupLabels = $avgPickupLabels ?? [];
        $avgPickupData   = $avgPickupData   ?? [];
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
                <a href="{{-- route('incidents.sla') --}}" class="small-box-footer">Détails <i class="fas fa-arrow-circle-right"></i></a>
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

    {{-- Tendance 30 jours et MTTA --}}
    <div class="row">
        <div class="col-md-8">
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

        {{-- Bloc Temps moyen de prise en charge --}}
        <div class="col-md-4">
            <div class="card card-compact">
                <div class="card-header py-2">
                    <h3 class="card-title mb-0">Prise en charge (h) — 30j</h3>
                </div>
                <div class="card-body">
                    <canvas id="chartAvgPickup" class="chart-xs"></canvas>
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

    {{-- Réglages globaux --}}
    <script>
    if (window.Chart) {
      Chart.defaults.font.size = 11;
      Chart.defaults.plugins.legend.display = true;
      Chart.defaults.plugins.legend.position = 'bottom';
      Chart.defaults.plugins.legend.labels.boxWidth = 12;
      Chart.defaults.maintainAspectRatio = false;
    }
    </script>

    <script>
        // Graphe 1 : Doughnut Priorités
        new Chart(document.getElementById('chartPriority'), {
            type: 'doughnut',
            data: {
                labels: @json($priorites),
                datasets: [{ data: @json($byPriority), backgroundColor: ['#dc3545','#fd7e14','#17a2b8','#6c757d'], borderWidth: 0 }]
            },
            options: { cutout: '60%' }
        });

        // Graphe 2 : Barres Horizontales Applications
        new Chart(document.getElementById('chartApps'), {
            type: 'bar',
            data: {
                labels: @json($appLabels),
                datasets: [{
                    label: 'Incidents', data: @json($appCounts),
                    backgroundColor: 'rgba(0,123,255,.75)', borderColor: '#007bff',
                    borderWidth: 1, maxBarThickness: 20
                }]
            },
            options: {
                indexAxis: 'y',
                scales: { x: { beginAtZero: true, ticks: { precision: 0 } } },
                plugins: { legend: { display: false } }
            }
        });

        // Graphe 3 : Tendance 30 jours
        const trendChart = new Chart(document.getElementById('chartTrend'), {
            type: 'bar',
            data: {
                labels: @json($trendLabels ?? []),
                datasets: [
                    { label: 'Créés', data: @json($trendCreated ?? []), backgroundColor: 'rgba(23,162,184,.85)' },
                    { label: 'Résolus', data: @json($trendResolved ?? []), backgroundColor: 'rgba(40,167,69,.8)' }
                ]
            },
            options: {
                interaction: { mode: 'index', intersect: false },
                scales: { x: { stacked: false }, y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
        document.getElementById('toggleResolved')?.addEventListener('change', function () {
            trendChart.data.datasets[1].hidden = !this.checked;
            trendChart.update();
        });

        // --- MODIFICATION CI-DESSOUS ---
        // Graphe 4 : Temps moyen de prise en charge (MTTA)
        const avgPickupCtx = document.getElementById('chartAvgPickup');
        if (avgPickupCtx) {
            new Chart(avgPickupCtx, {
                type: 'bar',
                data: {
                    // On utilise les variables envoyées par le contrôleur
                    labels: @json($avgPickupLabels),
                    datasets: [{
                        label: 'Heures (moyenne)',
                        // On utilise les données envoyées par le contrôleur
                        data: @json($avgPickupData),
                        backgroundColor: '#6610f2',
                        maxBarThickness: 25
                    }]
                },
                options: {
                    indexAxis: 'y', // Barres horizontales pour une meilleure lisibilité
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => `${ctx.parsed.x} heures` // Affiche "X heures" au survol
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            title: { display: true, text: 'Temps moyen en heures' }
                        }
                    }
                }
            });
        }
    </script>
@endsection
