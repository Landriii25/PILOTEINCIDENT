<?php

namespace App\Notifications;

use App\Models\Incident;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue; // optionnel si tu queues
use Illuminate\Notifications\Messages\MailMessage;

class IncidentResolvedNotification extends Notification // implements ShouldQueue
{
    use Queueable;

    public function __construct(public Incident $incident) {}

    public function via($notifiable): array
    {
        return ['database', 'mail']; // retire 'mail' si tu ne veux que la base
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Incident {$this->incident->code} résolu")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("L’incident {$this->incident->code} a été marqué comme résolu par {$this->incident->technicien?->name}.")
            ->line("Titre : {$this->incident->titre}")
            ->action('Voir l’incident', route('incidents.show', $this->incident))
            ->line('Vous pouvez clôturer l’incident ou le renvoyer au technicien si nécessaire.');
    }

    public function toArray($notifiable): array
    {
        return [
            'incident_id' => $this->incident->id,
            'code'        => $this->incident->code,
            'titre'       => $this->incident->titre,
            'message'     => 'Incident résolu. Clôturez ou renvoyez au technicien.',
            'url'         => route('incidents.show', $this->incident),
        ];
    }
}
