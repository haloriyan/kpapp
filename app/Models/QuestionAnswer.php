<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'question_id', 'course_id', 'answer', 'is_correct', 'quiz_id'
    ];
}
