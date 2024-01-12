<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presence extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'enroll_id', 'presence_date', 'location', 'checked_in'
    ];

    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function enroll() {
        return $this->belongsTo(Enroll::class, 'enroll_id');
    }
}
