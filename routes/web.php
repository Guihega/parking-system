<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\CashSessionController;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\ParkingSpaceController;
use App\Http\Controllers\Admin\TariffController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CashSessionReportController;
use App\Http\Controllers\Admin\SalesReportController;
use App\Exports\SalesReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\AdminUsersController;
use App\Http\Controllers\Admin\AdminRolesController;
use App\Http\Controllers\Admin\TicketAdminController;
/*
|--------------------------------------------------------------------------
| Root
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return auth()->check()
        ? redirect('/parking/core')
        : redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Profile
|--------------------------------------------------------------------------
*/

Route::middleware(['auth','tenant'])->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| Core parking flow
|--------------------------------------------------------------------------
*/

Route::middleware(['auth','tenant'])->group(function () {

    Route::get('/parking/core', fn () => view('parking.parking-core'))
        ->name('parking.core');

    Route::get('/cash-session/open', [CashSessionController::class, 'openForm'])
        ->name('cash.open.form');

    Route::post('/cash-session/open', [CashSessionController::class, 'open'])
        ->name('cash.open');
});

/*
|--------------------------------------------------------------------------
| Dashboard resolver by role
|--------------------------------------------------------------------------
*/

Route::middleware(['auth','tenant'])->get('/dashboard', function () {

    if (auth()->user()->can('parking.entry')) {
        return redirect()->route('parking.core');
    }

    if (auth()->user()->can('reports.view')) {
        return redirect()->route('admin.dashboard.index');
    }

    abort(403);

})->name('dashboard');

