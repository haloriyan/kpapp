<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModulVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'modul_id', 'title', 'filename', 'thumbnail', 'duration', 'priority'
    ];
}
