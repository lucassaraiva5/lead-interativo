<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $fillable = [
        'from',
        'to',
        'body',
        'processed',
        'received_at'
    ];
}
