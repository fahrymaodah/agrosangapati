<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Example Routes with Role-based Middleware
|--------------------------------------------------------------------------
|
| Usage examples for role checking middleware:
|
| 1. Single role:
|    Route::get('/admin', [AdminController::class, 'index'])->middleware('role:superadmin');
|
| 2. Multiple roles:
|    Route::get('/gapoktan', [GapoktanController::class, 'index'])
|        ->middleware('role:ketua_gapoktan,pengurus_gapoktan');
|
| 3. Using Gates in controller:
|    if (Gate::allows('manage-transactions', $poktanId)) {
|        // User can manage transactions for this poktan
|    }
|
| 4. Using Gates in Blade:
|    @can('manage-gapoktan')
|        <button>Edit Gapoktan</button>
|    @endcan
|
| Available roles:
| - superadmin
| - ketua_gapoktan
| - pengurus_gapoktan
| - ketua_poktan
| - pengurus_poktan
| - anggota_poktan
|
| Available gates:
| - manage-gapoktan
| - manage-poktan
| - view-finances
| - manage-transactions
| - approve-transactions
| - manage-harvests
| - manage-products
| - manage-orders
| - view-reports
| - manage-users
|
*/
