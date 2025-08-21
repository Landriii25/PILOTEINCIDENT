<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Application extends Model
{
    protected $guarded = [];

    public function service()
    {
        return $this->belongsTo(\App\Models\Service::class);
    }

    // URL publique du logo (ou placeholder)
    public function getLogoUrlAttribute(): string
    {
        if ($this->logo_path && Storage::disk('public')->exists($this->logo_path)) {
            return Storage::url($this->logo_path);
        }
        // placeholder simple
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->nom) . '&size=128&background=EEF2FF&color=334155';
    }

    // URL publique de la miniature
    public function getThumbUrlAttribute(): string
    {
        if ($this->thumb_path && Storage::disk('public')->exists($this->thumb_path)) {
            return Storage::url($this->thumb_path);
        }
        // fallback sur le logo principal
        return $this->logo_url;
    }
}
