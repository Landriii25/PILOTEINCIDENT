<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $table = 'reports';
    protected $guarded = [];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at'   => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Un rapport appartient à un incident
    public function incident()
    {
        return $this->belongsTo(Incident::class, 'incident_id');
    }

    // Auteur (technicien généralement)
    public function author()
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
