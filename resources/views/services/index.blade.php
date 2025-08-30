{{-- resources/views/services/index.blade.php --}}
@extends('adminlte::page')

@section('title','Services')

@section('content_header')
<div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
    <h1 class="m-0">Services</h1>

    @can('create', App\Models\Service::class)
        <a href="{{ route('services.create') }}" class="btn btn-primary mt-2 mt-sm-0">
            <i class="fas fa-plus mr-1"></i> Nouveau service
        </a>
    @endcan
</div>
@endsection

@section('content')
{{-- Filtres --}}
<div class="card mb-3">
  <div class="card-body">
    <form method="GET">
      <div class="form-row">
        <div class="form-group col-12 col-md-6 col-lg-4">
          <label class="sr-only" for="q">Recherche</label>
          <div class="input-group">
            <div class="input-group-prepend d-none d-sm-flex">
              <span class="input-group-text"><i class="fas fa-search"></i></span>
            </div>
            <input id="q" type="text" name="q" value="{{ request('q') }}"
                   class="form-control" placeholder="Nom / chef de service…">
          </div>
        </div>

        <div class="form-group col-12 col-md-auto d-flex align-items-end">
          <button class="btn btn-outline-primary mr-2 mb-2 mb-md-0">
            <i class="fas fa-filter mr-1"></i> Filtrer
          </button>

          @if(request()->hasAny(['q']))
            <a href="{{ route('services.index') }}" class="btn btn-link mb-2 mb-md-0">Réinitialiser</a>
          @endif
        </div>
      </div>
    </form>
  </div>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table table-hover table-striped mb-0">
      <thead class="thead-light">
      <tr>
        <th>Service</th>
        <th class="d-none d-sm-table-cell">Chef</th>
        <th class="text-center d-none d-md-table-cell">Applications</th>
        <th class="text-center d-none d-md-table-cell">Techniciens</th>
        <th class="d-none d-lg-table-cell">Créé le</th>
        <th class="text-right text-nowrap">Actions</th>
      </tr>
      </thead>
      <tbody>
      @forelse($services as $service)
        <tr>
          <td style="min-width:220px;">
            <div class="font-weight-bold text-truncate" title="{{ $service->nom }}">{{ $service->nom }}</div>
            @if($service->description)
              <div class="text-muted small text-truncate-2" title="{{ $service->description }}">
                {{ $service->description }}
              </div>
            @endif

            {{-- Infos compactes visibles seulement en mobile --}}
            <div class="d-sm-none mt-1 text-muted small">
              @php $chef = $service->chef; @endphp
              <span class="mr-2"><i class="far fa-user mr-1"></i>{{ $chef?->name ?? '—' }}</span>
              <span class="mr-2"><i class="fas fa-th-large mr-1"></i>{{ $service->applications_count ?? ($service->applications->count() ?? 0) }}</span>
              <span><i class="fas fa-tools mr-1"></i>{{ $service->techniciens_count ?? ($service->techniciens->count() ?? 0) }}</span>
            </div>
          </td>

          <td class="d-none d-sm-table-cell" style="min-width:180px;">
            @php
              $chef = $service->chef;
              $label = $chef?->name ?? '—';
              $avatar = $chef
                  ? 'https://ui-avatars.com/api/?name='.urlencode($chef->name).'&size=64&background=0D8ABC&color=fff'
                  : 'https://ui-avatars.com/api/?name=--&size=64&background=999&color=fff';
            @endphp
            <div class="d-flex align-items-center text-truncate">
              <img src="{{ $avatar }}" alt="Chef" class="rounded-circle mr-2" width="28" height="28" loading="lazy">
              <span class="text-truncate" title="{{ $label }}">{{ $label }}</span>
            </div>
          </td>

          <td class="text-center d-none d-md-table-cell">
            {{ $service->applications_count ?? ($service->applications->count() ?? 0) }}
          </td>

          <td class="text-center d-none d-md-table-cell">
            {{ $service->techniciens_count ?? ($service->techniciens->count() ?? 0) }}
          </td>

          <td class="d-none d-lg-table-cell">
            {{ optional($service->created_at)->format('d/m/Y') }}
          </td>

          {{-- Actions dropdown --}}
          <td class="text-right text-nowrap">
            <div class="dropdown position-static">
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
          <td colspan="6" class="text-center text-muted p-4">Aucun service.</td>
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
  /* Utilitaires */
  .text-truncate-2{
    display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;
  }

  /* Empêche le dropdown d'être rogné dans .table-responsive (solution Bootstrap) */
  .table-responsive .dropdown-menu{
    position: absolute; /* par défaut via .dropdown-menu; gardé ici à titre explicatif */
    will-change: transform;
  }
</style>
@endpush
