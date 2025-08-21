<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class KbCategoriesSeeder extends Seeder
{
    public function run(): void
    {
        // Catégories (type d'incident)
        $cats = [
            'Matériel',
            'Logiciel',
            'Réseaux',
            'Sécurité',
            'Systèmes',
            'Bases de données',
            'Applications métiers',
        ];

        foreach ($cats as $idx => $nom) {
            // Slug de base
            $base = Str::slug($nom);
            $slug = $base;

            // Assure une unicité simple en suffixant si le slug existe déjà
            $i = 1;
            while (DB::table('kb_categories')->where('slug', $slug)->exists()) {
                $slug = $base.'-'.$i++;
            }

            // upsert par slug (si existe => met à jour la description/position)
            DB::table('kb_categories')->updateOrInsert(
                ['slug' => $slug],
                [
                    'nom'         => $nom,
                    'description' => 'Catégorie pour '.Str::lower($nom),
                    'position'    => $idx,
                    'updated_at'  => now(),
                    'created_at'  => now(),
                ]
            );
        }
    }
}
