@extends('adminlte::page')

@section('title','Utilisateurs')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between">
        <h1 class="m-0">Utilisateurs</h1>

        @can('users.create')
            <a href="{{ route('users.create') }}" class="btn btn-primary">
                <i class="fas fa-user-plus mr-1"></i> Nouvel utilisateur
            </a>
        @endcan
    </div>
@stop

@section('content')
    {{-- Flash --}}
    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
    @if(session('error'))   <div class="alert alert-danger">{{ session('error') }}</div> @endif

    <div class="card">
        <div class="card-body p-0 table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead>
                <tr>
                    <th style="width:56px;"> </th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Service</th>
                    <th>Rôles</th>
                    <th class="text-right pr-3" style="width:70px;">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($users as $u)
                    @php
                        $avatar = 'https://ui-avatars.com/api/?name='.urlencode($u->name).'&background=0F766E&color=fff&size=128';
                        $serviceName = optional($u->service)->nom ?? '—';
                        $roles = $u->roles->pluck('name')->map(function($r){
                            return [
                                'name'  => $r,
                                'label' => [
                                    'admin'       => 'Administrateur',
                                    'superviseur' => 'Superviseur',
                                    'technicien'  => 'Technicien',
                                    'utilisateur' => 'Utilisateur',
                                ][$r] ?? ucfirst($r),
                                'badge' => [
                                    'admin'       => 'danger',
                                    'superviseur' => 'warning',
                                    'technicien'  => 'info',
                                    'utilisateur' => 'secondary',
                                ][$r] ?? 'light',
                            ];
                        });
                    @endphp
                    <tr>
                        <td>
                            <img src="{{ $avatar }}" class="rounded-circle" width="36" height="36" alt="avatar" style="object-fit:cover;">
                        </td>

                        <td class="font-weight-600">
                            <a href="{{ route('users.show', $u) }}">{{ $u->name }}</a>
                            @if(!empty($u->title))
                                <div class="text-muted small">{{ $u->title }}</div>
                            @endif
                        </td>

                        <td class="text-nowrap">{{ $u->email }}</td>

                        <td>{{ $serviceName }}</td>

                        <td class="text-nowrap">
                            @forelse($roles as $r)
                                <span class="badge badge-{{ $r['badge'] }} mr-1">{{ $r['label'] }}</span>
                            @empty
                                <span class="text-muted">—</span>
                            @endforelse
                        </td>

                        <td class="text-right pr-3">
                            {{-- Dropdown d’actions unifié (même composant que pour incidents/applications) --}}
                            <x-action.dropdown :actions="array_values(array_filter([
                                auth()->user()->can('users.view')
                                    ? [
                                        'type'  => 'link',
                                        'href'  => route('users.show', $u),
                                        'icon'  => 'fas fa-eye',
                                        'label' => 'Voir',
                                      ] : null,
                                auth()->user()->can('users.update')
                                    ? [
                                        'type'  => 'link',
                                        'href'  => route('users.edit', $u),
                                        'icon'  => 'fas fa-edit',
                                        'label' => 'Éditer',
                                        'class' => 'text-warning',
                                      ] : null,
                                auth()->user()->can('users.delete')
                                    ? [
                                        'type'    => 'delete',
                                        'href'    => route('users.destroy', $u),
                                        'icon'    => 'fas fa-trash',
                                        'label'   => 'Supprimer',
                                        'confirm' => 'Supprimer cet utilisateur ?',
                                      ] : null,
                            ]))" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted p-4">Aucun utilisateur.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($users,'links'))
            <div class="card-footer">
                {{ $users->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
@stop

@push('css')
<style>
    .table td, .table th { vertical-align: middle; }
    .font-weight-600 { font-weight: 600; }
    .dropdown-toggle::after { display: none; }
</style>
@endpush
