<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantInstance extends Model
{
    /**
     * Siempre usa la conexión central (landlord).
     * Esta tabla vive en la BD principal, no en la BD del tenant.
     */
    protected $connection = 'mysql';

    protected $table = 'tenant_instances';

    protected $fillable = [
        'tenant_id',
        'instance_name',
        'descripcion',
        'activo',
        'is_default',
    ];

    protected $casts = [
        'activo'     => 'boolean',
        'is_default' => 'boolean',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }
}
