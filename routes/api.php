<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{
    TicketApiController,
    TicketExitApiController,
    TicketChargeApiController,
    TicketReceiptApiController,
    TicketHistoryApiController,
    TicketCancelApiController,
    CashSessionApiController,
    CashSessionCloseApiController,
    CashSessionReportApiController,
    SalesReportApiController,
    DashboardFinanceApiController,
    DashboardChartsApiController,
    AuditLogApiController,
    CashSessionAuditApiController,
    TariffApiController,
    AuthApiController,
    UsersApiController,
    RoleApiController,
    PermissionApiController,
    ParkingSpaceApiController,
    TicketInfoApiController,
    BranchApiController,
    VehicleTypeController
};

use Illuminate\Http\Request;
/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS (SIN JWT)
|--------------------------------------------------------------------------
*/

Route::post('/auth/login', [AuthApiController::class, 'login'])
    ->middleware('throttle:login');

/*
|--------------------------------------------------------------------------
| RUTAS PRIVADAS (JWT REQUERIDO)
|--------------------------------------------------------------------------
*/

Route::middleware(['vjwt'])->group(function () {

    // REPORTES
    Route::get('/reports/sales', [SalesReportApiController::class, 'show'])
        ->middleware('perm:reports.view');

    // DASHBOARD
    Route::get('/dashboard/finance', [DashboardFinanceApiController::class, 'show'])
        ->middleware('perm:dashboard.view');

    Route::get('/dashboard/charts', [DashboardChartsApiController::class, 'show'])
        ->middleware('perm:dashboard.view');

    // TARIFAS
    Route::get('/tariffs', [TariffApiController::class, 'index'])
        ->middleware('perm:tariffs.view');

    Route::post('/tariffs', [TariffApiController::class, 'store'])
        ->middleware('perm:tariffs.create');

    Route::put('/tariffs/{id}', [TariffApiController::class, 'update'])
        ->middleware('perm:tariffs.update');

    Route::delete('/tariffs/{id}', [TariffApiController::class, 'destroy'])
        ->middleware('perm:tariffs.delete');

    // CAJA
    Route::post('/cash-sessions/open', [CashSessionApiController::class, 'open'])
        ->middleware('perm:cash.open');

    Route::post('/cash-sessions/close', [CashSessionCloseApiController::class, 'store'])
        ->middleware('perm:cash.close');

    Route::get('/cash-sessions/{id}/report', [CashSessionReportApiController::class, 'show'])
        ->middleware('perm:cash.audit');

    Route::get('/audit/cash-sessions', [CashSessionAuditApiController::class, 'show'])
        ->middleware('perm:audit.view');

    Route::get('/audit/logs/export', [AuditLogApiController::class, 'export'])
        ->middleware('perm:audit.export');

    Route::get('/users', [UsersApiController::class, 'index'])
        ->middleware('perm:users.view');

    Route::post('/users', [UsersApiController::class, 'store'])
        ->middleware('perm:users.create');

    Route::get('/users/{id}', [UsersApiController::class, 'show'])
        ->middleware('perm:users.view');

    Route::put('/users/{id}', [UsersApiController::class, 'update'])
        ->middleware('perm:users.update');

    Route::put('/users/{id}/role', [UsersApiController::class, 'updateRole'])
        ->middleware('perm:users.assign');

    Route::put('/users/{id}/status', [UsersApiController::class, 'updateStatus'])
        ->middleware('perm:users.status');

    Route::put('/users/{id}/reset-password', [UsersApiController::class, 'resetPassword'])
        ->middleware(['perm:users.reset']);

    Route::get('/roles', [RoleApiController::class, 'index'])
        ->middleware('perm:roles.view');

    Route::post('/roles', [RoleApiController::class, 'store'])
        ->middleware('perm:roles.create');

    Route::put('/roles/{id}', [RoleApiController::class, 'update'])
        ->middleware('perm:roles.update');

    Route::delete('/roles/{id}', [RoleApiController::class, 'destroy'])
        ->middleware('perm:roles.delete');

    Route::get('/roles/{id}/permissions', [RoleApiController::class, 'permissions'])
        ->middleware('perm:roles.view');

    Route::put('/roles/{id}/permissions', [RoleApiController::class, 'assignPermissions'])
        ->middleware('perm:roles.assign');

    Route::get('/permissions', [PermissionApiController::class, 'index'])
        ->middleware('perm:permissions.view');

    // AUDITORÍA
    Route::get('/audit-logs', [AuditLogApiController::class, 'index'])
        ->middleware(['perm:audit.view']);

    // ESTACIONAMIENTO - OPERACIÓN

    Route::get('/parking-spaces', [ParkingSpaceApiController::class, 'index'])
        ->middleware('perm:parking.view');

    Route::post('/tickets/entry', [TicketApiController::class, 'store'])
        ->middleware('perm:parking.entry');

    Route::get('/tickets/{token}', [TicketInfoApiController::class, 'show'])
        ->middleware('perm:parking.view');

    Route::post('/tickets/exit', [TicketExitApiController::class, 'store'])
        ->middleware('perm:parking.exit');

    Route::post('/tickets/cancel', [TicketCancelApiController::class, 'store'])
        ->middleware('perm:parking.cancel');

    Route::get('/tickets/{token}/charge', [TicketChargeApiController::class, 'show'])
        ->middleware('perm:parking.charge');

    Route::get('/tickets/{token}/history', [TicketHistoryApiController::class, 'show'])
        ->middleware('perm:parking.history');

    Route::get('/tickets/token/{token}/receipt', [TicketReceiptApiController::class, 'show'])
        ->middleware('perm:parking.receipt');

    Route::get('/tickets/token/{token}/receipt/print', [TicketReceiptApiController::class, 'print'])
        ->middleware('perm:parking.receipt');

    Route::get('/branches', [BranchApiController::class, 'index'])
        ->middleware('perm:parking.view');

    Route::get('/cash-sessions/current', [CashSessionApiController::class, 'current'])
        ->middleware('perm:cash.open');

    Route::get('/vehicle-types', [VehicleTypeController::class, 'index'])
        ->middleware('perm:parking.view');
});

Route::fallback(function () {
    return response()->json([
        'status' => 'error',
        'message' => 'Endpoint no encontrado'
    ], 404);
});


