<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApplicationsSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('applications')->insert([
            [
                'nom'         => 'SAPHIRV3 CIE PROD',
                'description' => 'CRM client CIE',
                'statut'      => 'Actif',
                'service_id'  => 3,           // adapte aux IDs existants
                'logo_path'   => null,        // pas d’image au seed
                'thumb_path'  => null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'nom'         => 'Portail RH',
                'description' => 'Gestion des congés et des présences',
                'statut'      => 'Actif',
                'service_id'  => 1,
                'logo_path'   => null,
                'thumb_path'  => null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
            [
                'nom'         => 'Monitoring Zabbix',
                'description' => 'Supervision infrastructure',
                'statut'      => 'Actif',
                'service_id'  => 1,
                'logo_path'   => null,
                'thumb_path'  => null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ],
        ]);
    }
}
