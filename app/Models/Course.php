<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'title', 'description', 'cover_image', 'cover_video', 'price', 'category'
    ];

    public function medias() {
        return $this->hasMany(Media::class, 'course_id');
    }
    public function materials() {
        return $this->hasMany(Material::class, 'course_id');
    }
    public function enrolls() {
        return $this->hasMany(Enroll::class, 'course_id');
    }
}
