{{-- resources/views/components/incidents/table.blade.php --}}
@props([
    'incidents',
    'compact' => false,
    'showAssignee' => true,
    'showCreatedAt' => true,
    'showStatus' => true,
    'showActions' => true,
])

<div class="table-responsive">
    <table class="table {{ $compact ? 'table-sm' : '' }} table-hover mb-0 incidents-list">
        <thead class="thead-light">
            <tr>
                <th>Code</th>
                <th>Titre</th>
                <th>Appli</th>
                <th>Priorité</th>
                @if($showStatus)
                    <th>Statut</th>
                @endif
                @if($showAssignee)
                    <th>Assigné</th>
                @endif
                <th>Échéance</th>
                @if($showCreatedAt)
                    <th>Créé le</th>
                @endif
                @if($showActions)
                    <th class="text-right">Action</th>
                @endif
            </tr>
        </thead>

        <tbody>
        @forelse($incidents as $it)
            <tr>
                {{-- Code (nowrap via CSS global) --}}
                <td>
                    <a href="{{ route('incidents.show', $it) }}" class="font-weight-bold">
                        {{ $it->code }}
                    </a>
                </td>

                {{-- Titre / description courte --}}
                <td>
                    {{ \Illuminate\Support\Str::limit($it->titre ?? $it->description ?? '—', 70) }}
                </td>

                {{-- Application --}}
                <td>{{ optional($it->application)->nom ?? '—' }}</td>

                {{-- Priorité --}}
                <td>
                    @php
                        $prioClass = [
                            'Critique' => 'danger',
                            'Haute'    => 'warning',
                            'Moyenne'  => 'info',
                            'Basse'    => 'secondary',
                        ][$it->priorite] ?? 'secondary';
                    @endphp
                    <span class="badge badge-{{ $prioClass }}">{{ $it->priorite ?? '—' }}</span>
                </td>

                {{-- Statut --}}
                @if($showStatus)
                    <td>
                        @php
                            $stClass = match ($it->statut) {
                                'Ouvert'   => 'primary',
                                'En cours' => 'info',
                                'Résolu'   => 'success',
                                default    => 'secondary',
                            };
                        @endphp
                        <span class="badge badge-{{ $stClass }}">{{ $it->statut ?? '—' }}</span>
                    </td>
                @endif

                {{-- Assigné --}}
                @if($showAssignee)
                    <td>{{ optional($it->technicien)->name ?? '—' }}</td>
                @endif

                {{-- Échéance (SLA) --}}
                <td>
                    @if($it->due_at)
                        <i class="far fa-clock mr-1"></i>
                        {{ $it->due_at->diffForHumans() }}
                    @else
                        —
                    @endif
                </td>

                {{-- Créé le --}}
                @if($showCreatedAt)
                    <td>{{ $it->created_at?->format('d/m/Y H:i') ?? '—' }}</td>
                @endif

                {{-- Actions (dropdown) --}}
                @if($showActions)
                    <td class="text-right">
                        <div class="btn-group">
                            <button type="button" class="btn btn-light btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right">
                                <a class="dropdown-item" href="{{ route('incidents.show', $it) }}">
                                    <i class="far fa-eye mr-2 text-muted"></i> Voir
                                </a>

                                @can('update', $it)
                                    <a class="dropdown-item" href="{{ route('incidents.edit', $it) }}">
                                        <i class="far fa-edit mr-2 text-muted"></i> Éditer
                                    </a>
                                @endcan

                                @can('resolve', $it)
                                    <form action="{{ route('incidents.resolve', $it) }}" method="POST" onsubmit="return confirm('Marquer comme résolu ?');">
                                        @csrf @method('PUT')
                                        <button class="dropdown-item" type="submit">
                                            <i class="fas fa-check mr-2 text-success"></i> Résoudre
                                        </button>
                                    </form>
                                @endcan

                                @can('delete', $it)
                                    <div class="dropdown-divider"></div>
                                    <form action="{{ route('incidents.destroy', $it) }}" method="POST" onsubmit="return confirm('Supprimer cet incident ?');">
                                        @csrf @method('DELETE')
                                        <button class="dropdown-item text-danger" type="submit">
                                            <i class="far fa-trash-alt mr-2"></i> Supprimer
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
                <td colspan="9" class="text-center text-muted p-4">
                    <i class="far fa-folder-open mr-1"></i> Aucun incident trouvé.
                </td>
            </tr>
        @endforelse
        </tbody>
    </table>
</div>

@if(method_exists($incidents,'links'))
    <div class="card-footer">
        {{ $incidents->links('pagination::bootstrap-4') }}
    </div>
@endif
