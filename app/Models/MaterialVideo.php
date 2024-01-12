<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialVideo extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_id', 'course_id', 'filename', 'duration', 'thumbnail', 'priority'
    ];

    public function material() {
        return $this->belongsTo(Material::class, 'material_id');
    }
    public function course() {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
