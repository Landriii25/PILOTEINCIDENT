<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Commentaire extends Model
{
    use HasFactory;

    protected $fillable = [
        'incident_id',
        'user_id',
        'contenu',
    ];

    /**
     * Relation vers l'incident associé.
     */
    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }

    /**
     * Relation vers l'utilisateur qui a commenté.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
