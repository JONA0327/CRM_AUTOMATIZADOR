<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecondaryData extends Model
{
    use HasFactory;

    // Especificar la conexión de base de datos PostgreSQL
    protected $connection = 'pgsql_secondary';

    // Nombre de la tabla
    protected $table = 'secondary_data';

    protected $fillable = [
        'name',
        'description',
        'data',
        'is_active'
    ];

    protected $casts = [
        'data' => 'array',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Ejemplo de relación con modelo de otra BD
     * Nota: Las relaciones entre diferentes BD son limitadas
     */
    public function relatedUser()
    {
        // Para relacionar con otra BD, necesitarías hacer consultas manuales
        // o usar técnicas avanzadas como foreign keys entre servidores
        return User::on('mysql')->find($this->user_id);
    }
}
