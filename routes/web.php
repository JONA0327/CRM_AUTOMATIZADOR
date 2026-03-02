<?php

use App\Http\Controllers\BotController;
use App\Http\Controllers\CatalogModuleController;
use App\Http\Controllers\CatalogRecordController;
use App\Http\Controllers\CollaboratorController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TenantController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Webhook público por instancia — Evolution API hace POST aquí cuando llega un mensaje
// El middleware tenant.instance inicializa el tenant a partir del nombre de la instancia
Route::post('/webhook/whatsapp/{instancia}', [BotController::class, 'recibirWebhook'])
    ->name('webhook.whatsapp')
    ->middleware('tenant.instance')
    ->where('instancia', '.+');

// ──────────────────────────────────────────────────────────────────────────────
// Dashboard — autenticado, inicializa tenant si el usuario tiene uno
// ──────────────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified', 'tenant.auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

// ──────────────────────────────────────────────────────────────────────────────
// Rutas que REQUIEREN tenant activo (anfitrion + colaborador)
// ──────────────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified', 'tenant.required'])->group(function () {

    // ── Catálogos dinámicos — todos los roles del tenant ──
    Route::prefix('catalogo/{module}')->name('catalogo.')->group(function () {
        Route::get('/',        [CatalogRecordController::class, 'index'])->name('index');
        Route::post('/',       [CatalogRecordController::class, 'store'])->name('store');
        Route::put('/{id}',    [CatalogRecordController::class, 'update'])->name('update');
        Route::delete('/{id}', [CatalogRecordController::class, 'destroy'])->name('destroy');
        Route::get('/opciones-relation',                  [CatalogRecordController::class, 'opcionesRelation'])->name('opciones-relation');
        Route::post('/upload-file',                       [CatalogRecordController::class, 'uploadFile'])->name('upload-file');
        Route::post('/{id}/whatsapp-verify/{fieldSlug}', [CatalogRecordController::class, 'verificarWhatsapp'])->name('whatsapp-verify');
    });

    // ── Bot: conversaciones y contactos — todos los roles del tenant ──
    Route::prefix('bot')->name('bot.')->group(function () {
        Route::get('/conversaciones',   [BotController::class, 'conversaciones'])->name('conversaciones');
        Route::get('/contactos',        [BotController::class, 'listarContactos'])->name('contactos');
        Route::get('/mensajes/{phone}', [BotController::class, 'mensajesPorTelefono'])->name('mensajes')->where('phone', '.+');

        // ── Gestión de instancias y configuración — solo anfitrion ──
        Route::middleware('role:anfitrion')->group(function () {
            Route::get('/',                        [BotController::class, 'index'])->name('index');
            Route::get('/conectar',                [BotController::class, 'conectar'])->name('conectar');
            Route::post('/crear-instancia',        [BotController::class, 'crearInstancia'])->name('crear');
            Route::get('/estado/{instancia}',      [BotController::class, 'estadoConexion'])->name('estado');
            Route::get('/qr/{instancia}',          [BotController::class, 'refrescarQr'])->name('qr');
            Route::delete('/eliminar/{instancia}', [BotController::class, 'eliminarInstancia'])->name('eliminar');
            Route::post('/toggle',                 [BotController::class, 'toggleBot'])->name('toggle');
            Route::get('/diagnostico',             [BotController::class, 'diagnostico'])->name('diagnostico');
            Route::get('/config/{instancia}',      [BotController::class, 'getConfig'])->name('config.get')->where('instancia', '.+');
            Route::post('/config/{instancia}',     [BotController::class, 'setConfig'])->name('config.set')->where('instancia', '.+');
            Route::get('/logs/{instancia}',        [BotController::class, 'getLogs'])->name('logs')->where('instancia', '.+');
            Route::post('/registrar-instancia',    [BotController::class, 'registrarInstancia'])->name('registrar');
            Route::post('/toggle-instance',        [BotController::class, 'toggleInstance'])->name('toggle-instance');
            Route::post('/set-default',            [BotController::class, 'setDefault'])->name('set-default');
        });
    });

    // ── Rutas exclusivas para anfitrion ──
    Route::middleware('role:anfitrion')->group(function () {

        // Admin no-code de módulos y campos
        Route::prefix('admin/modulos')->name('admin.modulos.')->group(function () {
            Route::get('/',                      [CatalogModuleController::class, 'index'])->name('index');
            Route::post('/',                     [CatalogModuleController::class, 'store'])->name('store');
            Route::put('/{id}',                  [CatalogModuleController::class, 'update'])->name('update');
            Route::delete('/{id}',              [CatalogModuleController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/campos',          [CatalogModuleController::class, 'storeField'])->name('campos.store');
            Route::put('/{id}/campos/{fid}',     [CatalogModuleController::class, 'updateField'])->name('campos.update');
            Route::delete('/{id}/campos/{fid}',  [CatalogModuleController::class, 'destroyField'])->name('campos.destroy');
            Route::post('/{id}/campos/reorder',  [CatalogModuleController::class, 'reorderFields'])->name('campos.reorder');
            Route::post('/reorder',              [CatalogModuleController::class, 'reorder'])->name('reorder');
        });

        // Configuración de APIs, prompts y BD externas
        Route::prefix('configuracion')->name('configuracion.')->group(function () {
            Route::get('/',           [ConfiguracionController::class, 'index'])->name('index');
            Route::post('/',          [ConfiguracionController::class, 'update'])->name('update');
            Route::delete('/{clave}', [ConfiguracionController::class, 'limpiar'])->name('limpiar');
            Route::post('/test-db',   [ConfiguracionController::class, 'testExternalDb'])->name('test-db');
        });

        // Gestión de colaboradores del negocio
        Route::prefix('colaboradores')->name('colaboradores.')->group(function () {
            Route::get('/',        [CollaboratorController::class, 'index'])->name('index');
            Route::post('/',       [CollaboratorController::class, 'store'])->name('store');
            Route::delete('/{id}', [CollaboratorController::class, 'destroy'])->name('destroy');
        });
    });
});

// ──────────────────────────────────────────────────────────────────────────────
// Admin global — super_admin únicamente, opera sobre la BD central (sin tenant)
// ──────────────────────────────────────────────────────────────────────────────
Route::middleware(['auth', 'verified', 'role:super_admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/negocios',         [TenantController::class, 'index'])->name('negocios.index');
    Route::post('/negocios',        [TenantController::class, 'store'])->name('negocios.store');
    Route::put('/negocios/{id}',    [TenantController::class, 'update'])->name('negocios.update');
    Route::delete('/negocios/{id}', [TenantController::class, 'destroy'])->name('negocios.destroy');

    // Aliases de compatibilidad con el nombre anterior
    Route::post('/tenants',         [TenantController::class, 'store'])->name('tenants.store');
    Route::get('/tenants',          [TenantController::class, 'index'])->name('tenants.index');
    Route::delete('/tenants/{id}',  [TenantController::class, 'destroy'])->name('tenants.destroy');
});

// ──────────────────────────────────────────────────────────────────────────────
// Perfil de usuario
// ──────────────────────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
