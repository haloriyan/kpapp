<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ModulDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'modul_id', 'filename', 'type', 'priority',
        'title', 'size'
    ];
}
