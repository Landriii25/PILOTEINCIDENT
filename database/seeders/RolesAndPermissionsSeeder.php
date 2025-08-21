<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Toujours vider le cache Spatie avant toute modif
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $guard = 'web';

        // === 1) Permissions (sans wildcard) ===============================
        $permissions = [
            // Dashboards
            'dashboard.view.admin',
            'dashboard.view.superviseur',
            'dashboard.view.technicien',
            'dashboard.view.utilisateur',

            // Incidents : lecture
            'incidents.view.any',
            'incidents.view.service',
            'incidents.view.assigned',
            'incidents.view.own',

            // Incidents : création / mise à jour / assignation / résolution
            'incidents.create',
            'incidents.update.any',
            'incidents.update.service',
            'incidents.update.assigned',
            'incidents.assign.any',
            'incidents.assign.service',
            'incidents.assign.pickup',
            'incidents.resolve.any',
            'incidents.resolve.service',
            'incidents.resolve.assigned',

            // Incidents : fin de vie
            'incidents.close.any',
            'incidents.close.own',
            'incidents.reopen.any',
            'incidents.reopen.own',
            'incidents.delete',
            'incidents.restore',
            'incidents.forceDelete',

            // Applications (lecture via Policy; écriture via permissions)
            'applications.create',
            'applications.update',
            'applications.delete',
            'applications.restore',
            'applications.forceDelete',

            // Services
            'services.view',
            'services.create',
            'services.update',
            'services.delete',

            // Base de connaissances
            'kb.view',
            'kb.create',
            'kb.update',
            'kb.delete',
            'kb.categories.manage',

            // Utilisateurs / Rôles
            'users.view',
            'users.create',
            'users.update',
            'users.delete',

            'roles.manage',

            // Rapports / Paramètres
            'reports.view',
            'settings.manage',
        ];

        foreach ($permissions as $p) {
            Permission::findOrCreate($p, $guard);
        }

        // === 2) Rôles ======================================================
        $roles = ['admin', 'superviseur', 'technicien', 'utilisateur'];
        foreach ($roles as $r) {
            Role::findOrCreate($r, $guard);
        }

        // === 3) Attribution des permissions par rôle =======================
        // Admin : toutes les permissions (et en plus, tes policies peuvent avoir un before() qui le laisse tout faire)
        Role::findByName('admin', $guard)->syncPermissions(Permission::all());

        // Superviseur
        $superviseurPerms = [
            'dashboard.view.superviseur',
            'incidents.view.service',
            'incidents.create',
            'incidents.update.service',
            'incidents.assign.service',
            'incidents.resolve.service',
            'kb.view', 'kb.create', 'kb.update', 'kb.delete', 'kb.categories.manage',
            'services.view',
            'reports.view',
        ];
        Role::findByName('superviseur', $guard)->syncPermissions(
            Permission::whereIn('name', $superviseurPerms)->get()
        );

        // Technicien
        $technicienPerms = [
            'dashboard.view.technicien',
            'incidents.view.assigned',
            'incidents.update.assigned',
            'incidents.resolve.assigned',
            'incidents.assign.pickup',
            'kb.view', 'kb.create', 'kb.update',
            'reports.view',
        ];
        Role::findByName('technicien', $guard)->syncPermissions(
            Permission::whereIn('name', $technicienPerms)->get()
        );

        // Utilisateur (demandeur)
        $utilisateurPerms = [
            'dashboard.view.utilisateur',
            'incidents.view.own',
            'incidents.create',
            'incidents.close.own',
            'incidents.reopen.own',
            'kb.view',
            'reports.view', // garde ou enlève selon ta politique
        ];
        Role::findByName('utilisateur', $guard)->syncPermissions(
            Permission::whereIn('name', $utilisateurPerms)->get()
        );

        // Flush final du cache permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
