<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

// Models
use App\Models\Incident;
use App\Models\Application;
use App\Models\KbArticle;
use App\Models\KbCategory;
use App\Models\Service;
use App\Models\User;

// Policies
use App\Policies\IncidentPolicy;
use App\Policies\ApplicationPolicy;
use App\Policies\KbArticlePolicy;
use App\Policies\KbCategoryPolicy;
use App\Policies\ServicePolicy;
use App\Policies\UserPolicy;
use App\Policies\RolePolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     * NB: Laravel 12 n’exige pas forcément $policies, mais on le laisse explicite.
     */
    protected $policies = [
        Incident::class    => IncidentPolicy::class,
        Application::class => ApplicationPolicy::class,
        KbArticle::class   => KbArticlePolicy::class,
        KbCategory::class  => KbCategoryPolicy::class,
        Service::class     => ServicePolicy::class,
        User::class        => UserPolicy::class,
        // Pour les rôles, il n’y a pas de modèle Eloquent custom.
        // On met une Policy dédiée pour centraliser la permission roles.*
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // ✅ Admin = tous les droits
        Gate::before(function ($user, $ability) {
            return $user->hasRole('admin') ? true : null;
        });

        // ✅ Alias gate pour la gestion des rôles (si tu préfères passer par une Gate)
        Gate::define('roles.manage', function ($user) {
            return $user->can('roles.*') || $user->can('roles.manage');
        });
    }
}
