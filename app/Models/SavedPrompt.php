<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SavedPrompt extends Model
{
    protected $fillable = ['nombre', 'contenido'];
}
