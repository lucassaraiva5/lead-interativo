<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserResponse extends Model
{
    protected $fillable = ['questionary_id', 'question_id', 'response_option_id'];

    public function questionary()
    {
        return $this->belongsTo(Questionary::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function responseOption()
    {
        return $this->belongsTo(ResponseOption::class);
    }
}
