<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Thread extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id', 'user_id', 'title', 'body',
        'upvote_count', 'downvote_count', 'comments_count'
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function course() {
        return $this->belongsTo(Course::class, 'course_id');
    }
}
