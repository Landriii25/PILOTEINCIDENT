<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Service extends Model
{
    use HasFactory;

    protected $guarded = [];

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
