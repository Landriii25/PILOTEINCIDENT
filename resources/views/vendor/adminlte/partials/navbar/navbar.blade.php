@inject('layoutHelper', 'JeroenNoten\LaravelAdminLte\Helpers\LayoutHelper')

@php
    $user = auth()->user();

    // Valeurs par défaut pour éviter "Undefined variable"
    $unread = collect();
    $unreadCount = 0;

    if ($user) {
        try {
            $unread = $user->unreadNotifications()->latest()->limit(10)->get();
            $unreadCount = $user->unreadNotifications()->count();
        } catch (\Throwable $e) {
            // Silencieux si la table/connexion n’est pas prête.
            $unread = collect();
            $unreadCount = 0;
        }
    }
@endphp

<style>
/* Avatar générique (au cas où ton composant ne le force pas) */
.navbar .rounded-circle{ border-radius:50%!important }

/* Dropdown utilisateur */
.user-dropdown{
    width:320px; border-radius:16px; overflow:hidden; border:0;
    box-shadow:0 16px 48px rgba(16,24,40,.16);
}
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
.user-dropdown .user-footer > *{ flex:1; }

/* Boutons harmonisés */
.user-dropdown .user-footer .btn{
    height:48px; line-height:1.15;
    display:inline-flex; align-items:center; justify-content:center;
    gap:.5rem; white-space:nowrap;
}
.user-dropdown .user-footer i{ margin:0; }

/* Bouton doux (Profil) */
.btn-soft{
    background:#f5f7fb; border:1px solid #e6ebf2; color:#3a3f51;
    font-weight:600; border-radius:14px; box-shadow:0 1px 2px rgba(16,24,40,.04);
    transition:transform .12s ease, box-shadow .12s ease, background .12s ease, border-color .12s ease;
    padding:.55rem .8rem;
}
.btn-soft:hover{
    background:#eef2f8; border-color:#d9e1ec; transform:translateY(-1px);
    box-shadow:0 4px 14px rgba(16,24,40,.12);
}

/* Bouton rouge (Déconnexion) */
.btn-logout{
    background:#e03131; border:1px solid #e03131; color:#fff;
    font-weight:700; border-radius:14px; box-shadow:0 1px 2px rgba(224,49,49,.18);
    transition:transform .12s ease, box-shadow .12s ease, filter .12s ease;
    padding:.55rem .8rem;
}
.btn-logout:hover{
    filter:brightness(.95); transform:translateY(-1px);
    box-shadow:0 8px 22px rgba(224,49,49,.25);
}

/* Mobile : dropdown plus large */
@media (max-width: 420px){
  .user-dropdown{ width:92vw; }
}

/* --- AJOUT : Correction pour le texte long des notifications --- */
.navbar .dropdown-menu .dropdown-item {
    white-space: normal;      /* Permet au texte de revenir à la ligne */
    overflow-wrap: break-word;  /* Force la coupe des mots très longs */
}
.navbar .dropdown-menu .dropdown-item .float-right {
    margin-left: 10px; /* Ajoute un petit espace pour que le temps ne colle pas au texte */
}
</style>


<nav class="main-header navbar
    {{ config('adminlte.classes_topnav_nav', 'navbar-expand') }}
    {{ config('adminlte.classes_topnav', 'navbar-white navbar-light') }}">

    {{-- Gauche --}}
    <ul class="navbar-nav">
        @include('adminlte::partials.navbar.menu-item-left-sidebar-toggler')
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-left'), 'item')
        @yield('content_top_nav_left')
    </ul>

    {{-- Droite --}}
    <ul class="navbar-nav ml-auto">
        @yield('content_top_nav_right')
        @each('adminlte::partials.navbar.menu-item', $adminlte->menu('navbar-right'), 'item')

        {{-- NOTIFICATIONS --}}
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
                    {{ $unreadCount }} notification(s) non lue(s)
                </span>
                <div class="dropdown-divider"></div>

                @forelse($unread as $n)
                    <a href="{{ route('notifications.go', $n->id) }}" class="dropdown-item">
                        <i class="fas fa-info-circle mr-2 text-primary"></i>
                        {{-- Ce texte va maintenant revenir à la ligne correctement --}}
                        {{ data_get($n->data, 'title') ?? data_get($n->data,'message','Notification') }}
                        <span class="float-right text-muted text-sm">{{ optional($n->created_at)->diffForHumans() }}</span>
                    </a>
                    <div class="dropdown-divider"></div>
                @empty
                    <span class="dropdown-item text-muted">Aucune nouvelle notification</span>
                    <div class="dropdown-divider"></div>
                @endforelse

                <form action="{{-- route('notifications.readAll') --}}" method="POST" class="px-2 pb-2">
                    @csrf
                    <button class="btn btn-sm btn-outline-secondary btn-block">Tout marquer comme lu</button>
                </form>
            </div>
        </li>
        @endif

        {{-- USER MENU --}}
        @if(Auth::user())
            @php
                $user = Auth::user();
                $userTitle = $user->title;
                $roleName = optional($user->roles->first())->name ?? 'utilisateur';
                $roleLabel = [
                    'admin'       => 'Administrateur',
                    'superviseur' => 'Superviseur',
                    'technicien'  => 'Technicien',
                    'utilisateur' => 'Utilisateur',
                ][$roleName] ?? ucfirst($roleName);
                $displayInfo = $userTitle ?? $roleLabel;
            @endphp
            <li class="nav-item dropdown user-menu">
                <a href="#" class="nav-link dropdown-toggle d-flex align-items-center" data-toggle="dropdown">
                    <x-avatar :name="$user->name" :url="$user->avatar_url" size="32" class="mr-2"/>
                    <span class="d-none d-md-inline font-weight-bold">{{ $user->name }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right user-dropdown">
                    <li class="user-header">
                        <x-avatar :name="$user->name" :url="$user->avatar_url" size="72" class="mb-2 shadow" />
                        <p class="mb-1 font-weight-bold">{{ $user->name }}</p>
                        <span class="role-pill">{{ $displayInfo }}</span>
                    </li>
                    <li class="user-footer">
                        <a href="{{ route('profile.edit') }}" class="btn btn-soft">
                            <i class="fas fa-user mr-2"></i> Profil
                        </a>
                        <form action="{{ route('logout') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-logout">
                                <i class="fas fa-sign-out-alt mr-2"></i> Déconnexion
                            </button>
                        </form>
                    </li>
                </ul>
            </li>
        @endif
    </ul>
</nav>
