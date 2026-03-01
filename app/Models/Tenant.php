<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    /**
     * Campos personalizados almacenados en la columna JSON `data` de la tabla tenants.
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'nombre',
            'slug',
            'db_driver',
            'db_name',
        ];
    }

    /**
     * Instancias de Evolution API que pertenecen a este tenant.
     */
    public function instances(): HasMany
    {
        return $this->hasMany(TenantInstance::class, 'tenant_id', 'id');
    }

    /**
     * Usuarios vinculados a este tenant (en la BD central).
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'tenant_id', 'id');
    }

    /**
     * Nombre de la BD del tenant: usa db_name si está definido,
     * o genera automáticamente "tenant_{slug}".
     */
    public function getDatabaseName(): string
    {
        return $this->db_name ?? ('tenant_' . $this->slug);
    }
}
