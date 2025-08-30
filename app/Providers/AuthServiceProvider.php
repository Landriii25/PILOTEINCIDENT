<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

// Models
use App\Models\Application;
use App\Models\Incident;
use App\Models\KbArticle;
use App\Models\KbCategory;
use App\Models\Service as AppService;
use App\Models\User;
use App\Models\Report;

// Policies
use App\Policies\ApplicationPolicy;
use App\Policies\IncidentPolicy;
use App\Policies\KbArticlePolicy;
use App\Policies\KbCategoryPolicy;
use App\Policies\ServicePolicy;
use App\Policies\UserPolicy;
use App\Policies\ReportPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Application::class => ApplicationPolicy::class,
        Incident::class    => IncidentPolicy::class,
        KbArticle::class   => KbArticlePolicy::class,
        KbCategory::class  => KbCategoryPolicy::class,
        AppService::class  => ServicePolicy::class,
        User::class        => UserPolicy::class,
        Report::class      => ReportPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        // Admin = accès total
        Gate::before(function ($user, ?string $ability = null) {
            return $user && method_exists($user, 'hasRole') && $user->hasRole('admin')
                ? true
                : null;
        });
    }
}
