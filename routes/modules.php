<?php

use App\Http\Controllers\LogController;
use App\Http\Controllers\PadraoImportacaoController;
use App\Http\Controllers\PerfilConsolidacaoController;
use App\Http\Controllers\ProdutoController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {

    // -------------------------------------------------------------------------
    // Module 1: Perfis de Consolidação
    // -------------------------------------------------------------------------
    Route::resource('perfis-consolidacao', PerfilConsolidacaoController::class)
        ->names('perfis-consolidacao');

    // -------------------------------------------------------------------------
    // Module 3: Logs de Auditoria
    // -------------------------------------------------------------------------
    Route::get('/logs', [LogController::class, 'index'])->name('logs.index');

    // -------------------------------------------------------------------------
    // Module 4: Padrões de Importação de Extrato
    // -------------------------------------------------------------------------
    Route::resource('padroes-importacao', PadraoImportacaoController::class)
        ->names('padroes-importacao')
        ->except(['show']);

    // -------------------------------------------------------------------------
    // Module 2: Variações e Fotos de Produto (nested under produtos)
    // -------------------------------------------------------------------------
    Route::get('produtos/{produto}/variacoes', [ProdutoController::class, 'variacoes'])
        ->name('produtos.variacoes');
    Route::post('produtos/{produto}/variacoes', [ProdutoController::class, 'storeVariacao'])
        ->name('produtos.variacoes.store');
    Route::delete('produtos/{produto}/variacoes/{variacao}', [ProdutoController::class, 'destroyVariacao'])
        ->name('produtos.variacoes.destroy');

    Route::post('produtos/{produto}/fotos', [ProdutoController::class, 'uploadFoto'])
        ->name('produtos.fotos.store');
    Route::delete('produtos/{produto}/fotos/{foto}', [ProdutoController::class, 'destroyFoto'])
        ->name('produtos.fotos.destroy');
});
