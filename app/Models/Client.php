<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $table = 'client';

    protected $fillable = [
        'folio',
        'name',
        'phone',
        'date',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function observations()
    {
        return $this->hasMany(ClientObservation::class, 'client_id');
    }
}
