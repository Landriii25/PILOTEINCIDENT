@props(['actions' => []])

<div class="dropdown">
    <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-toggle="dropdown">
        <i class="fas fa-ellipsis-v"></i>
    </button>
    <div class="dropdown-menu dropdown-menu-right shadow-sm">
        @foreach($actions as $act)
            @if($act['type'] === 'link')
                <a href="{{ $act['href'] }}"
                   class="dropdown-item d-flex align-items-center {{ $act['class'] ?? '' }}">
                    <i class="{{ $act['icon'] }} mr-2 text-muted"></i> {{ $act['label'] }}
                </a>
            @elseif($act['type'] === 'delete')
                <form method="POST" action="{{ $act['href'] }}"
                      onsubmit="return confirm('{{ $act['confirm'] ?? 'Confirmer la suppression ?' }}');">
                    @csrf @method('DELETE')
                    <button class="dropdown-item d-flex align-items-center text-danger">
                        <i class="{{ $act['icon'] ?? 'fas fa-trash' }} mr-2"></i> {{ $act['label'] ?? 'Supprimer' }}
                    </button>
                </form>
            @endif
        @endforeach
    </div>
</div>
