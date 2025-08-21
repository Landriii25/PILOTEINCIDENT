<?php

namespace App\Notifications;

use App\Models\Incident;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class IncidentCommentedNotification extends Notification
{
    use Queueable;

    public function __construct(public Incident $incident, public string $auteur, public string $extrait) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Nouveau commentaire sur {$this->incident->code}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("{$this->auteur} a ajouté un commentaire sur l’incident {$this->incident->code}.")
            ->line("« {$this->extrait} »")
            ->action('Voir l’incident', route('incidents.show', $this->incident->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'incident_id' => $this->incident->id,
            'code'        => $this->incident->code,
            'auteur'      => $this->auteur,
            'extrait'     => $this->extrait,
            'url'         => route('incidents.show', $this->incident->id),
            'message'     => "Nouveau commentaire sur {$this->incident->code}",
        ];
    }
}
