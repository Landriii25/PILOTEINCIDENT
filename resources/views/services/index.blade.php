{{-- resources/views/services/index.blade.php --}}
@extends('adminlte::page')

@section('title','Services')

@section('content_header')
<div class="d-flex align-items-center justify-content-between">
    <h1 class="m-0">Services</h1>

    @can('create', App\Models\Service::class)
        <a href="{{ route('services.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Nouveau service
        </a>
    @endcan
</div>
@endsection

@section('content')
{{-- Filtres (optionnels) --}}
<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="form-inline">
            <div class="form-group mr-2 mb-2">
                <label class="mr-2">Recherche</label>
                <input type="text" name="q" value="{{ request('q') }}" class="form-control"
                       placeholder="Nom / chef de service…">
            </div>
            <button class="btn btn-outline-primary mb-2">
                <i class="fas fa-search mr-1"></i> Filtrer
            </button>
            @if(request()->hasAny(['q']))
                <a href="{{ route('services.index') }}" class="btn btn-link mb-2">Réinitialiser</a>
            @endif
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead class="thead-light">
            <tr>
                <th>Service</th>
                <th>Chef</th>
                <th class="text-center">Applications</th>
                <th class="text-center">Techniciens</th>
                <th>Créé le</th>
                <th class="text-right" style="width:72px;">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse($services as $service)
                <tr>
                    <td>
                        <div class="font-weight-bold">{{ $service->nom }}</div>
                        @if($service->description)
                            <div class="text-muted small">{{ $service->description }}</div>
                        @endif
                    </td>

                    <td>
                        @php
                            $chef = $service->chef;
                            $label = $chef?->name ?? '—';
                            $avatar = $chef
                                ? 'https://ui-avatars.com/api/?name='.urlencode($chef->name).'&size=64&background=0D8ABC&color=fff'
                                : 'https://ui-avatars.com/api/?name=--&size=64&background=999&color=fff';
                        @endphp
                        <div class="d-flex align-items-center">
                            <img src="{{ $avatar }}" alt="Chef" class="rounded-circle mr-2" width="28" height="28">
                            <span>{{ $label }}</span>
                        </div>
                    </td>

                    <td class="text-center">
                        {{ $service->applications_count ?? ($service->applications->count() ?? 0) }}
                    </td>

                    <td class="text-center">
                        {{ $service->techniciens_count ?? ($service->techniciens->count() ?? 0) }}
                    </td>

                    <td>{{ optional($service->created_at)->format('d/m/Y') }}</td>

                    {{-- Actions dropdown --}}
                    <td class="text-right">
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm" data-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>

                            <div class="dropdown-menu dropdown-menu-right">
                                <a href="{{ route('services.show', $service) }}" class="dropdown-item">
                                    <i class="far fa-eye mr-2"></i> Voir
                                </a>

                                @can('update', $service)
                                    <a href="{{ route('services.edit', $service) }}" class="dropdown-item">
                                        <i class="far fa-edit mr-2"></i> Éditer
                                    </a>
                                @endcan

                                @can('delete', $service)
                                    <div class="dropdown-divider"></div>
                                    <form action="{{ route('services.destroy', $service) }}" method="POST" class="m-0 p-0"
                                          onsubmit="return confirm('Supprimer ce service ?');">
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
                    <td colspan="6" class="text-center text-muted p-4">
                        Aucun service.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($services,'links'))
        <div class="card-footer">
            {{ $services->appends(request()->query())->links('pagination::bootstrap-4') }}
        </div>
    @endif
</div>
@endsection

@push('css')
<style>
    /* Pour éviter que le dropdown soit masqué dans un conteneur scrollable */
    .table-responsive{ overflow: visible !important; }
</style>
@endpush
