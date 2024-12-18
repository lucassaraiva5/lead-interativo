<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserQuestionStatus extends Model
{
    protected $fillable = [
        'number',
        'name',
        'image_sent',
        'vocation',
        'current_random_question',
        'instagram',
        'school',
        'questionary_id',
        'image_generated',
        'current_question'
    ];
}