/*
|--------------------------------------------------------------------------
| Parking operation (POS shell)
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'tenant',
    'cash.session',
    'perm:parking.entry'
])->group(function () {

    // Siempre carga el core
    Route::get('/parking/core', fn () => view('parking.parking-core'))
        ->name('parking.core');

    // Rutas virtuales (mismo core — UX limpia)
    Route::get('/parking/select-space', fn () => redirect()->route('parking.core'))
        ->name('parking.select.space');

    Route::get('/parking/checkout', fn () => redirect()->route('parking.core'))
        ->name('parking.checkout');

    // Tickets reales (backend)
    Route::get('/tickets/create', [TicketController::class, 'create'])
        ->name('tickets.create');

    Route::post('/tickets', [TicketController::class, 'store'])
        ->name('tickets.store');

    Route::get('/tickets/{id}', [TicketController::class, 'show'])
        ->name('tickets.show');
});


/*
|--------------------------------------------------------------------------
| Admin area
|--------------------------------------------------------------------------
*/
//Route::middleware(['auth','tenant','perm:users.view'])
Route::middleware(['auth','tenant'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {

    // 📊 Dashboard + ventas
    Route::middleware('perm:reports.view')->group(function () {

        Route::get('/dashboard', [DashboardController::class,'index'])
            ->name('dashboard.index');

        Route::get('/sales-report', [SalesReportController::class,'index'])
            ->name('sales-report.index');

        Route::get('/sales-report/pdf', [SalesReportController::class,'pdf'])
            ->name('sales-report.pdf');

        Route::get('/sales-report/excel', [SalesReportController::class,'export'])
            ->name('sales-report.export');
    });

    // 💰 Caja
    Route::middleware('perm:cash.audit')->group(function () {

        Route::get('/cash-sessions', [CashSessionReportController::class,'index'])
            ->name('cash-sessions.index');

        Route::get('/cash-sessions/{id}', [CashSessionReportController::class,'show'])
            ->name('cash-sessions.show');

        Route::get('/cash-sessions/current', [CashSessionController::class, 'current'])
            ->middleware('perm:cash.open')
            ->name('cash-sessions.current');

        // WEB (admin)
        Route::get('/cash-sessions/{id}/close-preview', [CashSessionController::class, 'closePreview'])
            ->middleware('perm:cash.close');

        Route::get('/cash-sessions/{id}/pdf', [CashSessionReportController::class, 'pdf'])
            ->name('cash-sessions.pdf'); // ✅ ESTA ES LA QUE FALTABA
    });


    // 💵 Tarifas
    Route::middleware('perm:tariffs.view')->group(function () {

        Route::get('/tariffs', [TariffController::class,'index'])
            ->name('tariffs.index');

        Route::get('/tariffs/create', [TariffController::class,'create'])
            ->middleware('perm:tariffs.create')
            ->name('tariffs.create');

        Route::post('/tariffs', [TariffController::class,'store'])
            ->middleware('perm:tariffs.create')
            ->name('tariffs.store');

        Route::get('/tariffs/{id}/edit', [TariffController::class,'edit'])
            ->middleware('perm:tariffs.update')
            ->name('tariffs.edit');

        Route::put('/tariffs/{id}', [TariffController::class,'update'])
            ->middleware('perm:tariffs.update')
            ->name('tariffs.update');
    });

    // 🚗 Cajones
    Route::middleware('perm:parking.view')->group(function () {

        Route::get('/parking-spaces', [ParkingSpaceController::class,'index'])
            ->name('parking-spaces.index');

        Route::get('/parking-spaces/create', [ParkingSpaceController::class,'create'])
            ->name('parking-spaces.create');

        Route::post('/parking-spaces', [ParkingSpaceController::class,'store'])
            ->name('parking-spaces.store');

        Route::get('/parking-spaces/{id}/edit', [ParkingSpaceController::class,'edit'])
            ->name('parking-spaces.edit');

        Route::put('/parking-spaces/{id}', [ParkingSpaceController::class,'update'])
            ->name('parking-spaces.update');
    });

    // 🏢 Sucursales
    Route::middleware('perm:roles.assign')->group(function () {

        Route::get('/branches', [BranchController::class, 'index'])
            ->name('branches.index');

        Route::get('/branches/create', [BranchController::class, 'create'])
            ->name('branches.create');

        Route::post('/branches', [BranchController::class, 'store'])
            ->name('branches.store');

        Route::get('/branches/{id}/edit', [BranchController::class, 'edit'])
            ->name('branches.edit');

        Route::put('/branches/{id}', [BranchController::class, 'update'])
            ->name('branches.update');
    });
});

/*
|--------------------------------------------------------------------------
| Exports + PDFs (tenant safe)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth','tenant','perm:reports.view'])
->get('/sales-report/export', function (Request $request) {

    return Excel::download(
        new SalesReportExport(
            app('tenant_id'),
            $request->start_date,
            $request->end_date
        ),
        'reporte_ventas.xlsx'
    );
})->name('sales-report.export');

Route::middleware(['auth','tenant','perm:cash.audit'])
->get('/cash-sessions/{id}/pdf',
    [CashSessionReportController::class,'pdf']
)->name('cash-sessions.pdf');

Route::middleware(['auth','tenant','perm:reports.view'])
->get('/sales-report/pdf',
    [SalesReportController::class,'pdf']
)->name('sales-report.pdf');

/*
|--------------------------------------------------------------------------
| Auth routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'tenant', 'perm:users.view'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/users', [AdminUsersController::class, 'index'])
            ->name('users.index');
    });

Route::middleware(['auth', 'tenant', 'perm:roles.view'])
->prefix('admin')
->name('admin.')
->group(function () {

    Route::get('/roles', [AdminRolesController::class, 'index'])
        ->name('roles.index');
});


Route::put('/admin/roles/{role}/permissions',[AdminRolesController::class, 'assignPermissions'])
    ->middleware(['auth', 'perm:roles.assign']);


Route::middleware(['auth', 'tenant'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/tickets', [TicketAdminController::class, 'index'])
        ->name('admin.tickets.index');

        Route::get('/tickets/{token}', [TicketAdminController::class, 'show'])
        ->name('tickets.show');
});




require __DIR__.'/auth.php';
