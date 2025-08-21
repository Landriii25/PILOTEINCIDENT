@extends('adminlte::page')

@section('title','Rôles & Permissions')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between">
        <h1 class="m-0">Rôles</h1>
        <x-can perm="roles.manage">
            <x-action.link href="{{ route('roles.create') }}" icon="fas fa-plus">Nouveau rôle</x-action.link>
        </x-can>
    </div>
@stop

@section('content')
<x-can perm="roles.manage">
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Permissions</th>
                    <th style="width:220px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($roles as $r)
                    <tr>
                        <td>{{ $r->name }}</td>
                        <td class="text-monospace small">
                            {{ $r->permissions->pluck('name')->sort()->join(', ') ?: '—' }}
                        </td>
                        <td>
                            <x-action.link href="{{ route('roles.edit',$r) }}" class="btn-warning" icon="fas fa-edit">Éditer</x-action.link>
                            <x-action.delete :action="route('roles.destroy',$r)" />
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted p-4">Aucun rôle.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
</x-can>
@cannot('roles.manage')
    <div class="alert alert-warning">Accès non autorisé.</div>
@endcannot
@stop
