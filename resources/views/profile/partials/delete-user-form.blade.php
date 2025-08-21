<form action="{{ route('profile.destroy') }}" method="POST" onsubmit="return confirm('Supprimer définitivement votre compte ?')">
    @csrf
    @method('DELETE')

    <div class="form-group">
        <label for="current_password">Mot de passe actuel</label>
        <input type="password" name="password" id="current_password" class="form-control" required>
        <small class="form-text text-muted">Obligatoire pour confirmer la suppression.</small>
    </div>

    <button type="submit" class="btn btn-danger">
        <i class="fas fa-user-slash mr-1"></i> Supprimer mon compte
    </button>
</form>
