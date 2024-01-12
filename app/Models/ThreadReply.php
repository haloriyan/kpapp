<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThreadReply extends Model
{
    use HasFactory;

    protected $fillable = [
        'thread_id', 'user_id', 'course_id',
        'body', 'image',
        'upvote_count', 'downvote_count',
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function thread() {
        return $this->belongsTo(Thread::class, 'thread_id');
    }
}
