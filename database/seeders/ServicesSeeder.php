<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ServicesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('services')->insert([
            [
                'nom' => 'Informatique',
                'description' => 'Service support IT & supervision',
                'chef_id' => null, // sera mis à jour après création du superviseur
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nom' => 'Réseaux',
                'description' => 'Service réseau & sécurité',
                'chef_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nom' => 'Applications',
                'description' => 'Maintien en condition des applications métiers',
                'chef_id' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
