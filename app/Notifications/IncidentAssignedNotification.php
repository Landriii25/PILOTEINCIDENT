<?php

namespace App\Notifications;

use App\Models\Incident;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class IncidentAssignedNotification extends Notification
{
    use Queueable;

    public function __construct(public Incident $incident) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database']; // mail + in-app
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Nouvel incident assigné : {$this->incident->code}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Un incident vient de vous être assigné : {$this->incident->titre} ({$this->incident->priorite}).")
            ->action('Voir l’incident', route('incidents.show', $this->incident->id))
            ->line('Merci d’intervenir avant l’échéance SLA.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'incident_id' => $this->incident->id,
            'code'        => $this->incident->code,
            'titre'       => $this->incident->titre,
            'priorite'    => $this->incident->priorite,
            'url'         => route('incidents.show', $this->incident->id),
            'message'     => "Incident {$this->incident->code} assigné.",
        ];
    }
}
