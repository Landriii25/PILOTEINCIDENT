<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Incident;
use App\Models\Application;
use App\Models\Service;
use App\Models\User;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use Faker\Factory as Faker;

class IncidentsSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('fr_FR');

        $apps     = Application::query()->pluck('id')->all();
        $services = Service::query()->pluck('id')->all();

        // Demandeurs (tout le monde peut créer)
        $requesters = User::query()->pluck('id')->all();

        // Techniciens (si rôle existe), sinon fallback : n’importe quel user
        $techIds = User::role('technicien')->pluck('id')->all();
        if (empty($techIds)) {
            $techIds = $requesters;
        }

        if (empty($apps) || empty($services) || empty($requesters)) {
            $this->command->warn('⚠ IncidentsSeeder: apps/services/users insuffisants. Seeder ignoré.');
            return;
        }

        // Map SLA par priorité
        $slaHours = [
            'Critique' => 4,
            'Haute'    => 8,
            'Moyenne'  => 24,
            'Basse'    => 72,
        ];

        $priorites = array_keys($slaHours);
        $statuts   = ['Ouvert', 'En cours', 'Résolu'];

        $count = 60; // nombre d’incidents à générer

        for ($i = 1; $i <= $count; $i++) {
            $appId     = Arr::random($apps);
            $serviceId = Arr::random($services);
            $userId    = Arr::random($requesters);
            $techId    = Arr::random($techIds);

            $priorite = Arr::random($priorites);
            $statut   = Arr::random($statuts);

            // Création entre J-45 et J-1, horaires réalistes
            $createdAt = Carbon::now()->subDays(rand(1, 45))->setTime(rand(8, 18), rand(0, 59));

            // SLA = created_at + SLA_hours (+/- 0..6h pour varier)
            $dueAt = (clone $createdAt)->addHours($slaHours[$priorite] + rand(-2, 6));

            // Optionnel : incidents résolus
            $resolvedAt = null;
            if ($statut === 'Résolu') {
                // Résolution entre +1h et +72h
                $resolvedAt = (clone $createdAt)->addHours(rand(1, 72));
                if ($resolvedAt->lessThan($createdAt)) {
                    $resolvedAt = (clone $createdAt)->addHours(1);
                }
            }

            // Création SANS définir "code" → auto‑généré par le model
            Incident::create([
                'titre'          => 'Incident #' . $faker->numberBetween(10, 999),
                'description'    => $faker->sentence(8) . ' — ' . $faker->sentence(10),
                'application_id' => $appId,
                'service_id'     => $serviceId,
                'user_id'        => $userId,
                'technicien_id'  => $techId,
                'priorite'       => $priorite,
                'statut'         => $statut,
                'due_at'         => $dueAt,
                'created_at'     => $createdAt,
                'updated_at'     => $resolvedAt ?? $createdAt,
                'resolved_at'    => $resolvedAt,
                // 'code' => (NE PAS RENSEIGNER)
            ]);
        }

        $this->command->info("✅ $count incidents générés sans doublon de code.");
    }
}
