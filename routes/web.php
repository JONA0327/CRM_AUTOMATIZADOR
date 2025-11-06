<?php

use App\Http\Controllers\DiseaseController;
use App\Http\Controllers\DiseaseSuggestionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    $user = auth()->user();

    if ($user->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }

    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rutas de productos
    Route::resource('products', \App\Http\Controllers\ProductController::class);
    Route::get('/products-by-category', [\App\Http\Controllers\ProductController::class, 'getProductsByCategory'])->name('products.by-category');

    // Índice de enfermedades
    Route::get('/diseases/list', [DiseaseController::class, 'list'])->name('diseases.list');
    Route::post('/diseases/suggestions', [DiseaseSuggestionController::class, 'suggestProducts'])->name('diseases.suggestions');
    Route::post('/diseases/information', [DiseaseSuggestionController::class, 'generateInformation'])->name('diseases.information');
    Route::post('/diseases/suggestions/{suggestion}/approve', [DiseaseController::class, 'approveSuggestion'])->name('diseases.suggestions.approve');
    Route::resource('diseases', DiseaseController::class);

    // Rutas de mensajes programados
    Route::resource('scheduled-messages', \App\Http\Controllers\ScheduledMessageController::class);
    Route::post('/scheduled-messages/{scheduledMessage}/toggle-status', [\App\Http\Controllers\ScheduledMessageController::class, 'toggleStatus'])->name('scheduled-messages.toggle-status');
    Route::get('/scheduled-messages/category/{category}', [\App\Http\Controllers\ScheduledMessageController::class, 'getByCategory'])->name('scheduled-messages.by-category');
    Route::get('/scheduled-messages/current', [\App\Http\Controllers\ScheduledMessageController::class, 'getCurrentMessages'])->name('scheduled-messages.current');
    Route::post('/scheduled-messages/preview', [\App\Http\Controllers\ScheduledMessageController::class, 'preview'])->name('scheduled-messages.preview');
});

// Rutas protegidas para administradores
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    Route::get('/users', function () {
        $users = \App\Models\User::with('role')->get();
        return view('admin.users.index', compact('users'));
    })->name('users.index');

    Route::get('/roles', function () {
        $roles = \App\Models\Role::withCount('users')->get();
        return view('admin.roles.index', compact('roles'));
    })->name('roles.index');

    Route::get('/system-info', function () {
        return view('admin.system-info');
    })->name('system.info');
});

require __DIR__.'/auth.php';
