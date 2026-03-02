<?php

namespace App\Services;

use App\Models\Configuracion;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExternalDbService
{
    private const CONN = 'ext_db';

    // ─────────────────────────────────────────────────────────────────────────
    // PÚBLICOS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Registra la conexión dinámica para las credenciales dadas y verifica que sea alcanzable.
     *
     * @throws \RuntimeException con mensaje legible si falla
     */
    public function conectar(array $creds): bool
    {
        Config::set('database.connections.' . self::CONN, $this->buildConfig($creds));
        DB::purge(self::CONN);
        DB::reconnect(self::CONN);

        try {
            if ($creds['driver'] === 'mongodb') {
                DB::connection(self::CONN)->command(['ping' => 1]);
            } else {
                DB::connection(self::CONN)->statement('SELECT 1');
            }
            return true;
        } catch (\Throwable $e) {
            Log::error('[ExternalDb] Fallo de conexión: ' . $e->getMessage());
            throw new \RuntimeException('No se pudo conectar: ' . $e->getMessage());
        }
    }

    /**
     * Lista tablas/colecciones disponibles.
     * Requiere haber llamado conectar() previamente.
     */
    public function listarTablas(): array
    {
        $driver = Config::get('database.connections.' . self::CONN . '.driver');

        if ($driver === 'mysql')   return $this->tablasMysql();
        if ($driver === 'pgsql')   return $this->tablasPostgres();
        if ($driver === 'mongodb') return $this->tablasMongo();
        return [];
    }

    /**
     * Devuelve el esquema completo: { tabla => [columna, ...] } para todas las tablas.
     * Requiere haber llamado conectar() previamente.
     *
     * @return array<string, string[]>
     */
    public function listarEsquemaCompleto(): array
    {
        $driver = Config::get('database.connections.' . self::CONN . '.driver');
        $dbName = Config::get('database.connections.' . self::CONN . '.database');
        $tablas = $this->listarTablas();
        $esquema = [];

        foreach ($tablas as $tabla) {
            try {
                if ($driver === 'mongodb') {
                    $doc = DB::connection(self::CONN)->collection($tabla)->first();
                    $esquema[$tabla] = $doc ? array_keys((array) $doc) : [];
                } else {
                    $esquema[$tabla] = $this->columnasSql($driver, $dbName, $tabla);
                }
            } catch (\Exception) {
                $esquema[$tabla] = [];
            }
        }

        return $esquema;
    }

    /**
     * Devuelve columnas + muestra de filas de una tabla.
     * Si $columnasFiltro no está vacío, solo devuelve esas columnas.
     *
     * @return array{columnas: string[], filas: array<int,array>}
     */
    public function datosTabla(string $tabla, int $limit = 10, array $columnasFiltro = []): array
    {
        $driver = Config::get('database.connections.' . self::CONN . '.driver');
        $conn   = DB::connection(self::CONN);

        if ($driver === 'mongodb') {
            $query = $conn->collection($tabla)->limit($limit);
            if (!empty($columnasFiltro)) {
                $query = $query->project(array_fill_keys($columnasFiltro, 1));
            }
            $filas    = $query->get()->toArray();
            $columnas = $filas ? array_keys((array) $filas[0]) : $columnasFiltro;
            $filas    = array_map(fn($f) => $this->normalizarMongo((array) $f), $filas);
        } else {
            $dbName   = Config::get('database.connections.' . self::CONN . '.database');
            $todasCols = $this->columnasSql($driver, $dbName, $tabla);

            if (!empty($columnasFiltro)) {
                // Solo columnas que realmente existen en la tabla
                $columnas = array_values(array_intersect($columnasFiltro, $todasCols));
            } else {
                $columnas = $todasCols;
            }

            $query = $conn->table($tabla)->limit($limit);
            if (!empty($columnas)) {
                $query = $query->select($columnas);
            }
            $filas = array_map(fn($f) => (array) $f, $query->get()->toArray());
        }

        return ['columnas' => $columnas, 'filas' => $filas];
    }

    /**
     * Punto de entrada principal para el bot.
     * Lee TODAS las conexiones configuradas en `ext_dbs`, conecta a cada una
     * y genera el contexto concatenado con las tablas seleccionadas de cada BD.
     */
    public function construirContextoMultiple(): string
    {
        $conexiones = $this->leerConexiones();

        if (empty($conexiones)) {
            return '';
        }

        $bloques = [];

        foreach ($conexiones as $idx => $conn) {
            $tablas = array_filter(
                $conn['tablas'] ?? [],
                fn($t) => is_string($t) && preg_match('/^[a-zA-Z0-9_]+$/', $t)
            );

            if (empty($tablas)) {
                continue;
            }

            $nombre = $conn['nombre'] ?? ('BD ' . ($idx + 1));

            try {
                $this->conectar($conn);
            } catch (\Exception $e) {
                Log::warning("[ExternalDb] No se pudo conectar a '{$nombre}': " . $e->getMessage());
                $bloques[] = "=== {$nombre} ===\n(Error de conexión: {$e->getMessage()})";
                continue;
            }

            $tablasColumnas = $conn['tablas_columnas'] ?? [];
            $partes = ["=== DATOS: {$nombre} ==="];

            foreach (array_values($tablas) as $tabla) {
                try {
                    $colsFiltro = isset($tablasColumnas[$tabla]) && is_array($tablasColumnas[$tabla])
                        ? array_values($tablasColumnas[$tabla])
                        : [];
                    $datos = $this->datosTabla($tabla, 10, $colsFiltro);

                    if (empty($datos['columnas'])) {
                        continue;
                    }

                    $partes[] = "\n--- Tabla: {$tabla} ---";
                    $partes[] = 'Columnas: ' . implode(', ', $datos['columnas']);

                    if (! empty($datos['filas'])) {
                        $partes[] = 'Muestra (' . count($datos['filas']) . ' registros):';
                        foreach ($datos['filas'] as $fila) {
                            $celdas = [];
                            foreach ($fila as $col => $val) {
                                $celdas[] = $col . ': ' . (is_null($val) ? 'NULL' : (string) $val);
                            }
                            $partes[] = '  ' . implode(' | ', $celdas);
                        }
                    } else {
                        $partes[] = '(tabla vacía)';
                    }
                } catch (\Exception $e) {
                    Log::warning("[ExternalDb] Error leyendo {$nombre}.{$tabla}: " . $e->getMessage());
                    $partes[] = "(Error al leer tabla {$tabla})";
                }
            }

            $bloques[] = implode("\n", $partes);
        }

        return implode("\n\n", $bloques);
    }

    /**
     * Mantiene la firma anterior para compatibilidad (usada en BotController).
     * Ahora delega a construirContextoMultiple() ignorando los parámetros de la firma vieja.
     */
    public function construirContexto(array $tablasSeleccionadas = []): string
    {
        return $this->construirContextoMultiple();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVADOS
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Lee el array de conexiones desde la clave `ext_dbs` (cifrada).
     */
    private function leerConexiones(): array
    {
        $json = Configuracion::get('ext_dbs', '[]');
        $arr  = json_decode($json, true);
        return is_array($arr) ? $arr : [];
    }

    private function buildConfig(array $creds): array
    {
        $port = (int) ($creds['port'] ?: $this->defaultPort($creds['driver']));

        $base = [
            'driver'   => $creds['driver'],
            'host'     => $creds['host'],
            'port'     => $port,
            'database' => $creds['database'],
            'username' => $creds['username'] ?? '',
            'password' => $creds['password'] ?? '',
        ];

        if ($creds['driver'] === 'mysql') {
            return array_merge($base, [
                'charset'   => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix'    => '',
                'strict'    => false,
                'engine'    => null,
                'options'   => defined('PDO::ATTR_TIMEOUT') ? [\PDO::ATTR_TIMEOUT => 5] : [],
            ]);
        }

        if ($creds['driver'] === 'pgsql') {
            return array_merge($base, [
                'charset' => 'utf8',
                'prefix'  => '',
                'schema'  => 'public',
                'sslmode' => 'prefer',
                'options' => defined('PDO::ATTR_TIMEOUT') ? [\PDO::ATTR_TIMEOUT => 5] : [],
            ]);
        }

        if ($creds['driver'] === 'mongodb') {
            return [
                'driver'   => 'mongodb',
                'dsn'      => $this->mongodsn($creds, $port),
                'database' => $creds['database'],
            ];
        }

        return $base;
    }

    private function mongodsn(array $creds, int $port): string
    {
        $auth = ($creds['username'] ?? '') !== ''
            ? urlencode($creds['username']) . ':' . urlencode($creds['password'] ?? '') . '@'
            : '';

        return "mongodb://{$auth}{$creds['host']}:{$port}/{$creds['database']}";
    }

    private function defaultPort(string $driver): int
    {
        if ($driver === 'pgsql')   return 5432;
        if ($driver === 'mongodb') return 27017;
        return 3306;
    }

    private function tablasMysql(): array
    {
        $rows = DB::connection(self::CONN)->select('SHOW TABLES');
        return array_map(fn($r) => array_values((array) $r)[0], $rows);
    }

    private function tablasPostgres(): array
    {
        $rows = DB::connection(self::CONN)->select(
            "SELECT table_name FROM information_schema.tables
             WHERE table_schema = 'public' AND table_type = 'BASE TABLE'
             ORDER BY table_name"
        );
        return array_column($rows, 'table_name');
    }

    private function tablasMongo(): array
    {
        $collections = DB::connection(self::CONN)->listCollections();
        $nombres = [];
        foreach ($collections as $col) {
            $nombres[] = $col->getName();
        }
        return $nombres;
    }

    private function columnasSql(string $driver, string $dbName, string $tabla): array
    {
        if ($driver === 'mysql') {
            $rows = DB::connection(self::CONN)->select(
                'SELECT COLUMN_NAME FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ?
                 ORDER BY ORDINAL_POSITION',
                [$dbName, $tabla]
            );
            return array_column($rows, 'COLUMN_NAME');
        }

        $rows = DB::connection(self::CONN)->select(
            "SELECT column_name FROM information_schema.columns
             WHERE table_schema = 'public' AND table_name = ?
             ORDER BY ordinal_position",
            [$tabla]
        );
        return array_column($rows, 'column_name');
    }

    private function normalizarMongo(array $doc): array
    {
        foreach ($doc as $k => $v) {
            if (is_object($v) || is_array($v)) {
                $doc[$k] = json_encode($v);
            }
        }
        return $doc;
    }
}
