{{-- resources/views/kb/categories/show.blade.php --}}
@extends('adminlte::page')

@section('title', 'Catégorie : '.$category->nom)

@section('content_header')
  <div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="m-0">Catégorie : {{ $category->nom }}</h1>
        @if($category->description)
            <small class="text-muted d-block mt-1">{{ $category->description }}</small>
        @endif
    </div>

    <div class="btn-group dropleft">
        <button type="button" class="btn btn-outline-secondary dropdown-toggle"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Actions">
            <i class="fas fa-ellipsis-v"></i>
        </button>
        <div class="dropdown-menu dropdown-menu-right">
            <a class="dropdown-item" href="{{ route('kb.categories') }}">
                <i class="fas fa-list mr-2"></i> Toutes les catégories
            </a>
            @can('kb.categories.manage')
                <a class="dropdown-item" href="{{ route('kb.categories.edit', $category) }}">
                    <i class="far fa-edit mr-2"></i> Éditer
                </a>
                <div class="dropdown-divider"></div>
                <form action="{{ route('kb.categories.destroy', $category) }}" method="POST"
                      onsubmit="return confirm('Supprimer cette catégorie ?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="dropdown-item text-danger">
                        <i class="far fa-trash-alt mr-2"></i> Supprimer
                    </button>
                </form>
            @endcan
        </div>
    </div>
  </div>
@endsection

@section('content')
  {{-- Résumé chiffré --}}
  <div class="row">
      <div class="col-md-4">
          <div class="small-box bg-info">
              <div class="inner">
                  <h3>{{ $articles->total() ?? ($articles->count() ?? 0) }}</h3>
                  <p>Article(s) dans cette catégorie</p>
              </div>
              <div class="icon"><i class="far fa-file-alt"></i></div>
          </div>
      </div>
  </div>

  {{-- Liste des articles de la catégorie --}}
  @if(($articles->count() ?? 0) === 0)
    <div class="text-center text-muted py-5">
      <i class="far fa-file-alt fa-2x mb-3"></i>
      <div>Aucun article dans cette catégorie.</div>
    </div>
  @else
    <div class="card">
      <div class="card-header"><strong>Articles</strong></div>

      <div class="list-group list-group-flush">
        @foreach($articles as $a)
          <a href="{{ route('kb.show', $a) }}"
             class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
            <div class="text-truncate" style="max-width: calc(100% - 24px);">
              <div class="font-weight-600 text-truncate">{{ $a->title }}</div>
              <div class="small text-muted text-truncate">
                @if($a->summary) {{ $a->summary }} @else — @endif
              </div>
              <div class="small text-muted">
                Publié {{ $a->created_at?->diffForHumans() }} • Mis à jour {{ $a->updated_at?->diffForHumans() }}
              </div>
            </div>
            <i class="fas fa-chevron-right text-muted"></i>
          </a>
        @endforeach
      </div>

      @if(method_exists($articles,'links'))
        <div class="card-footer">
            {{ $articles->links('pagination::bootstrap-4') }}
        </div>
      @endif
    </div>
  @endif
@endsection

@push('css')
<style>
    .font-weight-600{ font-weight:600; }
    /* Évite la casse sur 2 lignes du nom dans des tableaux, si besoin */
    table.categories-list td:first-child,
    table.categories-list th:first-child { white-space: nowrap; }
</style>
@endpush
