@extends('adminlte::page')

@section('title', $article->title . ' — Base de connaissances')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="mb-0">{{ $article->title }}</h1>
        <div>
            @can('kb.update', $article)
                <a href="{{ route('kb.edit', $article) }}" class="btn btn-warning">
                    <i class="fas fa-edit"></i> Éditer
                </a>
            @endcan
            <a href="{{ route('kb.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-lg-9">
        <div class="card">
            <div class="card-body">
                <div class="mb-3">
                    @if($article->is_published)
                        <span class="badge badge-success">Publié</span>
                    @else
                        <span class="badge badge-secondary">Brouillon</span>
                    @endif

                    <span class="ml-2 text-muted">
                        Catégorie :
                        <strong>{{ optional($article->category)->nom ?? '—' }}</strong>
                    </span>

                    <span class="ml-3 text-muted">
                        Vues : <strong>{{ $article->views }}</strong>
                    </span>
                </div>

                @if($article->summary)
                    <p class="lead">{{ $article->summary }}</p>
                    <hr>
                @endif

                {{-- Contenu : si c'est HTML, il s'affiche ; sinon on fallback en texte --}}
                @php
                    $isHtml = $article->content && $article->content !== strip_tags($article->content);
                @endphp

                <div class="kb-content">
                    @if($isHtml)
                        {!! $article->content !!}
                    @else
                        {!! nl2br(e($article->content)) !!}
                    @endif
                </div>

                @if(!empty($article->tags))
                    @php
                        $tags = is_array($article->tags) ? $article->tags : (json_decode($article->tags, true) ?: []);
                    @endphp
                    @if(count($tags))
                        <hr>
                        <div>
                            <i class="fas fa-tags text-muted mr-2"></i>
                            @foreach($tags as $t)
                                <span class="badge badge-pill badge-light border">{{ $t }}</span>
                            @endforeach
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-3">
        <div class="card">
            <div class="card-header"><strong>Métadonnées</strong></div>
            <div class="card-body">
                <div class="text-muted small">Créé le</div>
                <div class="mb-2">{{ optional($article->created_at)->format('d/m/Y H:i') }}</div>

                <div class="text-muted small">Mis à jour</div>
                <div class="mb-2">{{ optional($article->updated_at)->format('d/m/Y H:i') }}</div>

                <div class="text-muted small">Slug</div>
                <div class="mb-2"><code>{{ $article->slug }}</code></div>
            </div>
        </div>
    </div>
</div>
@stop
