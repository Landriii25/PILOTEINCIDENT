{{-- resources/views/roles/index.blade.php --}}
@extends('adminlte::page')

@section('title', 'Rôles & permissions')

@section('content_header')
  <div class="d-flex justify-content-between align-items-center">
    <h1 class="m-0">Rôles & permissions</h1>
    @can('roles.create')
      <a href="{{ route('roles.create') }}" class="btn btn-primary">
        <i class="fas fa-plus mr-1"></i> Nouveau rôle
      </a>
    @endcan
  </div>
@endsection

@section('content')
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  <div class="card">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead class="thead-light">
          <tr>
            <th style="width:160px">Nom</th>
            <th>Permissions</th>
            <th class="text-right" style="width:100px">Actions</th>
          </tr>
          </thead>
          <tbody>
          @forelse($roles as $role)
            <tr>
              <td class="font-weight-600">{{ $role->name }}</td>
              <td>
                @php
                  $perms = $role->permissions->pluck('name')->sort()->values();
                @endphp
                @if($perms->isEmpty())
                  <span class="text-muted">Aucune</span>
                @else
                  @foreach($perms as $p)
                    <span class="badge badge-light border mr-1 mb-1">{{ $p }}</span>
                  @endforeach
                @endif
              </td>

              {{-- Actions --}}
              <td class="text-right text-nowrap pr-2">
                @canany(['roles.update', 'roles.delete'])
                  <div class="dropdown position-static">
                    <button class="btn btn-sm btn-outline-secondary"
                            data-toggle="dropdown"
                            data-display="static"
                            aria-haspopup="true" aria-expanded="false"
                            title="Actions">
                      <i class="fas fa-ellipsis-v"></i>
                    </button>

                    <div class="dropdown-menu dropdown-menu-right">
                      @can('roles.update')
                        <a href="{{ route('roles.edit',$role) }}" class="dropdown-item">
                          <i class="far fa-edit mr-2"></i> Éditer
                        </a>
                      @endcan

                      @can('roles.delete')
                        <div class="dropdown-divider"></div>
                        <form action="{{ route('roles.destroy',$role) }}" method="POST"
                              onsubmit="return confirm('Supprimer ce rôle ?');" class="m-0 p-0">
                          @csrf @method('DELETE')
                          <button class="dropdown-item text-danger">
                            <i class="far fa-trash-alt mr-2"></i> Supprimer
                          </button>
                        </form>
                      @endcan
                    </div>
                  </div>
                @endcanany

                {{-- ▼ Version B : garde le bouton même sans droits
                <div class="dropdown position-static">
                  <button class="btn btn-sm btn-outline-secondary"
                          data-toggle="dropdown" data-display="static">
                    <i class="fas fa-ellipsis-v"></i>
                  </button>
                  <div class="dropdown-menu dropdown-menu-right">
                    @canany(['roles.update','roles.delete'])
                      @can('roles.update')
                        <a href="{{ route('roles.edit',$role) }}" class="dropdown-item">
                          <i class="far fa-edit mr-2"></i> Éditer
                        </a>
                      @endcan
                      @can('roles.delete')
                        <div class="dropdown-divider"></div>
                        <form action="{{ route('roles.destroy',$role) }}" method="POST"
                              onsubmit="return confirm('Supprimer ce rôle ?');" class="m-0 p-0">
                          @csrf @method('DELETE')
                          <button class="dropdown-item text-danger">
                            <i class="far fa-trash-alt mr-2"></i> Supprimer
                          </button>
                        </form>
                      @endcan
                    @else
                      <span class="dropdown-item text-muted disabled">Aucune action</span>
                    @endcanany
                  </div>
                </div>
                --}}
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="3" class="text-center text-muted p-4">Aucun rôle.</td>
            </tr>
          @endforelse
          </tbody>
        </table>
      </div>
    </div>

    @if(method_exists($roles,'links'))
      <div class="card-footer">
        {{ $roles->links('pagination::bootstrap-4') }}
      </div>
    @endif
  </div>
@endsection

@push('css')
<style>
  /* Laisse .table-responsive scroller, mais permet au dropdown de s'afficher correctement */
  .table-responsive .dropdown-menu{ will-change: transform; }
</style>
@endpush
