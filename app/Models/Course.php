<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'title', 'description', 'cover_image', 'cover_video', 'price', 'category', 'presence_day_count',
        'start_date', 'end_date', 'minimum_completing_modul', 'minimum_correct_answer'
    ];

    public function medias() {
        return $this->hasMany(Media::class, 'course_id');
    }
    public function materials() {
        return $this->hasMany(Material::class, 'course_id');
    }
    public function moduls() {
        return $this->hasMany(Modul::class, 'course_id');
    }
    public function enrolls() {
        return $this->hasMany(Enroll::class, 'course_id');
    }
    public function exam_questions() {
        return $this->hasMany(Question::class, 'course_id');
    }
    public function certificate() {
        return $this->hasOne(Certificate::class, 'course_id');
    }
    public function quiz() {
        return $this->hasOne(Quiz::class, 'course_id');
    }
}
