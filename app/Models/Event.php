<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id', 'title', 'description', 'cover',
        'start_date', 'start_time', 'end_date', 'end_time', 'join_rule', 'stream_url'
    ];

    public function course() {
        return $this->belongsTo(Course::class, 'course_id');
    }
    public function users() {
        return $this->hasMany(EventUser::class, 'event_id');
    }
}
