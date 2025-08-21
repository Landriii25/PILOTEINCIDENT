<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;  // Assuming you are using Spatie's Permission package for roles and permissions

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'title',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relation vers les incidents créés par l'utilisateur.
     */
    public function commentaires()
    {
        return $this->hasMany(Commentaire::class);
    }

    public function incidents()
    {
        return $this->hasMany(Incident::class, 'user_id');
    }

    /**
     * Relation vers le service de l'utilisateur.
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    // Assuming the User model has a service_id foreign key
    }

    // Avatar UI-Avatars (https://ui-avatars.com)
    public function getAvatarUrlAttribute(): string
    {
        $name = urlencode($this->name ?? 'U');
        return "https://ui-avatars.com/api/?name={$name}&size=96&background=4f46e5&color=ffffff&format=png";
    }
}
