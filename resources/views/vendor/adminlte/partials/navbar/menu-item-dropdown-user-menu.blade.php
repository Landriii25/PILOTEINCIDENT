@php
    // La vue reçoit déjà des variables de la part d'AdminLTE, utilisons-les !
    // $user_name => Auth::user()->name
    // $user_avatar => L'avatar de l'utilisateur
    // $user_role => Le premier rôle de l'utilisateur (ex: "Utilisateur")

    // On récupère juste le titre en plus.
    $user_title = Auth::user()->title;
@endphp

<li class="nav-item dropdown user-menu">

    {{-- Le lien qui ouvre le dropdown --}}
    <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown">
        <img src="{{ $user_avatar }}" class="user-image img-circle elevation-2" alt="{{ $user_name }}">
        <span class="d-none d-md-inline">{{ $user_name }}</span>
    </a>

    {{-- Le contenu du dropdown --}}
    <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-right">

        {{-- L'en-tête du dropdown --}}
        <li class="user-header {{ config('adminlte.classes_auth_header', '') }}">
            <img src="{{ $user_avatar }}" class="img-circle elevation-2" alt="{{ $user_name }}">
            <p>
                {{ $user_name }}
                {{-- CORRECTION : Affiche le titre, ou le rôle si le titre est vide --}}
                <small>{{ $user_title ?? $user_role }}</small>
            </p>
        </li>

        {{-- Les boutons du dropdown --}}
        <li class="user-footer">
            <a href="#" class="btn btn-default btn-flat">Profil</a>
            <a href="#" class="btn btn-default btn-flat float-right"
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                Déconnexion
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </li>

    </ul>
</li>
