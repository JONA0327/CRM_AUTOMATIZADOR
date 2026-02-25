<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Configuracion extends Model
{
    protected $table = 'configuraciones';

    protected $fillable = ['clave', 'valor', 'grupo', 'descripcion'];

    /**
     * Obtiene y descifra el valor de una clave.
     * Retorna $default si la clave no existe o no tiene valor.
     */
    public static function get(string $clave, ?string $default = null): ?string
    {
        $registro = static::where('clave', $clave)->first();

        if (! $registro || ! $registro->valor) {
            return $default;
        }

        try {
            return Crypt::decryptString($registro->valor);
        } catch (\Exception) {
            return $default;
        }
    }

    /**
     * Cifra y guarda (o actualiza) el valor de una clave.
     */
    public static function set(
        string $clave,
        string $valor,
        string $grupo = 'general',
        string $descripcion = ''
    ): void {
        static::updateOrCreate(
            ['clave' => $clave],
            [
                'valor'       => Crypt::encryptString($valor),
                'grupo'       => $grupo,
                'descripcion' => $descripcion,
            ]
        );
    }

    /**
     * Elimina (borra) el valor de una clave, dejando la fila con valor null.
     */
    public static function clear(string $clave): void
    {
        static::where('clave', $clave)->update(['valor' => null]);
    }

    /**
     * Indica si una clave tiene un valor guardado (no nulo).
     */
    public static function isConfigured(string $clave): bool
    {
        return static::where('clave', $clave)->whereNotNull('valor')->exists();
    }
}
