<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Laravel\Sanctum\HasApiTokens;


class User extends Authenticatable
{
    use HasFactory, Notifiable, HasRoles, LogsActivity, HasApiTokens;

    protected $fillable = [
        'name',
        'title',
        'email',
        'password',
        'service_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relations
    public function commentaires()
    {
        return $this->hasMany(Commentaire::class);
    }

    public function incidents()
    {
        return $this->hasMany(Incident::class, 'user_id');
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    // Avatar via UI-Avatars
    public function getAvatarUrlAttribute(): string
    {
        $name = urlencode($this->name ?? 'U');
        return "https://ui-avatars.com/api/?name={$name}&size=96&background=4f46e5&color=ffffff&format=png";
    }

    // Spatie Activitylog
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('user')
            ->logOnly(['name','email','title','service_id'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "Utilisateur {$this->name} {$eventName}");
    }
}
