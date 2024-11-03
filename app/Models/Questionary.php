<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Questionary extends Model
{

    protected $fillable = ['user_id'];

    public function respostas()
    {
        return $this->hasMany(Answer::class);
    }

    public function userResponses()
    {
        return $this->hasMany(UserResponse::class);
    }
}
