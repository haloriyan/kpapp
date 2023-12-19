<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id', 'filename', 'font_properties', 'position'
    ];

    public function course() {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
