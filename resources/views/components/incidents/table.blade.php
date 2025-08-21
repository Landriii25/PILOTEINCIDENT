@props([
    'incidents',                  // LengthAwarePaginator|Collection d’Incidents
    'compact' => false,           // true => tableau plus serré
    'showAssignee' => true,       // masquer/afficher la colonne "Assigné"
    'showCreatedAt' => false,     // afficher la date de création
    'showStatus' => true,         // afficher la colonne "Statut"
    'showActions' => true,        // afficher la colonne Actions
])

@php
    $cellClass = $compact ? 'py-2 align-middle' : 'align-middle';
@endphp

<div class="table-responsive">
    <table class="table table-hover mb-0">
        <thead>
            <tr>
                <th style="width:110px">Code</th>
                <th>Titre</th>
                <th>Appli</th>
                <th>Priorité</th>
                @if($showStatus)<th>Statut</th>@endif
                @if($showAssignee)<th>Assigné</th>@endif
                <th>Échéance</th>
                @if($showCreatedAt)<th>Créé le</th>@endif
                @if($showActions)<th class="text-right pr-3" style="width:90px">Actions</th>@endif
            </tr>
        </thead>
        <tbody>
            @forelse($incidents as $it)
                <tr>
                    <td class="{{ $cellClass }}">
                        <a href="{{ route('incidents.show',$it) }}" class="font-weight-bold">
                            {{ $it->code }}
                        </a>
                    </td>
                    <td class="{{ $cellClass }}">
                        {{ \Illuminate\Support\Str::limit($it->titre ?? $it->description, 80) }}
                    </td>
                    <td class="{{ $cellClass }}">{{ optional($it->application)->nom ?? '—' }}</td>
                    <td class="{{ $cellClass }}">
                        @switch($it->priorite)
                            @case('Critique') <span class="badge badge-danger">Critique</span> @break
                            @case('Haute')    <span class="badge badge-warning">Haute</span>   @break
                            @case('Moyenne')  <span class="badge badge-info">Moyenne</span>    @break
                            @default          <span class="badge badge-secondary">Basse</span>
                        @endswitch
                    </td>

                    @if($showStatus)
                        <td class="{{ $cellClass }}">
                            @php
                                $statusMap = [
                                    'Ouvert'   => 'secondary',
                                    'En cours' => 'primary',
                                    'Résolu'   => 'success',
                                    'Fermé'    => 'dark',
                                ];
                                $color = $statusMap[$it->statut ?? 'Ouvert'] ?? 'secondary';
                            @endphp
                            <span class="badge badge-{{ $color }}">{{ $it->statut ?? 'Ouvert' }}</span>
                        </td>
                    @endif

                    @if($showAssignee)
                        <td class="{{ $cellClass }}">
                            {{ optional($it->technicien)->name ?? '—' }}
                        </td>
                    @endif

                    <td class="{{ $cellClass }}">
                        @if($it->due_at)
                            <i class="far fa-clock mr-1 text-muted"></i>{{ $it->due_at->diffForHumans() }}
                        @else
                            —
                        @endif
                    </td>

                    @if($showCreatedAt)
                        <td class="{{ $cellClass }}">
                            {{ optional($it->created_at)->format('d/m/Y H:i') ?? '—' }}
                        </td>
                    @endif

                    @if($showActions)
                        <td class="{{ $cellClass }} text-right pr-3">
                            {{-- Dropdown actions (icônes seules) --}}
                            <div class="btn-group">
                                <button type="button" class="btn btn-sm btn-light border dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Actions">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right">
                                    <a class="dropdown-item" href="{{ route('incidents.show',$it) }}">
                                        <i class="far fa-eye mr-2"></i>Ouvrir
                                    </a>

                                    @can('update incidents')
                                    <a class="dropdown-item" href="{{ route('incidents.edit',$it) }}">
                                        <i class="far fa-edit mr-2"></i>Éditer
                                    </a>
                                    @endcan

                                    @can('delete incidents')
                                    <form action="{{ route('incidents.destroy',$it) }}" method="POST"
                                          onsubmit="return confirm('Supprimer cet incident ?');">
                                        @csrf @method('DELETE')
                                        <button class="dropdown-item text-danger">
                                            <i class="far fa-trash-alt mr-2"></i>Supprimer
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </div>
                        </td>
                    @endif
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 7 + ($showAssignee?1:0) + ($showCreatedAt?1:0) + ($showStatus?1:0) }}" class="text-center text-muted p-4">
                        Aucun incident à afficher.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if(method_exists($incidents,'links'))
    <div class="mt-3">
        {{ $incidents->links('pagination::bootstrap-4') }}
    </div>
@endif
