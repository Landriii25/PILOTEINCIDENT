<?php

namespace App\Notifications;

use App\Models\Incident;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class NouvelIncidentNotification extends Notification
{
    use Queueable;

    public Incident $incident;

    /**
     * Crée une nouvelle instance de notification.
     */
    public function __construct(Incident $incident)
    {
        $this->incident = $incident;
    }

    /**
     * Définit les canaux de livraison de la notification.
     * Ici, on notifie seulement dans la base de données (pour l'icône cloche).
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Formate la notification pour qu'elle soit stockée dans la base de données.
     */
    public function toDatabase(object $notifiable): array
    {
        // Le créateur de l'incident
        $creatorName = $this->incident->user->name ?? 'Un utilisateur';

        // Ces données seront stockées au format JSON
        return [
            'incident_id'   => $this->incident->id,
            'incident_code' => $this->incident->code,
            'title'         => "Nouvel incident : {$this->incident->titre}",
            'user_name'     => $creatorName,
        ];
    }
}
