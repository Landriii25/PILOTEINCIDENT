<form action="{{ route('profile.update') }}" method="POST" class="mt-2">
    @csrf
    @method('PATCH')

    <div class="form-group">
        <label for="password">Nouveau mot de passe</label>
        <input type="password" name="password" id="password"
               class="form-control @error('password') is-invalid @enderror">
        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>

    <div class="form-group">
        <label for="password_confirmation">Confirmer le mot de passe</label>
        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
    </div>

    <button type="submit" class="btn btn-primary">
        <i class="fas fa-key mr-1"></i> Mettre à jour le mot de passe
    </button>
</form>
