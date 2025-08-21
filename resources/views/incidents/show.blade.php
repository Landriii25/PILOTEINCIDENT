@extends('adminlte::page')

@section('title', $incident->code)

@section('content_header')
<div class="d-flex align-items-center justify-content-between">
    <div>
        <h1 class="mb-0">{{ $incident->titre }}</h1>
        <div class="text-muted small">
            Code : <span class="badge badge-dark">{{ $incident->code }}</span>
            • Créé par {{ $incident->user->name ?? '—' }}
            • {{ $incident->created_at?->diffForHumans() }}
        </div>
    </div>

    <div class="text-right">
        {{-- === Rapport d’incident ========================================= --}}
        @if(!$incident->report)
            {{-- Aucun rapport encore : proposer de le remplir --}}
            <a href="{{ route('reports.create_for_incident', $incident) }}"
               class="btn btn-success mr-1">
                <i class="fas fa-file-signature mr-1"></i> Renseigner le rapport
            </a>
        @else
            {{-- Rapport déjà existant : liens Voir / Éditer --}}
            <a href="{{ route('reports.show', $incident->report) }}"
               class="btn btn-outline-primary mr-1">
                <i class="fas fa-file-alt mr-1"></i> Voir le rapport
            </a>
            <a href="{{ route('reports.edit', $incident->report) }}"
               class="btn btn-secondary mr-1">
                <i class="fas fa-edit mr-1"></i> Éditer le rapport
            </a>
        @endif
        {{-- ================================================================= --}}

        @if($incident->statut === 'Résolu' && (auth()->id()===$incident->user_id || auth()->user()->hasRole('admin')))
            <form action="{{ route('incidents.close', $incident) }}" method="POST" class="d-inline">
                @csrf @method('PUT')
                <button class="btn btn-success"><i class="fas fa-check mr-1"></i> Clore</button>
            </form>
            <form action="{{ route('incidents.reopen_to_tech', $incident) }}" method="POST" class="d-inline">
                @csrf @method('PUT')
                <button class="btn btn-warning"><i class="fas fa-redo mr-1"></i> Ré-ouvrir</button>
            </form>
        @endif

        <a href="{{ route('incidents.edit', $incident) }}" class="btn btn-primary ml-1">
            <i class="fas fa-edit mr-1"></i> Éditer
        </a>
        <a href="{{ route('incidents.index') }}" class="btn btn-outline-secondary ml-1">Retour</a>
    </div>
</div>
@endsection

@section('content')
<div class="row">
  <div class="col-lg-7">
    <div class="card">
      <div class="card-header">
        <strong>Détails</strong>
      </div>
      <div class="card-body">
        <dl class="row mb-0">
            <dt class="col-sm-4">Application</dt>
            <dd class="col-sm-8">{{ $incident->application->nom ?? '—' }}</dd>

            <dt class="col-sm-4">Priorité</dt>
            <dd class="col-sm-8">
                <span class="badge badge-{{ [
                    'Critique'=>'danger','Haute'=>'warning','Moyenne'=>'info','Basse'=>'secondary'
                ][$incident->priorite] ?? 'secondary' }}">
                    {{ $incident->priorite }}
                </span>
            </dd>

            <dt class="col-sm-4">Statut</dt>
            <dd class="col-sm-8">
                <span class="badge badge-{{ $incident->statut==='Ouvert'?'primary':($incident->statut==='En cours'?'info':($incident->statut==='Résolu'?'success':'secondary')) }}">
                    {{ $incident->statut }}
                </span>
            </dd>

            <dt class="col-sm-4">Assigné à</dt>
            <dd class="col-sm-8">{{ $incident->technicien->name ?? '—' }}</dd>

            <dt class="col-sm-4">Échéance (SLA)</dt>
            <dd class="col-sm-8">
                @if($incident->due_at)
                    @php $late = $incident->is_late ?? false; @endphp
                    <span class="badge badge-{{ $late?'danger':'secondary' }}">
                        {{ $incident->due_at->diffForHumans() }}
                    </span>
                @else
                    —
                @endif
            </dd>
        </dl>

        <hr>
        <div>
            <strong>Description</strong>
            <div class="mt-2">{!! nl2br(e($incident->description)) ?: '<span class="text-muted">Aucune description.</span>' !!}</div>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="card">
      <div class="card-header"><strong>Commentaires</strong></div>
      <div class="card-body" style="max-height:420px;overflow:auto">
        @forelse($commentaires as $c)
          <div class="media mb-3">
            <div class="mr-3 bg-secondary text-white rounded-circle d-inline-flex align-items-center justify-content-center"
                 style="width:36px;height:36px;font-weight:700">
                 {{ strtoupper(mb_substr($c->user->name ?? '?',0,1)) }}
            </div>
            <div class="media-body">
              <div class="small text-muted">
                {{ $c->user->name ?? 'Utilisateur' }} • {{ $c->created_at->diffForHumans() }}
              </div>
              <div>{!! nl2br(e($c->contenu)) !!}</div>
            </div>
          </div>
        @empty
          <div class="text-muted">Aucun commentaire pour l’instant.</div>
        @endforelse
      </div>
      <div class="card-footer">
        <form method="POST" action="{{ route('incidents.comments.store', $incident) }}">
          @csrf
          <div class="input-group">
            <input type="text" name="commentaire" class="form-control" placeholder="Ajouter un commentaire…">
            <div class="input-group-append">
              <button class="btn btn-primary"><i class="fas fa-paper-plane"></i></button>
            </div>
          </div>
        </form>
      </div>
    </div>

    {{-- Petit encart rappel Rapport --}}
    <div class="card mt-3">
      <div class="card-body d-flex align-items-center">
        <i class="fas fa-file-alt fa-lg text-muted mr-3"></i>
        <div class="flex-grow-1">
            <div class="font-weight-bold mb-1">Rapport d’incident</div>
            @if($incident->report)
                <div class="text-muted small mb-2">Réf : {{ $incident->report->ref }} — durée :
                    @if(!is_null($incident->report->duration_minutes))
                        {{ floor($incident->report->duration_minutes/60) }}h {{ $incident->report->duration_minutes % 60 }}m
                    @else
                        —
                    @endif
                </div>
                <a href="{{ route('reports.show',$incident->report) }}" class="btn btn-outline-primary btn-sm mr-2">
                    <i class="fas fa-eye mr-1"></i> Voir
                </a>
                <a href="{{ route('reports.edit',$incident->report) }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-edit mr-1"></i> Éditer
                </a>
            @else
                <div class="text-muted small mb-2">Aucun rapport pour l’instant.</div>
                <a href="{{ route('incidents.report.create',$incident) }}" class="btn btn-success btn-sm">
                    <i class="fas fa-file-medical-alt mr-1"></i> Remplir le rapport
                </a>
            @endif
        </div>
      </div>
    </div>

  </div>
</div>
@endsection
