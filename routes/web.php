<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrcamentoController;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/calcular', [OrcamentoController::class, 'calcular'])->name('orcamento.calcular');
Route::get('/confirmar', [OrcamentoController::class, 'confirmar'])->name('orcamento.confirmar');
