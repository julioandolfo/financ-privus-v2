<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CategoriaFinanceiraController;
use App\Http\Controllers\CentroCustoController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ContaBancariaController;
use App\Http\Controllers\ContasPagarController;
use App\Http\Controllers\ContasReceberController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\FormaPagamentoController;
use App\Http\Controllers\FornecedorController;
use App\Http\Controllers\MovimentacaoCaixaController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn() => redirect()->route('dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/senha/redefinir', [LoginController::class, 'showForgotForm'])->name('password.request');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/perfil', [ProfileController::class, 'index'])->name('profile');

    Route::resource('empresas', EmpresaController::class)->names('empresas');
    Route::resource('clientes', ClienteController::class)->names('clientes');
    Route::resource('fornecedores', FornecedorController::class)->names('fornecedores');

    Route::resource('contas-pagar', ContasPagarController::class)->names('contas-pagar');
    Route::post('contas-pagar/{contaPagar}/baixar', [ContasPagarController::class, 'baixar'])->name('contas-pagar.baixar');
    Route::post('contas-pagar/baixa-massa', [ContasPagarController::class, 'baixaMassa'])->name('contas-pagar.baixa-massa');

    Route::resource('contas-receber', ContasReceberController::class)->names('contas-receber');
    Route::post('contas-receber/{contaReceber}/baixar', [ContasReceberController::class, 'baixar'])->name('contas-receber.baixar');
    Route::post('contas-receber/baixa-massa', [ContasReceberController::class, 'baixaMassa'])->name('contas-receber.baixa-massa');

    Route::resource('contas-bancarias', ContaBancariaController::class)->names('contas-bancarias')->except(['show']);

    Route::resource('movimentacoes', MovimentacaoCaixaController::class)->names('movimentacoes')->except(['show']);

    Route::resource('categorias', CategoriaFinanceiraController::class)->names('categorias')->except(['show']);
    Route::resource('centros-custo', CentroCustoController::class)->names('centros-custo')->except(['show']);
    Route::resource('formas-pagamento', FormaPagamentoController::class)->names('formas-pagamento')->except(['show']);
});
