<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'course_id', 'coupon_id', 'payment_status', 'is_completed'
    ];

    public function course() {
        return $this->belongsTo(Course::class, 'course_id');
    }
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function paths() {
        return $this->hasMany(Path::class, 'enroll_id');
    }
}
