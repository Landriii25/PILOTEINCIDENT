@extends('adminlte::page')

@section('title', 'Galerie des applications')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">Galerie des applications</h1>
        @can('create', App\Models\Application::class)
            <a href="{{ route('applications.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i> Nouvelle application
            </a>
        @endcan
    </div>
@endsection

@section('content')
    @if(session('success'))
        <div class="alert alert-success mb-3">{{ session('success') }}</div>
    @endif

    @if($applications->count() === 0)
        <div class="text-center text-muted py-5">
            <i class="far fa-folder-open fa-2x mb-3"></i>
            <div>Aucune application pour le moment.</div>
        </div>
    @else
        <div class="row">
            @foreach($applications as $app)
                @php
                    $badge = [
                        'Actif'          => 'success',
                        'En maintenance' => 'warning',
                        'Retirée'        => 'secondary',
                    ][$app->statut] ?? 'light';

                    $thumb = $app->thumb_url; // ← accessor du modèle
                @endphp

                <div class="col-sm-6 col-md-4 col-lg-3 mb-4">
                    <div class="app-card h-100 position-relative">
                        <div class="app-card-media">
                            @if($thumb)
                                <img src="{{ $thumb }}" alt="{{ $app->nom }}">
                            @else
                                <div class="d-flex flex-column align-items-center text-muted">
                                    <i class="fas fa-th fa-3x mb-2"></i>
                                    <small>Pas d’image</small>
                                </div>
                            @endif
                        </div>

                        <div class="p-3 text-center">
                            <h5 class="mb-2 text-truncate" title="{{ $app->nom }}">{{ $app->nom }}</h5>
                            <span class="badge badge-{{ $badge }}">{{ $app->statut ?? '—' }}</span>

                            @if($app->service)
                                <div class="small text-muted mt-2">
                                    <i class="fas fa-sitemap mr-1"></i>{{ $app->service->nom }}
                                </div>
                            @endif

                            @if(!empty($app->description))
                                <div class="text-muted small mt-2" title="{{ $app->description }}">
                                    {{ \Illuminate\Support\Str::limit($app->description, 80) }}
                                </div>
                            @endif
                        </div>

                        {{-- Overlay d’actions --}}
                        <div class="app-overlay d-flex flex-column justify-content-center align-items-center text-center px-2">
                            <div class="mb-2">
                                <a href="{{ route('applications.show', $app) }}" class="btn btn-light btn-sm mr-2">
                                    <i class="far fa-eye mr-1"></i> Voir
                                </a>
                                @can('update', $app)
                                    <a href="{{ route('applications.edit', $app) }}" class="btn btn-primary btn-sm">
                                        <i class="far fa-edit mr-1"></i> Éditer
                                    </a>
                                @endcan
                            </div>
                            @can('delete', $app)
                                <form action="{{ route('applications.destroy', $app) }}" method="POST"
                                      onsubmit="return confirm('Supprimer cette application ?');">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm">
                                        <i class="far fa-trash-alt mr-1"></i> Supprimer
                                    </button>
                                </form>
                            @endcan
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-center">
            {{ $applications->links('pagination::bootstrap-4') }}
        </div>
    @endif
@endsection

@push('css')
<style>
    .app-card{ border-radius:16px; overflow:hidden; background:#fff;
        box-shadow:0 6px 18px rgba(0,0,0,.06); transition:transform .18s ease, box-shadow .18s ease; }
    .app-card:hover{ transform:translateY(-6px); box-shadow:0 16px 40px rgba(31,78,255,.18); }
    .app-card-media{ background:linear-gradient(135deg,#f5f7fb,#eef3ff);
        height:160px; display:flex; align-items:center; justify-content:center; }
    .app-card-media img{ width:96px; height:96px; object-fit:cover; border-radius:12px;
        box-shadow:0 6px 16px rgba(0,0,0,.12); transition:transform .25s ease; }
    .app-card:hover .app-card-media img{ transform:scale(1.05); }
    .app-overlay{ position:absolute; inset:0; background:linear-gradient(180deg, rgba(2,0,36,0) 20%, rgba(0,0,0,.35) 100%);
        opacity:0; transition:opacity .18s ease; padding-bottom:10px; }
    .app-card:hover .app-overlay{ opacity:1; }
</style>
@endpush
