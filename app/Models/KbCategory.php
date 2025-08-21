<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KbCategory extends Model
{
    use HasFactory;

    protected $fillable = ['nom','slug','description' ,'position'];

    public function articles()
    {
        return $this->hasMany(KbArticle::class, 'kb_category_id');
    }
}
