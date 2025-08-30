@php
  $isEdit = isset($role);
  $action = $isEdit ? route('roles.update',$role) : route('roles.store');
  $method = $isEdit ? 'PUT' : 'POST';
  $checked = function(string $perm) use($isEdit,$rolePermissions){
      return $isEdit && !empty($rolePermissions) && in_array($perm, $rolePermissions ?? []);
  };
@endphp

<form method="POST" action="{{ $action }}">
  @csrf
  @if($isEdit) @method('PUT') @endif

  <div class="form-group">
    <label>Nom du rôle <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control" required
           value="{{ old('name', $role->name ?? '') }}" placeholder="ex: superviseur">
  </div>

  <div class="form-group">
    <label class="d-block">Permissions</label>

    <div class="row">
      @foreach($permissions as $perm)
        <div class="col-md-4 col-sm-6 mb-2">
          <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input"
                   id="perm_{{ Str::slug($perm->name) }}"
                   name="permissions[]"
                   value="{{ $perm->name }}"
                   {{ (is_array(old('permissions')) && in_array($perm->name, old('permissions'))) || $checked($perm->name) ? 'checked' : '' }}>
            <label class="custom-control-label" for="perm_{{ Str::slug($perm->name) }}">
              {{ $perm->name }}
            </label>
          </div>
        </div>
      @endforeach
    </div>
    @if($permissions->isEmpty())
      <small class="text-muted">Aucune permission définie.</small>
    @endif
  </div>

    <div class="d-flex justify-content-between">
        <a href="{{ route('roles.index') }}" class="btn btn-secondary">
            i class="fas fa-arrow-left mr-1"></i> Annuler
        </a>
        <button class="btn btn-success">
            <i class="fas fa-check mr-1"></i> {{ $isEdit ? 'Mettre à jour' : 'Créer' }}
        </button>
    </div>
</form>
