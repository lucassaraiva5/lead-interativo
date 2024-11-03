<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ResponseOption extends Model
{
    protected $fillable = ['question_id', 'conteudo'];

    public function pergunta()
    {
        return $this->belongsTo(Question::class);
    }

    public function pontuacoes()
    {
        return $this->hasMany(Score::class);
    }
}
