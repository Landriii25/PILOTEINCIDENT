<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class IncidentActivity extends Model
{
    use HasFactory;

    protected $fillable = ['incident_id','user_id','action','meta'];
    protected $casts = ['meta' => 'array'];

    public function incident(){ return $this->belongsTo(Incident::class); }
    public function user(){ return $this->belongsTo(User::class); }

    public static function log(int $incidentId, ?int $userId, string $action, array $meta = []): self
    {
        return self::create([
            'incident_id' => $incidentId,
            'user_id'     => $userId,
            'action'      => $action,
            'meta'        => empty($meta) ? null : $meta,
        ]);
    }
}
