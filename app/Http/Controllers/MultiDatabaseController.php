<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Product;
use App\Models\SecondaryData;

class MultiDatabaseController extends Controller
{
    /**
     * Ejemplo de consultas en múltiples bases de datos
     */
    public function index()
    {
        try {
            // Datos de la BD principal
            $mainDbData = [
                'users_count' => User::count(),
                'products_count' => Product::count(),
                'connection' => 'mysql (principal)'
            ];

            // Datos de la BD secundaria (si existe)
            $secondaryDbData = [];
            try {
                $secondaryDbData = [
                    'secondary_data_count' => SecondaryData::count(),
                    'connection' => 'pgsql_secondary'
                ];
            } catch (\Exception $e) {
                $secondaryDbData = [
                    'error' => 'BD secundaria no disponible',
                    'connection' => 'pgsql_secondary'
                ];
            }

            return response()->json([
                'success' => true,
                'main_database' => $mainDbData,
                'secondary_database' => $secondaryDbData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ejemplo de inserción en múltiples BD
     */
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // Insertar en BD principal
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'role_id' => 2 // Usuario normal
            ]);

            DB::commit();

            // Insertar en BD secundaria PostgreSQL (en transacción separada)
            try {
                DB::connection('pgsql_secondary')->beginTransaction();

                $secondaryData = SecondaryData::create([
                    'name' => $request->name,
                    'description' => 'Usuario creado desde API',
                    'data' => [
                        'user_id' => $user->id,
                        'created_from' => 'api',
                        'timestamp' => now()
                    ]
                ]);

                DB::connection('pgsql_secondary')->commit();

                return response()->json([
                    'success' => true,
                    'user' => $user,
                    'secondary_record' => $secondaryData,
                    'message' => 'Datos guardados en ambas BD'
                ]);

            } catch (\Exception $e) {
                DB::connection('pgsql_secondary')->rollback();

                return response()->json([
                    'success' => true,
                    'user' => $user,
                    'warning' => 'Usuario creado solo en BD principal',
                    'secondary_error' => $e->getMessage()
                ]);
            }

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Ejemplo de consulta raw en múltiples BD
     */
    public function rawQueries()
    {
        try {
            // Consulta raw en BD principal
            $mainData = DB::connection('mysql')
                ->select('SELECT COUNT(*) as total FROM users WHERE created_at > ?', [
                    now()->subDays(30)
                ]);

            // Consulta raw en BD secundaria PostgreSQL
            $secondaryData = [];
            try {
                $secondaryData = DB::connection('pgsql_secondary')
                    ->select('SELECT COUNT(*) as total FROM secondary_data WHERE is_active = ?', [true]);
            } catch (\Exception $e) {
                $secondaryData = ['error' => 'BD secundaria PostgreSQL no disponible'];
            }

            return response()->json([
                'success' => true,
                'main_db_recent_users' => $mainData,
                'secondary_db_active_records' => $secondaryData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
