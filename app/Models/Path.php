<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Path extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'enroll_id', 'course_id', 'material_id'
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function enroll() {
        return $this->belongsTo(Enroll::class, 'enroll_id');
    }
    public function course() {
        return $this->belongsTo(Course::class, 'course_id');
    }
    public function material() {
        return $this->belongsTo(Material::class, 'material_id');
    }
}
