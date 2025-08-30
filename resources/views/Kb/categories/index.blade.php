{{-- resources/views/kb/categories/index.blade.php --}}
@extends('adminlte::page')

@section('title', 'Catégories — Base de connaissances')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">Catégories</h1>
        @can('kb.categories.manage')
            <a href="{{ route('kb.categories.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i> Nouvelle catégorie
            </a>
        @endcan
    </div>
@endsection

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle categories-list">
                    <thead class="thead-light">
                        <tr>
                            <th style="width:280px">Nom</th>
                            <th>Description</th>
                            <th style="width:120px">Articles</th>
                            <th class="text-right" style="width:100px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $c)
                            <tr>
                                <td class="font-weight-600">
                                    {{-- lien vers la page catégorie (liste des articles de cette catégorie) --}}
                                    <a href="{{ route('kb.categories.show', $c) }}" class="text-body">
                                        {{ $c->nom ?? '—' }}
                                    </a>
                                </td>

                                <td class="text-muted">
                                    {{ $c->description ?: '—' }}
                                </td>

                                <td>
                                    <span class="badge badge-light">
                                        {{ $c->articles_count ?? 0 }}
                                    </span>
                                </td>

                                <td class="text-right pr-2">
                                    <div class="btn-group dropleft">
                                        <button type="button"
                                            class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                            title="Actions">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>

                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item" href="{{ route('kb.categories.show', $c) }}">
                                                <i class="far fa-eye mr-2"></i> Voir
                                            </a>

                                            @can('kb.categories.manage')
                                                <a class="dropdown-item" href="{{ route('kb.categories.edit', $c) }}">
                                                    <i class="far fa-edit mr-2"></i> Éditer
                                                </a>
                                                <div class="dropdown-divider"></div>
                                                <form action="{{ route('kb.categories.destroy', $c) }}" method="POST"
                                                      onsubmit="return confirm('Supprimer cette catégorie ?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="far fa-trash-alt mr-2"></i> Supprimer
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted p-4">
                                    <i class="far fa-folder-open fa-lg d-block mb-2"></i>
                                    Aucune catégorie pour le moment.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('css')
<style>
    /* Évite la coupure du nom sur 2 lignes et harmonise */
    table.categories-list td:first-child,
    table.categories-list th:first-child { white-space: nowrap; }

    .font-weight-600 { font-weight: 600; }
</style>
@endpush
