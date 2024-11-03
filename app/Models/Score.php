<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    protected $fillable = ['response_option_id', 'vocation_id', 'pontos'];

    public function opcaoResposta()
    {
        return $this->belongsTo(ResponseOption::class);
    }

    public function vocation()
    {
        return $this->belongsTo(Vocation::class);
    }
}
