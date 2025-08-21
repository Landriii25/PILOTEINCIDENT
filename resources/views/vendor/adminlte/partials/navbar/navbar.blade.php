@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

<nav class="main-header navbar
    {{ config('adminlte.classes_topnav_nav', 'navbar-expand') }}
    {{ config('adminlte.classes_topnav', 'navbar-white navbar-light') }}">

    {{-- Gauche --}}
    <ul class="navbar-nav">
        {{-- Bouton burger (sidebar) --}}
        @include('adminlte::partials.navbar.menu-item-left-sidebar-toggler')

        {{-- Liens configurés côté gauche --}}
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-left'), 'item')

        {{-- Hooks custom --}}
        @yield('content_top_nav_left')
    </ul>

    {{-- Droite --}}
    <ul class="navbar-nav ml-auto">
        {{-- Hooks custom --}}
        @yield('content_top_nav_right')

        {{-- Liens configurés côté droit (search, fullscreen, etc.) --}}
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-right'), 'item')

        {{-- NOTIFICATIONS --------------------------------------------------- --}}
        @php
            $user = auth()->user();
            $unread = $user ? $user->unreadNotifications()->latest()->limit(10)->get() : collect();
            $unreadCount = $user ? $user->unreadNotifications()->count() : 0;
        @endphp

        @if($user)
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#" aria-expanded="false" title="Notifications">
                <i class="far fa-bell"></i>
                @if($unreadCount > 0)
                    <span class="badge badge-danger navbar-badge">{{ $unreadCount }}</span>
                @endif
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right p-0">
                <span class="dropdown-item dropdown-header">
                    {{ $unreadCount }} notification(s)
                </span>
                <div class="dropdown-divider"></div>

                @forelse($unread as $n)
                    <a href="{{ route('notifications.go', $n->id) }}" class="dropdown-item">
                        <i class="fas fa-info-circle mr-2 text-primary"></i>
                        {{ data_get($n->data, 'title') ?? data_get($n->data,'message','Notification') }}
                        <span class="float-right text-muted text-sm">{{ $n->created_at->diffForHumans() }}</span>
                    </a>
                    <div class="dropdown-divider"></div>
                @empty
                    <span class="dropdown-item text-muted">Aucune notification</span>
                    <div class="dropdown-divider"></div>
                @endforelse

                <form action="{{ route('notifications.readAll') }}" method="POST" class="px-2 pb-2">
                    @csrf
                    <button class="btn btn-sm btn-outline-secondary btn-block">Tout marquer comme lu</button>
                </form>
            </div>
        </li>
        @endif

        {{-- USER MENU ------------------------------------------------------- --}}
        @if($user)
            @php
                $roleName  = $user->roles->first()->name ?? 'utilisateur';
                $roleLabel = [
                    'admin'       => 'Administrateur',
                    'superviseur' => 'Superviseur',
                    'technicien'  => 'Technicien',
                    'utilisateur' => 'Utilisateur',
                ][$roleName] ?? ucfirst($roleName);
            @endphp

            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle d-flex align-items-center" data-toggle="dropdown">
                    {{-- Avatar (ui-avatars via accessor) --}}
                    <x-avatar :name="$user->name" :url="$user->avatar_url" size="32" class="mr-2"/>
                    <span class="d-none d-md-inline font-weight-bold">{{ $user->name }}</span>
                </a>

                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right user-dropdown">
                    {{-- En-tête avec fond dégradé --}}
                    <li class="user-header">
                        <x-avatar :name="$user->name" :url="$user->avatar_url" size="72" class="mb-2 shadow" />
                        <p class="mb-1 font-weight-bold">{{ $user->name }}</p>
                        <span class="role-pill">{{ $roleLabel }}</span>
                    </li>

                    {{-- Pied : actions --}}
                    <li class="user-footer">
                        <a href="{{ route('profile.edit') }}" class="btn btn-soft w-50 text-left">
                            <i class="fas fa-user mr-2"></i> Profil
                        </a>

                        <form action="{{ route('logout') }}" method="POST" class="m-0 w-50">
                            @csrf
                            <button type="submit" class="btn btn-logout w-100 text-left">
                                <i class="fas fa-sign-out-alt mr-2"></i> Déconnexion
                            </button>
                        </form>
                    </li>
                </ul>
            </li>
        @endif
    </ul>
</nav>

@once
@push('css')
<style>
/* Avatar de base (au cas où x-avatar n’est pas stylé) */
.navbar .rounded-circle{ border-radius:50%!important }

/* Dropdown utilisateur */
.user-dropdown{ width:320px; border-radius:16px; overflow:hidden; }
.user-dropdown .user-header{
    background:linear-gradient(135deg,#2f80ed,#56ccf2);
    color:#fff; text-align:center; padding:24px 16px;
}
.user-dropdown .role-pill{
    display:inline-block; padding:.18rem .6rem; border-radius:9999px;
    background:rgba(255,255,255,.2); color:#fff; font-size:.75rem;
}
.user-dropdown .user-footer{
    display:flex; gap:.5rem; padding:.75rem; border-top:1px solid rgba(0,0,0,.06);
}

/* Boutons */
.btn-soft{
    background:#f5f7fb; border:1px solid #e6ebf2; color:#3a3f51;
    font-weight:600; border-radius:12px; box-shadow:0 1px 2px rgba(16,24,40,.04);
    transition:transform .12s ease, box-shadow .12s ease, background .12s ease, border-color .12s ease;
    padding:.55rem .8rem;
}
.btn-soft:hover{ background:#eef2f8; border-color:#d9e1ec; transform:translateY(-1px);
    box-shadow:0 4px 14px rgba(16,24,40,.12); }
.btn-logout{
    background:#e03131; border:1px solid #e03131; color:#fff;
    font-weight:700; border-radius:12px; box-shadow:0 1px 2px rgba(224,49,49,.18);
    transition:transform .12s ease, box-shadow .12s ease, filter .12s ease;
    padding:.55rem .8rem;
}
.btn-logout:hover{ filter:brightness(.95); transform:translateY(-1px);
    box-shadow:0 8px 22px rgba(224,49,49,.25); }
</style>
@endpush
@endonce
