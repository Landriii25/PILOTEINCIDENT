@extends('adminlte::page')

@section('title','Applications')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between">
        <h1 class="m-0">Applications</h1>

        @can('create', App\Models\Application::class)
            <a href="{{ route('applications.create') }}" class="btn btn-primary">
                <i class="fas fa-plus mr-1"></i> Nouvelle application
            </a>
        @endcan
    </div>
@stop

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead>
                        <tr>
                            <th style="width:68px">Logo</th>
                            <th>Nom</th>
                            <th>Service</th>
                            <th>Statut</th>
                            <th class="text-right" style="width:80px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applications as $app)
                            <tr>
                                <td>
                                    @php
                                        $thumb = $app->thumb_url ?? $app->logo_url ?? null;
                                    @endphp
                                    @if($thumb)
                                        <img src="{{ $thumb }}" alt="{{ $app->nom }}" class="app-logo rounded">
                                    @else
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($app->nom) }}&size=96&background=0D8ABC&color=fff"
                                             alt="{{ $app->nom }}" class="app-logo rounded">
                                    @endif
                                </td>
                                <td class="font-weight-600">
                                    <a href="{{ route('applications.show', $app) }}">{{ $app->nom }}</a>
                                    @if($app->description)
                                        <div class="text-muted small">{{ \Illuminate\Support\Str::limit($app->description, 80) }}</div>
                                    @endif
                                </td>
                                <td>{{ optional($app->service)->nom ?? '—' }}</td>
                                <td>
                                    @php
                                        $map = [
                                            'Actif' => 'success',
                                            'En maintenance' => 'warning',
                                            'Retirée' => 'secondary',
                                        ];
                                        $cls = $map[$app->statut] ?? 'light';
                                    @endphp
                                    <span class="badge badge-{{ $cls }}">{{ $app->statut }}</span>
                                </td>
                                <td class="text-right pr-3">
                                    <div class="btn-group dropleft">
                                        <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"
                                                title="Actions">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-right">
                                            <a class="dropdown-item" href="{{ route('applications.show',$app) }}">
                                                <i class="far fa-eye mr-2"></i> Voir
                                            </a>

                                            @can('update', $app)
                                                <a class="dropdown-item" href="{{ route('applications.edit',$app) }}">
                                                    <i class="far fa-edit mr-2"></i> Éditer
                                                </a>
                                            @endcan

                                            @can('delete', $app)
                                                <div class="dropdown-divider"></div>
                                                <form action="{{ route('applications.destroy',$app) }}" method="POST"
                                                      onsubmit="return confirm('Supprimer cette application ?');">
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
                                <td colspan="5" class="text-center text-muted p-4">
                                    <i class="far fa-folder-open fa-lg d-block mb-2"></i>
                                    Aucune application.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if(method_exists($applications,'links'))
            <div class="card-footer">
                {{ $applications->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
@stop

@push('css')
<style>
    .app-logo{ width:48px; height:48px; object-fit:cover; }
    .font-weight-600{ font-weight:600; }
</style>
@endpush
