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
use App\Http\Controllers\ConciliacaoController;
use App\Http\Controllers\DespesaRecorrenteController;
use App\Http\Controllers\ReceitaRecorrenteController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\IntegracaoController;
use App\Http\Controllers\ConfiguracaoController;
use App\Http\Controllers\DreController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\FluxoCaixaController;
use App\Http\Controllers\MovimentacaoCaixaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\PontoEquilibrioController;
use App\Http\Controllers\ExtratoBancarioController;
use App\Http\Controllers\MigracaoLegadoController;
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
    Route::put('/perfil', [ProfileController::class, 'update'])->name('profile.update');

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

    Route::resource('usuarios', UsuarioController::class)->names('usuarios')->except(['show']);

    Route::resource('produtos', ProdutoController::class)->names('produtos')->except(['show']);

    Route::get('/api/cnpj/{cnpj}', [ApiController::class, 'buscarCnpj'])->name('api.cnpj');

    Route::get('/integracoes', [IntegracaoController::class, 'index'])->name('integracoes.index');
    Route::get('/integracoes/{tipo}', [IntegracaoController::class, 'configurar'])->name('integracoes.configurar');
    Route::put('/integracoes/{tipo}', [IntegracaoController::class, 'salvar'])->name('integracoes.salvar');
    Route::post('/integracoes/{tipo}/testar', [IntegracaoController::class, 'testar'])->name('integracoes.testar');

    Route::get('/configuracoes', [ConfiguracaoController::class, 'index'])->name('configuracoes.index');
    Route::put('/configuracoes', [ConfiguracaoController::class, 'update'])->name('configuracoes.update');

    Route::get('/migracao', [MigracaoLegadoController::class, 'index'])->name('migracao.index');
    Route::post('/migracao/testar', [MigracaoLegadoController::class, 'testar'])->name('migracao.testar');
    Route::post('/migracao/passo', [MigracaoLegadoController::class, 'executarPasso'])->name('migracao.passo');

    Route::get('/relatorios/fluxo-caixa', [FluxoCaixaController::class, 'index'])->name('relatorios.fluxo-caixa');
    Route::get('/relatorios/dre', [DreController::class, 'index'])->name('relatorios.dre');
    Route::get('/relatorios/ponto-equilibrio', [PontoEquilibrioController::class, 'index'])->name('relatorios.ponto-equilibrio');

    Route::resource('despesas-recorrentes', DespesaRecorrenteController::class)->names('despesas-recorrentes')->except(['show']);
    Route::post('despesas-recorrentes/{despesasRecorrente}/toggle', [DespesaRecorrenteController::class, 'toggle'])->name('despesas-recorrentes.toggle');
    Route::post('despesas-recorrentes/{despesasRecorrente}/gerar', [DespesaRecorrenteController::class, 'gerarAgora'])->name('despesas-recorrentes.gerar');

    Route::resource('receitas-recorrentes', ReceitaRecorrenteController::class)->names('receitas-recorrentes')->except(['show']);
    Route::post('receitas-recorrentes/{receitasRecorrente}/toggle', [ReceitaRecorrenteController::class, 'toggle'])->name('receitas-recorrentes.toggle');
    Route::post('receitas-recorrentes/{receitasRecorrente}/gerar', [ReceitaRecorrenteController::class, 'gerarAgora'])->name('receitas-recorrentes.gerar');

    Route::resource('extratos', ExtratoBancarioController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
    Route::post('extratos/{extrato}/lancamentos/{lancamento}/conciliar', [ExtratoBancarioController::class, 'conciliarLancamento'])->name('extratos.conciliar');
    Route::post('extratos/{extrato}/lancamentos/{lancamento}/ignorar', [ExtratoBancarioController::class, 'ignorarLancamento'])->name('extratos.ignorar');
    Route::post('extratos/{extrato}/lancamentos/{lancamento}/desconciliar', [ExtratoBancarioController::class, 'desconciliarLancamento'])->name('extratos.desconciliar');
    Route::post('extratos/{extrato}/lancamentos/{lancamento}/criar-movimentacao', [ExtratoBancarioController::class, 'criarMovimentacao'])->name('extratos.criar-movimentacao');

    Route::get('/conciliacao', [ConciliacaoController::class, 'index'])->name('conciliacao.index');
    Route::post('/conciliacao/conciliar', [ConciliacaoController::class, 'conciliar'])->name('conciliacao.conciliar');
    Route::post('/conciliacao/desconciliar', [ConciliacaoController::class, 'desconciliar'])->name('conciliacao.desconciliar');
    Route::post('/conciliacao/{movimentacao}/toggle', [ConciliacaoController::class, 'conciliarItem'])->name('conciliacao.toggle');
});
