<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Service;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        // ─────────────────────────────────────────────────────────────
        // 1) Services (créés s'ils n'existent pas)
        // ─────────────────────────────────────────────────────────────
        $it = Service::firstOrCreate(
            ['nom' => 'Service IT'],
            ['description' => 'Support & Exploitation', 'chef_id' => null]
        );

        $metier = Service::firstOrCreate(
            ['nom' => 'Service Métier'],
            ['description' => 'Pôle applicatif métier', 'chef_id' => null]
        );

        // ─────────────────────────────────────────────────────────────
        // 2) Utilisateurs
        // NB: Assure-toi que ton modèle User utilise le trait:
        // use Spatie\Permission\Traits\HasRoles;
        // ─────────────────────────────────────────────────────────────

        // Admin global (pas rattaché à un service obligatoirement)
        $admin = User::firstOrCreate(
            ['email' => 'admin@piloteincident.test'],
            [
                'name'              => 'Admin Système',
                'password'          => Hash::make('password'),
                'title'             => 'Administrateur',
                'service_id'        => null,
                'email_verified_at' => now(),
                'remember_token'    => Str::random(10),
            ]
        );
        if (! $admin->hasRole('admin')) {
            $admin->assignRole('admin');
        }

        // Superviseur (chef de Service IT)
        $superviseur = User::firstOrCreate(
            ['email' => 'superviseur.it@piloteincident.test'],
            [
                'name'              => 'Claire Superviseur',
                'password'          => Hash::make('password'),
                'title'             => 'Superviseur',
                'service_id'        => $it->id,
                'email_verified_at' => now(),
                'remember_token'    => Str::random(10),
            ]
        );
        if (! $superviseur->hasRole('superviseur')) {
            $superviseur->assignRole('superviseur');
        }

        // Met à jour le chef du service IT si absent (optionnel)
        if (is_null($it->chef_id)) {
            $it->chef_id = $superviseur->id;
            $it->save();
        }

        // Technicien (Service IT)
        $technicien = User::firstOrCreate(
            ['email' => 'tech.it@piloteincident.test'],
            [
                'name'              => 'Marc Technicien',
                'password'          => Hash::make('password'),
                'title'             => 'Technicien N2',
                'service_id'        => $it->id,
                'email_verified_at' => now(),
                'remember_token'    => Str::random(10),
            ]
        );
        if (! $technicien->hasRole('technicien')) {
            $technicien->assignRole('technicien');
        }

        // Utilisateur (Service Métier)
        $utilisateur = User::firstOrCreate(
            ['email' => 'user.metier@piloteincident.test'],
            [
                'name'              => 'Nadia Utilisatrice',
                'password'          => Hash::make('password'),
                'title'             => 'Chargée d\'application',
                'service_id'        => $metier->id,
                'email_verified_at' => now(),
                'remember_token'    => Str::random(10),
            ]
        );
        if (! $utilisateur->hasRole('utilisateur')) {
            $utilisateur->assignRole('utilisateur');
        }
    }
}
