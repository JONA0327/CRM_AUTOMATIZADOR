<?php

use App\Http\Controllers\BotController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\DiseaseController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Webhook público — Evolution API hace POST aquí cuando llega un mensaje
Route::post('/webhook/whatsapp', [BotController::class, 'recibirWebhook'])->name('webhook.whatsapp');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    // Productos
    Route::resource('productos', ProductController::class)->except(['show', 'create', 'edit']);

    // Enfermedades
    Route::resource('enfermedades', DiseaseController::class)
        ->except(['show', 'create', 'edit'])
        ->parameters(['enfermedades' => 'enfermedad']);

    // Clientes
    Route::resource('clientes', ClientController::class)
        ->except(['show', 'create', 'edit'])
        ->parameters(['clientes' => 'cliente']);

    Route::get('/conversaciones', fn () => view('dashboard'))->name('conversaciones.index');
    Route::get('/usuarios',       fn () => view('dashboard'))->name('usuarios.index');

    // Configuración de APIs
    Route::prefix('configuracion')->name('configuracion.')->group(function () {
        Route::get('/',              [ConfiguracionController::class, 'index'])->name('index');
        Route::post('/',             [ConfiguracionController::class, 'update'])->name('update');
        Route::delete('/{clave}',    [ConfiguracionController::class, 'limpiar'])->name('limpiar');
    });

    // Bot / Evolution API
    Route::prefix('bot')->name('bot.')->group(function () {
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
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
