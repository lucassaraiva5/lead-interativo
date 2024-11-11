<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserQuestionStatus extends Model
{
    protected $fillable = [
        'number',
        'name',
        'image_sent',
        'image_generated',
        'current_question'
    ];
}
