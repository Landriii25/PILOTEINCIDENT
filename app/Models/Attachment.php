<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    protected $fillable = [
        'incident_id',
        'chemin_fichier',
        'nom_original',
    ];

    // Relation avec l'incident
    public function incident()
    {
        return $this->belongsTo(Incident::class);
    }
}
