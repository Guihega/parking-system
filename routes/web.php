<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\Admin\AuditController;
use App\Http\Controllers\CashSessionController;
use Illuminate\Support\Facades\Http;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth'])->get('/admin/audit/logs', function () {
    // Aquí asumimos que el JWT ya está guardado en sesión
    $token = session('jwt_token');

    if (!$token) {
        return response()->json([
            'status' => 'error',
            'message' => 'JWT no disponible en sesión'
        ], 401);
    }

    $response = Http::withToken($token)
        ->get(url('/api/audit-logs'));

    return response()->json($response->json(), $response->status());
});


Route::middleware('auth')->get('/_debug/jwt', function () {
    return response()->json([
        'jwt' => session('jwt_token'),
    ]);
});


Route::middleware('auth')->group(function () {

    Route::get('/cash-session/open', [CashSessionController::class, 'openForm'])
        ->name('cash.open.form');

    Route::post('/cash-session/open', [CashSessionController::class, 'open'])
        ->name('cash.open');

    // ✅ NUEVA VISTA CORE UNIFICADA
    Route::get('/parking/core', function () {
        return view('parking.parking-core');
    })->name('parking.core');
});


Route::middleware(['auth', 'cash.session'])->group(function () {

    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/parking/select-space', function () {
        return view('parking.select-space');
    })->name('parking.select.space');

    Route::get('/parking/checkout', fn() => view('tickets.checkout'))
        ->name('parking.checkout');

    Route::get('/tickets/create', [TicketController::class, 'create'])->name('tickets.create');
    Route::post('/tickets', [TicketController::class, 'store'])->name('tickets.store');
    Route::get('/tickets/{id}', [TicketController::class, 'show'])->name('tickets.show');

    Route::get('/admin/audit', [AuditController::class, 'index'])
        ->name('admin.audit.index');
});

require __DIR__.'/auth.php';
