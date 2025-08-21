@extends('adminlte::page')

@section('title', 'Notifications')

@section('content_header')
  <div class="d-flex align-items-center justify-content-between">
    <h1 class="mb-0">Notifications</h1>
    <form action="{{ route('notifications.readAll') }}" method="POST" class="m-0">
      @csrf
      <button class="btn btn-outline-secondary">
        <i class="fas fa-check-double mr-1"></i> Tout marquer comme lu
      </button>
    </form>
  </div>
@endsection

@section('content')
<div class="row">
  <div class="col-12">
    <div class="card card-outline card-primary">
      <div class="card-header p-2">
        <ul class="nav nav-pills" id="notif-tabs">
          <li class="nav-item">
            <a class="nav-link active" href="#tab-unread" data-toggle="tab">
              Non lues
              <span class="badge badge-danger ml-1">{{ $unread->total() }}</span>
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#tab-read" data-toggle="tab">
              Lues
              <span class="badge badge-secondary ml-1">{{ $read->total() }}</span>
            </a>
          </li>
        </ul>
      </div>

      <div class="card-body">
        <div class="tab-content">
          {{-- Non lues --}}
          <div class="tab-pane active" id="tab-unread">
            @if($unread->count() === 0)
              <p class="text-muted m-2">Aucune notification non lue.</p>
            @else
              <div class="list-group">
                @foreach($unread as $n)
                  <div class="list-group-item d-flex align-items-start">
                    <div class="mr-3">
                      <i class="fas fa-bell text-danger"></i>
                    </div>
                    <div class="flex-fill">
                      <div class="d-flex justify-content-between">
                        <strong>{{ data_get($n->data, 'title') ?? 'Notification' }}</strong>
                        <small class="text-muted">{{ $n->created_at->diffForHumans() }}</small>
                      </div>
                      <div class="text-muted">
                        {{ data_get($n->data, 'message') ?? data_get($n->data, 'body') }}
                      </div>
                      <div class="mt-2">
                        @php $url = data_get($n->data, 'url'); @endphp
                        @if($url)
                          <a href="{{ route('notifications.go', $n->id) }}" class="btn btn-sm btn-primary mr-2">
                            <i class="fas fa-arrow-right mr-1"></i> Ouvrir
                          </a>
                        @endif
                        <form action="{{ route('notifications.read', $n->id) }}" method="POST" class="d-inline">
                          @csrf
                          <button class="btn btn-sm btn-outline-secondary">
                            Marquer comme lu
                          </button>
                        </form>
                      </div>
                    </div>
                  </div>
                @endforeach
              </div>

              <div class="mt-3">
                {{ $unread->onEachSide(1)->links() }}
              </div>
            @endif
          </div>

          {{-- Lues --}}
          <div class="tab-pane" id="tab-read">
            @if($read->count() === 0)
              <p class="text-muted m-2">Aucune notification lue.</p>
            @else
              <div class="list-group">
                @foreach($read as $n)
                  <div class="list-group-item d-flex align-items-start">
                    <div class="mr-3">
                      <i class="far fa-bell text-secondary"></i>
                    </div>
                    <div class="flex-fill">
                      <div class="d-flex justify-content-between">
                        <strong>{{ data_get($n->data, 'title') ?? 'Notification' }}</strong>
                        <small class="text-muted">{{ $n->created_at->diffForHumans() }}</small>
                      </div>
                      <div class="text-muted">
                        {{ data_get($n->data, 'message') ?? data_get($n->data, 'body') }}
                      </div>
                      @php $url = data_get($n->data, 'url'); @endphp
                      @if($url)
                        <div class="mt-2">
                          <a href="{{ $url }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-external-link-alt mr-1"></i> Ouvrir
                          </a>
                        </div>
                      @endif
                    </div>
                  </div>
                @endforeach
              </div>

              <div class="mt-3">
                {{ $read->onEachSide(1)->links() }}
              </div>
            @endif
          </div>
        </div>
      </div>

      <div class="card-footer text-muted">
        Astuce : utilisez la cloche dans la barre supérieure pour un accès rapide aux 10 dernières notifications non lues.
      </div>
    </div>
  </div>
</div>
@endsection
