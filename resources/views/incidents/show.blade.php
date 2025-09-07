@extends('adminlte::page')

@section('title', $incident->code)

@section('content_header')
<div class="d-flex align-items-center justify-content-between">
    <div>
        <h1 class="mb-0">{{ $incident->titre }}</h1>
        <div class="text-muted small">
            Code : <span class="badge badge-dark">{{ $incident->code }}</span>
            • Créé par {{ $incident->user?->name ?? '—' }}
            • {{ $incident->created_at?->diffForHumans() }}
        </div>
    </div>

    <div class="text-right">
        @if($incident->statut === 'Résolu' && (auth()->id() === $incident->user_id || auth()->user()->hasRole('admin')))
            <form action="{{ route('incidents.close', $incident) }}" method="POST" class="d-inline">
                @csrf
                @method('PUT')
                <button class="btn btn-success"><i class="fas fa-check mr-1"></i> Clore</button>
            </form>
            <form action="{{ route('incidents.reopen_to_tech', $incident) }}" method="POST" class="d-inline">
                @csrf
                @method('PUT')
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
    {{-- COLONNE DE GAUCHE : DÉTAILS --}}
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">Détails</h3>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-4">Application</dt>
                    <dd class="col-sm-8">{{ $incident->application?->nom ?? '—' }}</dd>

                    <dt class="col-sm-4">Priorité</dt>
                    <dd class="col-sm-8">
                        <span class="badge badge-{{ ['Critique'=>'danger','Haute'=>'warning','Moyenne'=>'info','Basse'=>'secondary'][$incident->priorite] ?? 'secondary' }}">
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
                    <dd class="col-sm-8">{{ $incident->technicien?->name ?? '—' }}</dd>

                    <dt class="col-sm-4">Échéance (SLA)</dt>
                    <dd class="col-sm-8">
                        @if($incident->due_at)
                            <span class="badge badge-{{ $incident->due_at->isPast() ?'danger':'secondary' }}">
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

                @if($incident->attachments->isNotEmpty())
                    <hr>
                    <strong>Pièces jointes</strong>
                    <ul class="list-unstyled mt-2">
                        @foreach($incident->attachments as $attachment)
                            <li>
                                <a href="{{ Storage::url($attachment->file_path ?? $attachment->chemin_fichier) }}" target="_blank">
                                    <i class="fas fa-paperclip mr-1"></i>
                                    {{ $attachment->original_name ?? $attachment->nom_original }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    {{-- COLONNE DE DROITE : COMMENTAIRES --}}
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title font-weight-bold">Commentaires</h3>
            </div>
            <div class="card-body" style="max-height: 420px; overflow-y: auto;">
                @forelse($commentaires as $c)
                    <div class="media mb-3">
                        <div class="mr-3 bg-secondary text-white rounded-circle d-inline-flex align-items-center justify-content-center"
                             style="width:36px; height:36px; font-weight:700">
                            {{ strtoupper(mb_substr($c->user?->name ?? '?', 0, 1)) }}
                        </div>
                        <div class="media-body">
                            <div class="small text-muted">
                                <strong class="text-dark">{{ $c->user?->name ?? 'Utilisateur' }}</strong>
                                • {{ $c->created_at->diffForHumans() }}
                            </div>
                            <div>{!! nl2br(e($c->contenu)) !!}</div>
                        </div>
                    </div>
                @empty
                    <div class="text-muted">Aucun commentaire pour l’instant.</div>
                @endforelse
            </div>
            <div class="card-footer">
                {{-- LE FORMULAIRE CORRECT EST ICI --}}
                <form method="POST" action="{{ route('incidents.comments.store', $incident) }}">
                    @csrf
                    <div class="form-group mb-0">
                        <textarea name="commentaire"
                                  class="form-control @error('commentaire') is-invalid @enderror"
                                  rows="3"
                                  placeholder="Ajouter un commentaire…" required></textarea>
                        @error('commentaire')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="text-right mt-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane mr-1"></i> Envoyer
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
