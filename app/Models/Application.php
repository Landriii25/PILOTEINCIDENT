<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
class Application extends Model
{
    use LogsActivity;
    protected $guarded = [];

    protected $appends = ['logo_url', 'thumb_url'];
    protected $hidden  = ['logo_path', 'thumb_path'];
    protected $casts   = [
        'service_id' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('application')
            ->logOnly(['nom','description','statut','service_id','logo_path','thumb_path'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn($e) => "Application {$this->nom} {$e}");
    }
    /* ==================== Relations ===================== */
    public function service()
    {
        return $this->belongsTo(\App\Models\Service::class);
    }

    public function getLogoUrlAttribute(): string
    {
        if ($this->logo_path) {
            return Storage::url($this->logo_path);
        }
        return 'https://ui-avatars.com/api/?name=' . urlencode($this->nom) . '&size=128&background=EEF2FF&color=334155';
    }

    public function getThumbUrlAttribute(): string
    {
        if ($this->thumb_path) {
            return Storage::url($this->thumb_path);
        }
        return $this->logo_url;
    }
}
