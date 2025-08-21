<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use App\Models\Incident;
use App\Models\Commentaire;
use App\Models\User;
use Carbon\Carbon;

class CommentairesSeeder extends Seeder
{
    public function run(): void
    {
        $allUsers = User::pluck('id')->all();

        Incident::all()->each(function ($incident) use ($allUsers) {

            $nbComments = rand(0, 3);

            for ($i = 0; $i < $nbComments; $i++) {
                // Auteur aléatoire : créateur, technicien ou un autre user
                $possibleAuthors = array_filter([
                    $incident->user_id,
                    $incident->technicien_id,
                    Arr::random($allUsers)
                ]);
                $authorId = Arr::random($possibleAuthors);

                // Date du commentaire entre création de l’incident et maintenant
                $commentDate = Carbon::parse($incident->created_at)
                    ->addHours(rand(1, 72))
                    ->min(now());

                Commentaire::create([
                    'incident_id' => $incident->id,
                    'user_id'     => $authorId,
                    'contenu'     => Arr::random([
                        'Je prends en charge le ticket.',
                        'Analyse en cours.',
                        'Problème identifié, en attente de correctif.',
                        'Incident résolu, merci de confirmer.',
                        'Veuillez vérifier si le problème persiste.',
                        'Réouverture suite à retour utilisateur.'
                    ]),
                    'created_at'  => $commentDate,
                    'updated_at'  => $commentDate,
                ]);
            }
        });
    }
}
