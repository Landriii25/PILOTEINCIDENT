<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            ServicesSeeder::class,
            UsersSeeder::class,
            ApplicationsSeeder::class,
            IncidentsSeeder::class,
            CommentairesSeeder::class,
            KbCategoriesSeeder::class,
            KbArticlesSeeder::class,
            SettingsSeeder::class,
        ]);
    }
}
