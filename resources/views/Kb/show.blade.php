@extends('adminlte::page')

@section('title', 'Catégorie : '.$category->nom)

@section('content_header')
  <div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0">
      Catégorie : {{ $category->nom }}
      @if($category->description)
        <small class="text-muted d-block mt-1">{{ $category->description }}</small>
      @endif
    </h1>

    @can('kb.create')
      <a href="{{ route('kb.create', ['category_id' => $category->id]) }}" class="btn btn-primary">
        <i class="fas fa-plus mr-1"></i> Nouvel article
      </a>
    @endcan
  </div>
@endsection

@section('content')
  @if($articles->isEmpty())
    <div class="text-center text-muted py-5">
      <i class="far fa-file-alt fa-2x mb-3"></i>
      <div>Aucun article dans cette catégorie.</div>
    </div>
  @else
    <div class="card">
      <div class="list-group list-group-flush">
        @foreach($articles as $a)
          <a href="{{ route('kb.show', $a) }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
            <div>
              <div class="font-weight-bold">{{ $a->title }}</div>
              <div class="small text-muted">
                Publié {{ $a->created_at?->diffForHumans() }} •
                @if($a->views) {{ $a->views }} vue(s) @else 0 vue @endif
              </div>
            </div>
            <i class="fas fa-chevron-right text-muted"></i>
          </a>
        @endforeach
      </div>

      <div class="card-footer">
        {{ $articles->links('pagination::bootstrap-4') }}
      </div>
    </div>
  @endif
@endsection
