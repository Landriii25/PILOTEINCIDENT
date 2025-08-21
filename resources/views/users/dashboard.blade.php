@extends('adminlte::page')

@section('title', 'Mon tableau de bord')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between">
        <h1 class="mb-0">Mon tableau de bord</h1>
        @can('create incidents')
        <a href="{{ route('incidents.create') }}" class="btn btn-primary">
            <i class="fas fa-plus-circle mr-1"></i> Créer un incident
        </a>
        @endcan
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner"><h3>{{ $myOpen }}</h3><p>Mes incidents ouverts</p></div>
            <div class="icon"><i class="fas fa-folder-open"></i></div>
            <a href="{{ route('incidents.index') }}?me=1&open=1" class="small-box-footer">Voir <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner"><h3>{{ $mySlaPercent }}<sup style="font-size:20px">%</sup></h3><p>SLA respectés (30j)</p></div>
            <div class="icon"><i class="fas fa-shield-alt"></i></div>
            <a href="{{ route('incidents.index') }}?me=1&range=30" class="small-box-footer">Détails <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner"><h3>{{ $myResolved30 }}</h3><p>Résolus par moi (30j)</p></div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <a href="{{ route('incidents.index') }}?me=1&status=resolved&range=30" class="small-box-footer">Historique <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner"><h3>{{ $myCritiqueOpen }}</h3><p>Mes critiques (ouverts)</p></div>
            <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            <a href="{{ route('incidents.index') }}?me=1&priorite=Critique&open=1" class="small-box-footer">Voir <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>

@if($isTech)
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner"><h3>{{ $assignedOpen }}</h3><p>Assignés à moi (ouverts)</p></div>
            <div class="icon"><i class="fas fa-user-cog"></i></div>
            <a href="{{ route('incidents.index') }}?assigned=me&open=1" class="small-box-footer">Voir <i class="fas fa-arrow-circle-right"></i></a>
        </div>
    </div>
</div>
@endif

<div class="row">
    <div class="col-xl-8">
        <div class="card">
            <div class="card-header"><h3 class="card-title">À suivre</h3></div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <thead>
                        <tr>
                            <th>Code</th><th>Titre</th><th>Appli</th><th>Priorité</th>
                            <th>Assigné</th><th>Échéance SLA</th><th class="text-right pr-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($aSuivre as $it)
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
                                    <span class="text-{{ $late ? 'danger' : 'muted' }}">{{ $it->due_at->diffForHumans() }}</span>
                                @else — @endif
                            </td>
                            <td class="text-right pr-3">
                                <a href="{{ route('incidents.show',$it) }}" class="btn btn-sm btn-outline-primary">Ouvrir</a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted p-3">Aucun incident en cours.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($isTech)
        <div class="card">
            <div class="card-header"><h3 class="card-title">Mes tickets assignés</h3></div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($assignedList as $it)
                    @php $late = $it->due_at && now()->greaterThan($it->due_at); @endphp
                    <li class="list-group-item d-flex align-items-center">
                        <span class="badge badge-{{ $late ? 'danger' : 'warning' }} mr-2">&nbsp;</span>
                        <div class="flex-fill">
                            <div class="font-weight-bold">{{ $it->code }} — {{ $it->priorite }}</div>
                            <small class="text-muted">
                                {{ optional($it->application)->nom }} • Demandeur {{ optional($it->user)->name ?? '—' }} •
                                Échéance {{ $it->due_at? $it->due_at->diffForHumans(): '—' }}
                            </small>
                        </div>
                        <a class="btn btn-sm btn-outline-primary ml-2" href="{{ route('incidents.show',$it) }}">Voir</a>
                    </li>
                    @empty
                    <li class="list-group-item text-center text-muted">Aucun ticket en cours.</li>
                    @endforelse
                </ul>
            </div>
        </div>
        @endif
    </div>

    <div class="col-xl-4">
        <div class="card">
            <div class="card-header"><h3 class="card-title">Derniers incidents créés</h3></div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($mesRecents as $it)
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <strong>{{ $it->code ?? $it->id }}</strong>
                            <small class="text-muted">{{ $it->created_at->diffForHumans() }}</small>
                        </div>
                        <div>{{ \Illuminate\Support\Str::limit($it->titre ?? $it->description, 60) }}</div>
                        <small class="text-muted">
                            {{ optional($it->application)->nom }} •
                            Assigné {{ optional($it->technicien)->name ?? '—' }}
                        </small>
                    </li>
                    @empty
                    <li class="list-group-item text-center text-muted">Aucun historique récent.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- Donut priorités (mes ouverts) --}}
        <div class="card">
            <div class="card-header"><h3 class="card-title">Priorités (mes incidents ouverts)</h3></div>
            <div class="card-body">
                <canvas id="chartPrioritiesMe"></canvas>
            </div>
        </div>

        {{-- Bar mensuelle (mes incidents créés) --}}
        <div class="card">
            <div class="card-header"><h3 class="card-title">Incidents créés par mois (12 mois)</h3></div>
            <div class="card-body">
                <canvas id="chartMonthlyMe"></canvas>
            </div>
        </div>
    </div>
</div>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function(){
    // Donut
    new Chart(document.getElementById('chartPrioritiesMe').getContext('2d'), {
        type: 'doughnut',
        data: { labels: @json($priorityLabels), datasets:[{ data: @json($priorityData), backgroundColor:['#ef4444','#f59e0b','#3b82f6','#6b7280'] }] },
        options:{ responsive:true, plugins:{ legend:{ position:'bottom' } } }
    });

    // Bar mensuelle
    new Chart(document.getElementById('chartMonthlyMe').getContext('2d'), {
        type: 'bar',
        data: { labels: @json($monthlyLabels), datasets:[{ label:'Incidents créés', data: @json($monthlyData), backgroundColor:'#2563eb', borderRadius:6, maxBarThickness:36 }] },
        options:{ responsive:true, plugins:{ legend:{ display:false } }, scales:{ x:{ grid:{ display:false } }, y:{ beginAtZero:true, ticks:{ precision:0 } } } }
    });
})();
</script>
@endsection
