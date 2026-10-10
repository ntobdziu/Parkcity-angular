<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ControlController;
use App\Http\Controllers\FineController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\VehicleController;
use App\Http\Controllers\ZoneController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ParkCity API  (svi putevi dobijaju prefiks /api)
|--------------------------------------------------------------------------
| Slojevi zastite:
|   auth:sanctum   -> mora poslati Bearer token
|   active         -> nalog mora biti "active"
|   role:...       -> uloga mora biti na listi
*/

// ---- javno ----
Route::post('login', [AuthController::class, 'login'])->middleware('throttle:20,1');
Route::post('register', [AuthController::class, 'register'])->middleware('throttle:20,1');

// ---- samo prijavljeni sa aktivnim nalogom ----
Route::middleware(['auth:sanctum', 'active'])->group(function () {

    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me']);

    Route::get('zones', [ZoneController::class, 'index']);

    // ===== FUNKCIONALNOST 1: PARKING KARTE =====
    Route::get('tickets', [TicketController::class, 'index']);
    Route::get('tickets/{ticket}', [TicketController::class, 'show'])->whereNumber('ticket');

    Route::middleware('role:gradjanin')->group(function () {
        Route::get('vehicles', [VehicleController::class, 'index']);
        Route::post('vehicles', [VehicleController::class, 'store']);

        Route::post('tickets', [TicketController::class, 'store']);
        Route::post('tickets/{ticket}/extend', [TicketController::class, 'extend'])->whereNumber('ticket');
        Route::post('tickets/{ticket}/cancel', [TicketController::class, 'cancel'])->whereNumber('ticket');
    });

    // ===== FUNKCIONALNOST 2: KONTROLA I KAZNE =====
    Route::middleware('role:kontrolor')->group(function () {
        Route::post('controls/lookup', [ControlController::class, 'lookup']);
        Route::post('controls', [ControlController::class, 'store']);
    });

    Route::get('controls', [ControlController::class, 'index'])->middleware('role:kontrolor,admin');

    Route::get('fines', [FineController::class, 'index'])->middleware('role:gradjanin,kontrolor,admin');
    Route::post('fines/{fine}/pay', [FineController::class, 'pay'])
        ->whereNumber('fine')
        ->middleware('role:kontrolor,admin');
});
