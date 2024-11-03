<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    protected $fillable = ['conteudo'];

    public function vocations()
    {
        return $this->belongsToMany(Vocation::class);
    }

    public function responseOptions()
    {
        return $this->hasMany(ResponseOption::class);
    }
}
