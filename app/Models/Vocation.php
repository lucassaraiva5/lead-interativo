<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vocation extends Model
{

    protected $fillable = ['nome', 'descricao'];

    public function perguntas()
    {
        return $this->belongsToMany(Question::class);
    }
}
