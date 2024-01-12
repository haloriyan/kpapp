<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnrollPath extends Model
{
    use HasFactory;

    protected $fillable = [
        'enroll_id', 'course_id', 'modul_id', 'user_id', 'is_complete'
    ];

    public function enroll() {
        return $this->belongsTo(Enroll::class, 'enroll_id');
    }
    public function modul() {
        return $this->belongsTo(Modul::class, 'modul_id');
    }
    public function course() {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
