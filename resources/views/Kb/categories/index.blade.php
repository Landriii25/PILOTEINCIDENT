@extends('adminlte::page')

@section('title','Catégories KB')

@section('content_header')
    <div class="d-flex align-items-center justify-content-between">
        <h1 class="m-0">Catégories</h1>
        <x-can perm="kb.categories.manage">
            <x-action.link href="{{ route('kb.categories.create') }}" icon="fas fa-plus">Nouvelle catégorie</x-action.link>
        </x-can>
    </div>
@stop

@section('content')
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Description</th>
                    <th style="width:240px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categories as $c)
                    <tr>
                        <td>{{ $c->name }}</td>
                        <td class="text-muted">{{ $c->description ?? '—' }}</td>
                        <td>
                            <x-action.link href="{{ route('kb.categories.show', $c) }}" class="btn-info" icon="far fa-eye">Voir</x-action.link>

                            <x-can perm="kb.categories.manage">
                                <x-action.link href="{{ route('kb.categories.edit', $c) }}" class="btn-warning" icon="fas fa-edit">Éditer</x-action.link>
                                <x-action.delete :action="route('kb.categories.destroy',$c)" />
                            </x-can>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center text-muted p-4">Aucune catégorie.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(method_exists($categories,'links'))
        <div class="card-footer">
            {{ $categories->links('pagination::bootstrap-4') }}
        </div>
    @endif
</div>
@stop
