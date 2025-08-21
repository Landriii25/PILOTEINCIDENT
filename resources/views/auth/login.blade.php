@extends('adminlte::auth.auth-page', ['auth_type' => 'login'])

@section('title', 'Connexion')

{{-- CSS custom --}}
@section('adminlte_css')
<style>
    body.login-page, body.register-page {
        background: radial-gradient(1200px 600px at 10% -10%, #e0f0ff 0%, transparent 50%),
                    radial-gradient(1000px 600px at 110% 10%, #e8ffe6 0%, transparent 50%),
                    linear-gradient(135deg, #f7f9fc 0%, #eef3f8 100%);
        min-height: 100vh;
    }
    .login-box { width: 420px; max-width: 92%; }
    .login-logo { margin-bottom: 1rem; }
    .brand-wrap { display:flex; align-items:center; justify-content:center; gap:.6rem; }
    .brand-wrap img { width:60px; height:60px; object-fit:contain; filter:drop-shadow(0 2px 6px rgba(0,0,0,.15)); }
    .brand-title { font-weight:700; color:#1f2d3d; font-size:1.35rem; line-height:1.1; letter-spacing:.2px; }
    .brand-title small { display:block; font-weight:500; color:#6b7a90; font-size:.9rem; }

    .login-card-body, .card {
        border:0; border-radius:16px; backdrop-filter:blur(6px);
        background:rgba(255,255,255,.82); box-shadow:0 10px 30px rgba(16,24,40,.12);
    }
    .login-card-body { padding:1.5rem 1.5rem 1rem; }
    .input-group .form-control { border-radius:12px 0 0 12px; padding:.8rem .9rem; border-color:#e5e7eb; }
    .input-group .input-group-text, .input-group .btn-icon {
        border-radius:0 12px 12px 0; border:1px solid #e5e7eb; background:#f7f7f9;
    }
    .btn-primary { border-radius:12px; padding:.7rem 1rem; font-weight:600; box-shadow:0 6px 16px rgba(29,78,216,.2); }
    .btn-primary:hover { transform:translateY(-1px); transition:.15s ease; }
    .login-actions { display:flex; align-items:center; gap:.75rem; }
    .login-footer { border-top:1px solid #eef2f6; background:rgba(250,250,252,.65); border-radius:0 0 16px 16px; padding:.9rem 1.25rem; color:#667085; }
    .alert { border-radius:12px; }
    .caps-warn { font-size:.85rem; margin-top:.35rem; }
</style>
@endsection

@section('auth_header')
    <div class="brand-wrap">
        {{-- Ton logo GS2E --}}
        <img src="{{ asset('vendor/adminlte/dist/img/gs2eci_logo-r.png') }}" alt="GS2E">
        <div class="brand-title">
            GS2E <span class="text-primary">PiloteIncident</span>
            <small>Connectez-vous à votre compte</small>
        </div>
    </div>
@endsection

@section('auth_body')
    @if ($errors->any())
        <div class="alert alert-danger mb-3">
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('login') }}" method="POST" autocomplete="on" novalidate>
        @csrf

        {{-- Email --}}
        <div class="input-group mb-2">
            <input type="email" name="email"
                   class="form-control @error('email') is-invalid @enderror"
                   placeholder="Adresse e‑mail"
                   value="{{ old('email') }}" required autofocus autocomplete="username">
            <div class="input-group-append">
                <div class="input-group-text"><span class="fas fa-envelope"></span></div>
            </div>
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
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
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
        <small id="capsWarn" class="text-danger d-none caps-warn"><i class="fas fa-exclamation-triangle mr-1"></i>Verr. Maj activé</small>

        <div class="login-actions my-3">
            <div class="icheck-primary">
                <input type="checkbox" id="remember" name="remember">
                <label for="remember">Se souvenir de moi</label>
            </div>
            <button type="submit" class="btn btn-primary ml-auto">Se connecter</button>
        </div>
    </form>
@endsection

@section('auth_footer')
    <div class="login-footer">
        <div>Mot de passe oublié ? Merci de contacter l’administrateur.</div>
    </div>
@endsection

{{-- JS des bonus UX --}}
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
        // Accessibilité : annoncer l'état
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
