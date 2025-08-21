@extends('adminlte::page')

@section('title','Base de connaissances')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between">
        <h1 class="m-0">Base de connaissances</h1>
        <div>
            <x-can perm="kb.create">
                <x-action.link href="{{ route('kb.create') }}" icon="fas fa-plus">Nouvel article</x-action.link>
            </x-can>
            <x-can perm="kb.categories.manage">
                <x-action.link href="{{ route('kb.categories') }}" class="btn-secondary" icon="fas fa-folder-open">
                    Catégories
                </x-action.link>
            </x-can>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header"><strong>Derniers articles</strong></div>
            <div class="list-group list-group-flush">
                @forelse($articles as $a)
                    <a href="{{ route('kb.show',$a) }}" class="list-group-item list-group-item-action">
                        <div class="d-flex w-100 justify-content-between">
                            <h5 class="mb-1">{{ $a->title }}</h5>
                            <small class="text-muted">{{ $a->updated_at->diffForHumans() }}</small>
                        </div>
                        <p class="mb-1 text-truncate">{{ $a->summary }}</p>
                        <small class="text-muted">Catégorie : {{ $a->category->name ?? '—' }}</small>
                    </a>
                @empty
                    <div class="p-3 text-muted">Aucun article.</div>
                @endforelse
            </div>
            @if(method_exists($articles,'links'))
                <div class="card-footer">
                    {{ $articles->links('pagination::bootstrap-4') }}
                </div>
            @endif
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><strong>Catégories</strong></div>
            <ul class="list-group list-group-flush">
                @forelse($categories as $c)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <a href="{{ route('kb.categories.show', $c) }}">{{ $c->name }}</a>
                        <span class="badge badge-light">{{ $c->articles_count ?? 0 }}</span>
                    </li>
                @empty
                    <li class="list-group-item text-muted">Aucune catégorie.</li>
                @endforelse
            </ul>
        </div>
    </div>
</div>
@stop
