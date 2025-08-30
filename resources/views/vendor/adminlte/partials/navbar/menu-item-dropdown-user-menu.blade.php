@php
    use Illuminate\Support\Str;

    $user  = Auth::user();
    $title = $user->title ?? null;

    $roleName  = method_exists($user, 'getRoleNames') ? optional($user->getRoleNames())->first() : ($user->role ?? null);
    $labelsMap = [
        'admin'        => 'Administrateur',
        'technicien'   => 'Technicien',
        'superviseur'  => 'Superviseur',
        'utilisateur'  => 'Utilisateur',
    ];
    $roleLabel = $labelsMap[Str::lower((string) $roleName)] ?? null;

    $initials = collect(preg_split('/\s+/', trim($user->name ?? '')))
        ->filter()->map(fn($p)=>Str::upper(Str::substr($p,0,1)))->take(2)->implode('') ?: 'U';
@endphp

{{-- Styles locaux (tu peux déplacer ça dans app.css si tu préfères) --}}
<style>
    /* reset dropdown */
    .pi-dropdown { padding:0; border:0; border-radius:14px; overflow:hidden; min-width:260px;
                   box-shadow:0 12px 24px rgba(16,24,40,.12); }
    .pi-dropdown .dropdown-item { padding:0; } /* on gère nous-mêmes les paddings via .pi-btn */

    .pi-header { background:linear-gradient(135deg,#ffffff,#f1f5f9); color:#0f172a; padding:18px 16px; text-align:center; }
    .pi-avatar { width:72px; height:72px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center;
                 background:#eff6ff; color:#2563eb; font-weight:800; font-size:24px; box-shadow:0 6px 18px rgba(2,6,23,.15); margin-bottom:10px; }
    .pi-body   { background:#ffffff; padding:12px; }

    /* boutons */
    .pi-btn { display:block; width:100%; border-radius:10px; padding:.6rem .9rem; font-weight:600;
              color:#334155; text-decoration:none !important; border:1px solid #e2e8f0; background:#fff;
              transition:transform .15s ease, box-shadow .15s ease, filter .15s ease, background .15s ease, color .15s ease; }
    .pi-btn:hover { transform:translateY(-1px); box-shadow:0 6px 16px rgba(2,6,23,.10); background:#f8fafc; }

    .pi-btn-logout { border:0; background:linear-gradient(135deg,#dc2626,#b91c1c); color:#fff !important; }
    .pi-btn-logout:hover { filter:brightness(1.03); }

    .pi-btn i { margin-right:.5rem; }
</style>

<li class="nav-item dropdown user-menu">
    {{-- BS4 = data-toggle / BS5 = data-bs-toggle → on met les deux, c’est sans risque --}}
    <a href="#" class="nav-link dropdown-toggle d-inline-flex align-items-center"
       data-toggle="dropdown" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="far fa-user-circle fa-lg mr-2"></i>
        <span class="d-none d-md-inline">{{ $user->name }}</span>
    </a>

    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right pi-dropdown">
        <li class="user-header pi-header">
            <div class="pi-avatar">{{ $initials }}</div>
            <p class="mb-0" style="font-weight:700; font-size:16px; color:#0f172a;">
                {{ $user->name }}
            </p>
            @if($title)
                <small style="display:block; color:#334155;">{{ $title }}</small>
            @endif
            @if($roleLabel)
                <small style="display:block; color:#2563eb;">{{ $roleLabel }}</small>
            @endif
        </li>

        <li class="user-body pi-body">
            <a href="{{ route('profile.edit') }}" class="pi-btn">
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
