<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Service extends Model
{
    use HasFactory, LogsActivity;

    protected $guarded = [];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('application')
            ->logOnly(['nom','description','statut','service_id','logo_path','thumb_path'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn($e) => "Application {$this->nom} {$e}");
    }
    /* ==================== Relations ===================== */
    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function chef()
    {
        return $this->belongsTo(User::class, 'chef_id');
    }

    public function superviseurs()
    {
        // si tes Users ont service_id
        return $this->hasMany(User::class)->whereHas('roles', fn($q) => $q->where('name', 'superviseur'));
    }
    public function techniciens()
    {
        // si tes Users ont service_id
        return $this->hasMany(User::class)->whereHas('roles', fn($q) => $q->where('name', 'technicien'));
    }
}
