<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Answer extends Model
{
    protected $fillable = ['questionary_id', 'question_id', 'value'];

    public function questionario()
    {
        return $this->belongsTo(Questionary::class);
    }

    public function pergunta()
    {
        return $this->belongsTo(Question::class);
    }
}
