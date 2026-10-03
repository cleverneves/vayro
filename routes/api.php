<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarroController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\LocacaoController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\ModeloController;
use App\Http\Controllers\RentalController;
use App\Http\Controllers\RenterController;

Route::prefix('v1')->group(function () {
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('jwt.auth')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('refresh', [AuthController::class, 'refresh']);

        Route::apiResource('marcas', MarcaController::class);
        Route::apiResource('modelos', ModeloController::class);
        Route::apiResource('carros', CarroController::class);
        Route::apiResource('clientes', ClienteController::class);
        Route::apiResource('locacoes', LocacaoController::class)
            ->parameters(['locacoes' => 'locacao']);
    });
});

Route::prefix('v2')->group(function () {
    Route::post('locatarios', [RenterController::class, 'store']);

    Route::middleware(['jwt.auth', 'role:renter'])->group(function () {
        Route::get('locatarios/me', [RenterController::class, 'me']);
        Route::patch('locatarios/me', [RenterController::class, 'updateMe']);
        Route::get('carros/disponiveis', [RentalController::class, 'available']);
        Route::post('locacoes', [RentalController::class, 'store']);
    });
});
