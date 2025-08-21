@extends('adminlte::page')
@section('title', 'Service')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">Service : {{ $service->nom }}</h1>

        <div>
            @can('services.update')
                <x-action.link href="{{ route('services.edit', $service) }}" icon="far fa-edit" class="btn-warning mr-2">
                    Éditer
                </x-action.link>
            @endcan
            @can('services.delete')
                <form action="{{ route('services.destroy', $service) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Supprimer ce service ?');">
                    @csrf @method('DELETE')
                    <button class="btn btn-danger"><i class="far fa-trash-alt mr-1"></i> Supprimer</button>
                </form>
            @endcan
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Nom</dt>
                        <dd class="col-sm-8">{{ $service->nom }}</dd>

                        <dt class="col-sm-4">Description</dt>
                        <dd class="col-sm-8">{{ $service->description ?? '—' }}</dd>

                        <dt class="col-sm-4">Chef de service</dt>
                        <dd class="col-sm-8">
                            @if($service->chef)
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($service->chef->name) }}&background=0D8ABC&color=fff&size=64&rounded=true"
                                     class="rounded-circle mr-2" width="28" height="28" alt="">
                                {{ $service->chef->name }}
                            @else
                                —
                            @endif
                        </dd>

                        <dt class="col-sm-4">Créé le</dt>
                        <dd class="col-sm-8">{{ $service->created_at?->format('d/m/Y H:i') }}</dd>

                        <dt class="col-sm-4">Mis à jour le</dt>
                        <dd class="col-sm-8">{{ $service->updated_at?->format('d/m/Y H:i') }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            {{-- Applications liées --}}
            <div class="card">
                <div class="card-header"><h3 class="card-title">Applications du service</h3></div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($service->applications ?? [] as $app)
                            <li class="list-group-item d-flex align-items-center">
                                <img src="{{ $app->thumb_url ?? 'https://ui-avatars.com/api/?name='.urlencode($app->nom).'&background=999&color=fff&rounded=true' }}"
                                     class="rounded mr-2" width="28" height="28" alt="">
                                <a href="{{ route('applications.show', $app) }}">{{ $app->nom }}</a>
                                <span class="ml-auto badge badge-{{ $app->statut === 'Actif' ? 'success':'secondary' }}">
                                    {{ $app->statut }}
                                </span>
                            </li>
                        @empty
                            <li class="list-group-item text-muted text-center">Aucune application.</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            {{-- Techniciens (option : passé par le contrôleur) --}}
            @isset($techniciens)
            <div class="card">
                <div class="card-header"><h3 class="card-title">Techniciens</h3></div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($techniciens as $t)
                            <li class="list-group-item d-flex align-items-center">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode($t->name) }}&background=6c757d&color=fff&size=64&rounded=true"
                                     class="rounded-circle mr-2" width="28" height="28" alt="">
                                {{ $t->name }} <span class="text-muted ml-2">{{ $t->email }}</span>
                            </li>
                        @empty
                            <li class="list-group-item text-center text-muted">Aucun technicien.</li>
                        @endforelse
                    </ul>
                </div>
            </div>
            @endisset
        </div>
    </div>
@endsection
