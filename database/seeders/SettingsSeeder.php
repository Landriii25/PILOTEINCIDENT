<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['key' => 'company.name', 'value' => 'PiloteIncident'],
            ['key' => 'company.logo_url', 'value' => 'vendor/adminlte/dist/img/gs2eci_logo-r.png'],
            ['key' => 'notifications.email_enabled', 'value' => '1'],
            ['key' => 'notifications.realtime_enabled', 'value' => '0'],
        ];

        foreach ($rows as $r) {
            DB::table('settings')->updateOrInsert(['key' => $r['key']], [
                'value' => $r['value'],
                'updated_at' => now(),
                'created_at' => now(),
            ]);
        }
    }
}
