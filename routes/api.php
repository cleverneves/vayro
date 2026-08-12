<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CarroController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\LocacaoController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\ModeloController;

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
