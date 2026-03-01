<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Conversation extends Model
{
    protected $fillable = [
        'phone',
        'instancia',
        'contact_name',
        'user_message',
        'bot_response',
        'status',
    ];
}
