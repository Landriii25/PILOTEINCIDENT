@extends('adminlte::page')

@section('title', 'Tableau de bord')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between">
        <h1 class="mb-0">Tableau de bord — Administrateur</h1>

        @auth
        <a href="{{ route('incidents.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle mr-1"></i> Créer un incident
        </a>
        @endauth
    </div>
@stop

@section('content')

{{-- ==================== Filtres rapides ==================== --}}
<div class="card mb-3">
    <form method="GET" class="card-body">
        <div class="form-row">
            <div class="col-md-3">
                <label>Priorité</label>
                <select name="priorite" class="form-control">
                    <option value="">Toutes</option>
                    @foreach(['Critique','Haute','Moyenne','Basse'] as $p)
                        <option value="{{ $p }}" @selected(request('priorite')===$p)>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label>Application</label>
                <select name="application_id" class="form-control">
                    <option value="">Toutes</option>
                    @foreach($applications as $app)
                        <option value="{{ $app->id }}" @selected((int)request('application_id') === $app->id)>{{ $app->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label>Afficher</label>
                <select name="open" class="form-control">
                    <option value="1" @selected(request('open','1')==='1')>Incidents ouverts</option>
                    <option value="0" @selected(request('open')==='0')>Tous (pour la liste “À traiter”)</option>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-primary btn-block mr-2">
                    <i class="fas fa-filter mr-1"></i> Filtrer
                </button>
                <a href="{{ route('dashboard') }}" class="btn btn-default">
                    Réinitialiser
                </a>
            </div>
        </div>
    </form>
</div>

{{-- ==================== KPIs ==================== --}}
<div class="row">
    <div class="col-lg-2 col-6">
        <div class="small-box bg-info">
            <div class="inner"><h3>{{ $openIncidents }}</h3><p>Incidents ouverts</p></div>
            <div class="icon"><i class="fas fa-folder-open"></i></div>
            <a href="{{ route('dashboard', ['open' => 1]) }}" class="small-box-footer">
                Voir <i class="fas fa-arrow-circle-right"></i>
            </a>

        </div>
    </div>
    <div class="col-lg-2 col-6">
        <div class="small-box bg-success">
            <div class="inner"><h3>{{ $slaPercent }}<sup style="font-size:20px">%</sup></h3><p>SLA respectés (30j)</p></div>
            <div class="icon"><i class="fas fa-shield-alt"></i></div>
            <a href="{{ route('dashboard') }}" class="small-box-footer">
                Détails <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-2 col-6">
        <div class="small-box bg-primary">
            <div class="inner"><h3>{{ $mttrHours }}h</h3><p>MTTR (30j)</p></div>
            <div class="icon"><i class="fas fa-clock"></i></div>
            <a href="{{ route('dashboard') }}" class="small-box-footer">
                Historique <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-2 col-6">
        <div class="small-box bg-warning">
            <div class="inner"><h3>{{ $slaRiskCount }}</h3><p>SLA à risque</p></div>
            <div class="icon"><i class="fas fa-stopwatch"></i></div>
            <a href="{{ route('dashboard', ['open' => 1]) }}" class="small-box-footer">
                Filtrer <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-2 col-6">
        <div class="small-box bg-danger">
            <div class="inner"><h3>{{ $critiqueOpen }}</h3><p>Critiques (P1) ouverts</p></div>
            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            <a href="{{ route('dashboard', ['priorite' => 'Critique', 'open' => 1]) }}" class="small-box-footer">
                Voir <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
    <div class="col-lg-2 col-6">
        <div class="small-box bg-secondary">
            <div class="inner"><h3>{{ $backlog7 }}</h3><p>Backlog &gt; 7 jours</p></div>
            <div class="icon"><i class="fas fa-hourglass-half"></i></div>
            <a href="{{ route('dashboard', ['open' => 1]) }}" class="small-box-footer">
                Voir <i class="fas fa-arrow-circle-right"></i>
            </a>
        </div>
    </div>
</div>

{{-- ==================== Listes ==================== --}}
<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title">À traiter maintenant</h3></div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Code</th><th>Titre</th><th>Appli</th><th>Priorité</th>
                            <th>Technicien</th><th>Échéance SLA</th><th class="text-right pr-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($aTraiter as $it)
                            <tr>
                                <td>{{ $it->code ?? $it->id }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($it->titre ?? $it->description, 40) }}</td>
                                <td>{{ optional($it->application)->nom }}</td>
                                <td>
                                    <span class="badge
                                        @if($it->priorite=='Critique') badge-danger
                                        @elseif($it->priorite=='Haute') badge-warning
                                        @elseif($it->priorite=='Moyenne') badge-info
                                        @else badge-secondary @endif
                                    ">{{ $it->priorite }}</span>
                                </td>
                                <td>{{ optional($it->technicien)->name ?? '—' }}</td>
                                <td>
                                    @if($it->due_at)
                                        @php $late = now()->greaterThan($it->due_at); @endphp
                                        <span class="text-{{ $late ? 'danger' : 'muted' }}">
                                            {{ $it->due_at->diffForHumans() }}
                                        </span>
                                    @else — @endif
                                </td>
                                <td class="text-right pr-3">
                                    <a href="{{ route('incidents.show',$it) }}" class="btn btn-sm btn-outline-primary">Ouvrir</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted p-3">Rien à traiter maintenant.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-header"><h3 class="card-title">SLA à risque / dépassés</h3></div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($slaRisk as $it)
                        @php $late = now()->greaterThan($it->due_at); $badge = $late ? 'danger' : 'warning'; @endphp
                        <li class="list-group-item d-flex align-items-center">
                            <span class="badge badge-{{ $badge }} mr-2">&nbsp;</span>
                            <div class="flex-fill">
                                <div class="font-weight-bold">{{ $it->code ?? $it->id }} — {{ $it->priorite }}</div>
                                <small class="text-muted">
                                    {{ optional($it->application)->nom }} •
                                    Échéance {{ $it->due_at? $it->due_at->diffForHumans(): '—' }}
                                </small>
                            </div>
                            <a class="btn btn-sm btn-outline-primary ml-2" href="{{ route('incidents.show',$it) }}">Voir</a>
                        </li>
                    @empty
                        <li class="list-group-item text-center text-muted">Rien d’urgent 🎉</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

{{-- ==================== Graphiques ==================== --}}
<div class="row">
    <div class="col-xl-4">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Répartition par priorité (ouverts)</h3></div>
            <div class="card-body"><canvas id="chartPriorities"></canvas></div>
        </div>
    </div>
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Top applications (30 derniers jours)</h3></div>
            <div class="card-body"><canvas id="chartTopApps"></canvas></div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function(){
    const priorityLabels = @json($priorityLabels);
    const priorityData   = @json($priorityData);
    const ctx1 = document.getElementById('chartPriorities').getContext('2d');
    new Chart(ctx1, { type:'doughnut', data:{
        labels: priorityLabels,
        datasets:[{ data: priorityData, backgroundColor:['#ef4444','#f59e0b','#3b82f6','#6b7280'] }]
    }, options:{ responsive:true, plugins:{ legend:{ position:'bottom' } }}});

    const topAppLabels = @json($topAppLabels);
    const topAppData   = @json($topAppData);
    const ctx2 = document.getElementById('chartTopApps').getContext('2d');
    new Chart(ctx2, { type:'bar', data:{
        labels: topAppLabels,
        datasets:[{ data: topAppData, label:'Incidents (30j)', backgroundColor:'#2563eb' }]
    }, options:{ responsive:true, plugins:{ legend:{ display:false } }, scales:{ y:{ beginAtZero:true, ticks:{ precision:0 } } }});
})();
</script>
@endsection
