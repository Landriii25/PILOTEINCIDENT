<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Configuration\Exceptions;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        // api: __DIR__ . '/../routes/api.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Résolution des classes Spatie selon la version installée
        $RoleMwClass = class_exists(\Spatie\Permission\Middlewares\RoleMiddleware::class)
            ? \Spatie\Permission\Middlewares\RoleMiddleware::class
            : (class_exists(\Spatie\Permission\Middleware\RoleMiddleware::class)
                ? \Spatie\Permission\Middleware\RoleMiddleware::class
                : null);

        $PermMwClass = class_exists(\Spatie\Permission\Middlewares\PermissionMiddleware::class)
            ? \Spatie\Permission\Middlewares\PermissionMiddleware::class
            : (class_exists(\Spatie\Permission\Middleware\PermissionMiddleware::class)
                ? \Spatie\Permission\Middleware\PermissionMiddleware::class
                : null);

        $RoleOrPermMwClass = class_exists(\Spatie\Permission\Middlewares\RoleOrPermissionMiddleware::class)
            ? \Spatie\Permission\Middlewares\RoleOrPermissionMiddleware::class
            : (class_exists(\Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class)
                ? \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class
                : null);

        // Enregistre les alias uniquement si les classes existent
        $aliases = [];

        if ($RoleMwClass) {
            $aliases['role'] = $RoleMwClass;
        }
        if ($PermMwClass) {
            $aliases['permission'] = $PermMwClass;
        }
        if ($RoleOrPermMwClass) {
            $aliases['role_or_permission'] = $RoleOrPermMwClass;
        }

        // Si rien n’a été résolu, on lève une erreur explicite
        if (empty($aliases)) {
            throw new RuntimeException(
                "Spatie Laravel-Permission middlewares introuvables. " .
                "Vérifie l'installation: composer require spatie/laravel-permission"
            );
        }

        $middleware->alias($aliases);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
