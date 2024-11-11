<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserQuestionStatus extends Model
{
    protected $fillable = [
        'number',
        'current_question'
    ];
}
