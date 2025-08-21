<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class KbArticlesSeeder extends Seeder
{
    public function run(): void
    {
        $catApps = DB::table('kb_categories')->where('nom','Applications métiers')->value('id');
        $catSup  = DB::table('kb_categories')->where('nom','Supervision')->value('id');

        DB::table('kb_articles')->insert([
            [
                'kb_category_id' => $catApps,
                'title'   => 'Erreur 500 SAPHIRV3 - Procédure',
                'slug'    => Str::slug('Erreur 500 SAPHIRV3 - Procédure').'-'.Str::random(6),
                'summary' => 'Étapes pour diagnostiquer et corriger les erreurs 500 sur SAPHIRV3.',
                'content' => "1) Vérif logs app\n2) Status DB\n3) Clear cache\n4) Redéployer si besoin",
                'tags'    => json_encode(['saphir','500','app']),
                'is_published' => 1,
                'created_at'=> now(),
                'updated_at'=> now(),
            ],
            [
                'kb_category_id' => $catSup,
                'title'   => 'Aucune alerte Zabbix',
                'slug'    => Str::slug('Aucune alerte Zabbix').'-'.Str::random(6),
                'summary' => 'Résoudre l’absence d’alertes remontées par Zabbix.',
                'content' => "1) Vérif SMTP/Media types\n2) Test action\n3) Agent pings\n4) Eskalation",
                'tags'    => json_encode(['zabbix','alerting']),
                'is_published' => 1,
                'created_at'=> now(),
                'updated_at'=> now(),
            ],
            [
                'kb_category_id' => $catApps,
                'title'   => 'Problème de connexion au Portail RH',
                'slug'    => Str::slug('Problème de connexion au Portail RH').'-'.Str::random(6),
                'summary' => 'Guide pour résoudre les problèmes de connexion au Portail RH.',
                'content' => "1) Vérif identifiants\n2) Réinitialiser mot de passe\n3) Vérifier l’état du service",
                'tags'    => json_encode(['portail','rh','connexion']),
                'is_published' => 1,
                'created_at'=> now(),
                'updated_at'=> now(),
            ],
        ]);
    }
}
