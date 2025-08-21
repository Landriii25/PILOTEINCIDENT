@extends('adminlte::page')

@section('title', 'Mon profil')

@section('content_header')
    <h1>Mon profil</h1>
    <p class="text-muted mb-0">Mettre à jour mes informations et mon mot de passe</p>
@endsection

@section('content')
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger mb-3">
            <ul class="mb-0">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-3">
                <div class="card-header"><strong>Informations du profil</strong></div>
                <div class="card-body">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><strong>Changer le mot de passe</strong></div>
                <div class="card-body">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="card">
                <div class="card-header"><strong>Supprimer mon compte</strong></div>
                <div class="card-body">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            {{-- Encadré résumé --}}
            <div class="card">
                <div class="card-body d-flex align-items-center">
                    @php
                        $user = auth()->user();
                        $initials = collect(preg_split('/\s+/', trim($user->name)))
                            ->filter()->map(fn($p)=>strtoupper(substr($p,0,1)))->take(2)->implode('') ?: 'U';
                    @endphp
                    <span class="d-inline-flex justify-content-center align-items-center bg-primary text-white rounded-circle mr-3"
                          style="width:48px;height:48px;font-weight:700;">{{ $initials }}</span>
                    <div>
                        <div class="mb-0 font-weight-bold">{{ $user->name }}</div>
                        @if($user->title)<small class="text-muted">{{ $user->title }}</small>@endif
                        <div class="mt-1"><x-role-badge :user="$user" /></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
