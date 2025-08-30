<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;



class Incident extends Model
{
    use HasFactory, LogsActivity;

    protected $guarded = [];

    public const PRIORITES = ['Critique','Haute','Moyenne','Basse'];
    public const SLA_HOURS = [
        'Critique' => 4,
        'Haute'    => 8,
        'Moyenne'  => 24,
        'Basse'    => 72,
    ];

    protected $casts = [
        'resolved_at' => 'datetime',
        'due_at'      => 'datetime',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    /* ======================= Boot ======================= */
    protected static function boot()
    {
        parent::boot();

        static::creating(function (self $incident) {
            // Détermine la date de référence (création fournie ? sinon maintenant)
            $ref = $incident->created_at ?? now();

            // Génère un code unique si absent
            if (empty($incident->code)) {
                $incident->code = static::generateCodeFromDate($ref);
            }

            // Statut par défaut
            if (empty($incident->statut)) {
                $incident->statut = 'Ouvert';
            }

            // due_at à partir de la priorité et de la date de ref
            if (!empty($incident->priorite) && isset(self::SLA_HOURS[$incident->priorite]) && empty($incident->due_at)) {
                $incident->due_at = (clone $ref)->addHours(self::SLA_HOURS[$incident->priorite]);
            }
        });

        static::saving(function (self $incident) {
            // Si on passe à "Résolu" et qu'aucune date n'est encore posée, on la met maintenant
            if (($incident->statut ?? null) === 'Résolu' && is_null($incident->resolved_at)) {
                $incident->resolved_at = now();
            }
            // Ne pas effacer resolved_at automatiquement en cas de réouverture : on laisse la logique métier le faire explicitement.
        });
    }

    /**
     * Génère un code unique du type INCYYMM-#### basé sur la date donnée.
     */
    public static function generateCodeFromDate(\DateTimeInterface $date): string
    {
        $prefix = 'INC' . $date->format('ym');

        // Récupère le dernier code existant pour ce préfixe et incrémente le suffixe
        $last = static::where('code', 'like', $prefix.'-%')
            ->orderByDesc('id')   // plus fiable/rapide que sur la colonne code
            ->first();

        $n = 1;
        if ($last && preg_match('/^'.$prefix.'-(\d{4})$/', $last->code, $m)) {
            $n = (int)$m[1] + 1;
        }

        return $prefix . '-' . str_pad((string)$n, 4, '0', STR_PAD_LEFT);
    }

    /* ==================== Relations ===================== */
    public function application()
    {
        return $this->belongsTo(\App\Models\Application::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function technicien()
    {
        return $this->belongsTo(\App\Models\User::class, 'technicien_id');
    }

    public function commentaires()
    {
        return $this->hasMany(\App\Models\Commentaire::class);
    }

    public function report()
    {
        return $this->hasOne(\App\Models\Report::class, 'incident_id');
    }

    /* ====================== Scopes ====================== */
    public function scopeOpen($q)
    {
        return $q->whereNull('resolved_at');
    }

    public function scopeResolved($q)
    {
        return $q->whereNotNull('resolved_at');
    }

    public function scopeSlaAtRisk($q, int $hours = 4)
    {
        $now = now();
        return $q->whereNull('resolved_at')
            ->whereNotNull('due_at')
            ->where(function ($qq) use ($now, $hours) {
                $qq->where('due_at', '<=', $now)
                   ->orWhereBetween('due_at', [$now, $now->copy()->addHours($hours)]);
            });
    }

    /* ==================== Accessors ===================== */
    public function getIsLateAttribute(): bool
    {
        return $this->due_at && now()->gt($this->due_at) && is_null($this->resolved_at);
    }

    public function getSlaRemainingForHumansAttribute(): ?string
    {
        if (!$this->due_at) return null;
        return $this->due_at->diffForHumans(null, true, true);
    }

      public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('incident')
            ->logOnly([
                'code', 'titre', 'description', 'priorite', 'statut',
                'application_id', 'service_id', 'user_id', 'technicien_id',
                'due_at', 'resolved_at'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Incident {$this->code} {$eventName}");

    }
}
