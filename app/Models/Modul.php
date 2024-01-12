<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modul extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id', 'title', 'description', 'priority'
    ];

    public function videos() {
        return $this->hasMany(ModulVideo::class, 'modul_id')->orderBy('priority', 'DESC')->orderBy('updated_at', 'DESC');
    }
    public function documents() {
        return $this->hasMany(ModulDocument::class, 'modul_id')->orderBy('priority', 'DESC')->orderBy('updated_at', 'DESC');
    }
}
