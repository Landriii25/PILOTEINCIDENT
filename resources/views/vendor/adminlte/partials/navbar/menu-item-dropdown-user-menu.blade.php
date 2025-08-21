@php
    use Illuminate\Support\Str;

    $user  = Auth::user();
    $title = $user->title ?? null;

    // Rôle (Spatie) -> libellé
    $roleName  = method_exists($user, 'getRoleNames') ? optional($user->getRoleNames())->first() : ($user->role ?? null);
    $labelsMap = [
        'admin'        => 'Administrateur',
        'technicien'   => 'Technicien',
        'superviseur'  => 'Superviseur',
        'utilisateur'  => 'Utilisateur',
    ];
    $roleLabel = $labelsMap[Str::lower((string) $roleName)] ?? null;

    // Initiales
    $initials = collect(preg_split('/\s+/', trim($user->name ?? '')))
        ->filter()->map(fn($p)=>Str::upper(Str::substr($p,0,1)))->take(2)->implode('') ?: 'U';
@endphp

{{-- Styles locales pour ce dropdown (animations hover) --}}
<style>
    .pi-dropdown { border:0; border-radius:14px; overflow:hidden; box-shadow:0 12px 24px rgba(16,24,40,.12); min-width:260px; }
    .pi-header   { background:linear-gradient(135deg,#ffffff,#f1f5f9); color:#0f172a; padding:18px 16px; text-align:center; }
    .pi-avatar   { width:72px; height:72px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center;
                   background:#eff6ff; color:#2563eb; font-weight:800; font-size:24px; box-shadow:0 6px 18px rgba(2,6,23,.15); margin-bottom:10px; }
    .pi-body     { background:#ffffff; padding:12px; }

    /* Boutons animés */
    .pi-btn { border-radius:10px; display:block; width:100%; padding:.6rem .9rem; font-weight:600; transition:transform .15s ease, box-shadow .15s ease, filter .15s ease; }
    .pi-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(2,6,23,.10); filter: brightness(1.02); }

    .pi-btn-profile { border:1px solid #e2e8f0; color:#334155; background:#ffffff; }
    .pi-btn-logout  { background:linear-gradient(135deg,#dc2626,#b91c1c); border:0; color:#fff !important; }

    /* Icônes un peu espacées */
    .pi-btn i { margin-right:.5rem; }
</style>

<li class="nav-item dropdown user-menu">
    {{-- Si AdminLTE 4 / Bootstrap 5, remplace data-toggle par data-bs-toggle --}}
    <a href="#" class="nav-link dropdown-toggle d-inline-flex align-items-center" data-toggle="dropdown">
        <i class="far fa-user-circle fa-lg mr-2"></i>
        <span class="d-none d-md-inline">{{ $user->name }}</span>
    </a>

    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right pi-dropdown">
        {{-- Header clair / glassy --}}
        <li class="user-header pi-header">
            <div class="pi-avatar">{{ $initials }}</div>
            <p class="mb-0" style="font-weight:700; font-size:16px; color:#0f172a;">
                {{ $user->name }}
            </p>
            @if($title)
                <small style="opacity:.9; display:block; color:#334155;">{{ $title }}</small>
            @endif
            @if($roleLabel)
                <small style="opacity:.95; display:block; color:#2563eb;">{{ $roleLabel }}</small>
            @endif
        </li>

        {{-- Actions claires + animées --}}
        <li class="user-body pi-body">
            <a href="{{ route('profile.edit') }}" class="pi-btn pi-btn-profile">
                <i class="fas fa-user-cog"></i> Profil
            </a>

            <a href="#"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
               class="pi-btn pi-btn-logout mt-2">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </li>
    </ul>
</li>
