@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('title', 'Connexion')

{{-- CSS custom (légèrement ajusté pour les nouveaux styles) --}}
@section('adminlte_css')
<style>
    /* Le fond de la page reste le même */
    body.login-page, body.register-page {
        background: radial-gradient(1200px 600px at 10% -10%, #e0f0ff 0%, transparent 50%),
                    radial-gradient(1000px 600px at 110% 10%, #e8ffe6 0%, transparent 50%),
                    linear-gradient(135deg, #f7f9fc 0%, #eef3f8 100%);
        min-height: 100vh;
    }

    /* Ajustements de la carte et du logo */
    .login-box { width: 420px; max-width: 92%; }
    .login-logo { margin-bottom: 1.5rem; }
    .login-logo img { width: 60px; height: auto; }
    .login-logo .h1 { font-weight: 700; color: #1f2d3d; }

    .card {
        border:0; border-radius:16px; backdrop-filter:blur(6px);
        background:rgba(255,255,255,.82); box-shadow:0 10px 30px rgba(16,24,40,.12);
    }
    .login-card-body { padding: 2.5rem; } /* Plus d'espacement interne */

    /* MODIFICATION : Titres à l'intérieur de la carte */
    .login-card-body .h2 { font-weight: 700; }
    .login-card-body .text-muted { margin-bottom: 2rem; }

    .input-group .form-control { border-radius:12px 0 0 12px; padding:.8rem .9rem; border-color:#e5e7eb; }
    .input-group .input-group-text, .input-group .btn-icon {
        border-radius:0 12px 12px 0; border:1px solid #e5e7eb; background:#f7f7f9;
    }

    /* MODIFICATION : Bouton pleine largeur */
    .btn-block { border-radius:12px; padding:.8rem 1rem; font-weight:600; box-shadow:0 6px 16px rgba(29,78,216,.2); }
    .btn-block:hover { transform:translateY(-1px); transition:.15s ease; }

    .login-actions-row { display:flex; align-items:center; justify-content:space-between; }
    .login-actions-row a { font-size: 0.9rem; }

    .alert { border-radius:12px; }
    .caps-warn { font-size:.85rem; margin-top:.35rem; }
</style>
@endsection

@section('auth_body')
    {{-- MODIFICATION : Le titre est maintenant dans la carte --}}
    <h2 class="text-center">Connectez-vous</h2>
    <p class="text-muted text-center">Accédez à votre compte PiloteIncident</p>

    <form action="{{ route('login') }}" method="POST" autocomplete="on" novalidate>
        @csrf

        {{-- Email --}}
        <div class="input-group mb-3">
            <input type="email" name="email"
                   class="form-control @error('email') is-invalid @enderror"
                   placeholder="Adresse e‑mail"
                   value="{{ old('email') }}" required autofocus autocomplete="username">
            <div class="input-group-append">
                <div class="input-group-text"><span class="fas fa-envelope"></span></div>
            </div>
        </div>

        {{-- Mot de passe + œil + Caps Lock --}}
        <div class="input-group mb-1">
            <input type="password" name="password" id="password"
                   class="form-control @error('password') is-invalid @enderror"
                   placeholder="Mot de passe" required autocomplete="current-password">
            <div class="input-group-append">
                <button type="button" class="input-group-text btn-icon" id="togglePwd" aria-label="Afficher le mot de passe">
                    <span class="fas fa-lock" id="pwdIcon"></span>
                </button>
            </div>
        </div>
        <small id="capsWarn" class="text-danger d-none caps-warn"><i class="fas fa-exclamation-triangle mr-1"></i>Verr. Maj activé</small>

        {{-- MODIFICATION : 'Se souvenir' et 'Mot de passe oublié' sont sur la même ligne --}}
        <div class="login-actions-row my-4">
            <div class="icheck-primary">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Se souvenir de moi</label>
            </div>
            @if (Route::has('password.request'))
                <a href="#" onclick="alert('Veuillez contacter l\'administrateur pour réinitialiser votre mot de passe.'); return false;">
                    {{-- Le lien "Mot de passe oublié" affiche maintenant une alerte --}}
                </a>
            @endif
        </div>

        {{-- MODIFICATION : Le bouton de connexion est maintenant pleine largeur --}}
        <button type="submit" class="btn btn-primary btn-block">Se connecter</button>

    </form>
@endsection

{{-- MODIFICATION : Le footer a été retiré car "Mot de passe oublié" est maintenant dans le formulaire --}}
@section('auth_footer')
@endsection

{{-- Le JavaScript pour l'UX (afficher/masquer mdp, alerte caps lock) est parfait, on le garde tel quel --}}
@push('js')
<script>
(function () {
    const pwd   = document.getElementById('password');
    const btn   = document.getElementById('togglePwd');
    const icon  = document.getElementById('pwdIcon');
    const warn  = document.getElementById('capsWarn');

    // 1) Afficher/Masquer le mot de passe
    btn?.addEventListener('click', function(){
        if (!pwd || !icon) return;
        const toText = pwd.type === 'password';
        pwd.type = toText ? 'text' : 'password';
        icon.className = toText ? 'fas fa-unlock' : 'fas fa-lock';
        btn.setAttribute('aria-label', toText ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
    });

    // 2) Alerte Caps Lock
    function onKey(e){
        if (!warn || !e.getModifierState) return;
        const on = e.getModifierState('CapsLock');
        warn.classList.toggle('d-none', !on);
    }
    pwd?.addEventListener('keyup', onKey);
    pwd?.addEventListener('keydown', onKey);
})();
</script>
@endpush
