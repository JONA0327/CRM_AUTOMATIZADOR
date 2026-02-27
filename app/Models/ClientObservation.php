<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClientObservation extends Model
{
    protected $table = 'client_observations';

    protected $fillable = [
        'client_id',
        'weight',
        'age',
        'observation',
        'suggested_products',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'age'    => 'integer',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
