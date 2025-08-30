@extends('adminlte::page')

@section('title','Paramètres')

@section('content_header')
  <h1 class="m-0">Paramètres de l’application</h1>
@endsection

@section('content')
  @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif
  @if($errors->any())
    <div class="alert alert-danger">
      <strong>Erreurs :</strong>
      <ul class="mb-0">
        @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
      </ul>
    </div>
  @endif

  @php
    $action = \Illuminate\Support\Facades\Route::has('settings.update')
      ? route('settings.update')
      : route('settings.index');
  @endphp

  <form method="POST" action="{{ $action }}">
    @csrf
    {{-- @method('PUT')  <-- dé-commente si tu fais un update en PUT --}}

    <div class="row">
      {{-- Général --}}
      <div class="col-lg-6">
        <div class="card">
          <div class="card-header"><strong>Général</strong></div>
          <div class="card-body">

            <div class="form-group">
              <label>Nom de l’application</label>
              <input type="text" name="app_name" class="form-control"
                     value="{{ old('app_name', $settings['app_name'] ?? config('app.name')) }}"
                     placeholder="PiloteIncident">
            </div>

            <div class="form-group">
              <label>Fuseau horaire</label>
              <input type="text" name="timezone" class="form-control"
                     value="{{ old('timezone', $settings['timezone'] ?? config('app.timezone','UTC')) }}"
                     placeholder="Africa/Abidjan">
              <small class="text-muted">Ex: Africa/Abidjan, Europe/Paris…</small>
            </div>

            <div class="form-group">
              <label>Langue par défaut</label>
              <select name="locale" class="form-control">
                @foreach(['fr','en'] as $loc)
                  <option value="{{ $loc }}"
                    @selected(old('locale', $settings['locale'] ?? app()->getLocale()) === $loc)>
                    {{ strtoupper($loc) }}
                  </option>
                @endforeach
              </select>
            </div>

          </div>
        </div>
      </div>

      {{-- SLA --}}
      <div class="col-lg-6">
        <div class="card">
          <div class="card-header"><strong>SLA</strong></div>
          <div class="card-body">
            <div class="form-group">
              <label>Seuil “à risque” (heures restantes)</label>
              <input type="number" min="0" name="sla_warning_hours" class="form-control"
                     value="{{ old('sla_warning_hours', $settings['sla_warning_hours'] ?? 4) }}">
              <small class="text-muted">En‑dessous de ce seuil, l’incident passe en “échéance &lt; Xh”.</small>
            </div>

            <div class="form-group">
              <label>Délai de prise en charge visé (minutes)</label>
              <input type="number" min="0" name="target_pickup_minutes" class="form-control"
                     value="{{ old('target_pickup_minutes', $settings['target_pickup_minutes'] ?? 60) }}">
            </div>
          </div>
        </div>
      </div>

      {{-- Notifications --}}
      <div class="col-lg-6">
        <div class="card">
          <div class="card-header"><strong>Notifications</strong></div>
          <div class="card-body">
            <div class="custom-control custom-switch mb-2">
              <input type="checkbox" class="custom-control-input" id="notif_new_incident"
                     name="notif_new_incident" value="1"
                     {{ old('notif_new_incident', $settings['notif_new_incident'] ?? true) ? 'checked' : '' }}>
              <label class="custom-control-label" for="notif_new_incident">
                Notifier à la création d’un incident
              </label>
            </div>
            <div class="custom-control custom-switch mb-2">
              <input type="checkbox" class="custom-control-input" id="notif_sla_risk"
                     name="notif_sla_risk" value="1"
                     {{ old('notif_sla_risk', $settings['notif_sla_risk'] ?? true) ? 'checked' : '' }}>
              <label class="custom-control-label" for="notif_sla_risk">
                Notifier quand un SLA est à risque
              </label>
            </div>
            <div class="custom-control custom-switch">
              <input type="checkbox" class="custom-control-input" id="notif_reassign"
                     name="notif_reassign" value="1"
                     {{ old('notif_reassign', $settings['notif_reassign'] ?? true) ? 'checked' : '' }}>
              <label class="custom-control-label" for="notif_reassign">
                Notifier lors d’une réaffectation
              </label>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="d-flex justify-content-end">
      <button class="btn btn-success">
        <i class="fas fa-check mr-1"></i> Enregistrer
      </button>
    </div>
  </form>
@endsection
