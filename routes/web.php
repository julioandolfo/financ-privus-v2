<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BoletoController;
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
use App\Http\Controllers\NfeController;
use App\Http\Controllers\NotificacaoController;
use App\Http\Controllers\PedidoVinculadoController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProdutoController;
use App\Http\Controllers\PontoEquilibrioController;
use App\Http\Controllers\ExtratoBancarioController;
use App\Http\Controllers\MigracaoLegadoController;
use App\Http\Controllers\TransacaoPendenteController;
use App\Http\Controllers\WhatsAppController;
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

    // Notificações
    Route::get('/notificacoes', [NotificacaoController::class, 'index'])->name('notificacoes.index');
    Route::get('/api/notificacoes/dropdown', [NotificacaoController::class, 'dropdown'])->name('notificacoes.dropdown');
    Route::post('/notificacoes/{id}/marcar-lida', [NotificacaoController::class, 'marcarLida'])->name('notificacoes.marcar-lida');
    Route::post('/notificacoes/marcar-todas-lidas', [NotificacaoController::class, 'marcarTodasLidas'])->name('notificacoes.marcar-todas-lidas');
    Route::delete('/notificacoes/{id}', [NotificacaoController::class, 'destroy'])->name('notificacoes.destroy');

    Route::resource('empresas', EmpresaController::class)->names('empresas');
    Route::resource('clientes', ClienteController::class)->names('clientes');
    Route::resource('fornecedores', FornecedorController::class)->names('fornecedores');

    // Financeiro
    Route::resource('contas-pagar', ContasPagarController::class)->names('contas-pagar');
    Route::post('contas-pagar/{contaPagar}/baixar', [ContasPagarController::class, 'baixar'])->name('contas-pagar.baixar');
    Route::post('contas-pagar/baixa-massa', [ContasPagarController::class, 'baixaMassa'])->name('contas-pagar.baixa-massa');

    Route::resource('contas-receber', ContasReceberController::class)->names('contas-receber');
    Route::post('contas-receber/{contaReceber}/baixar', [ContasReceberController::class, 'baixar'])->name('contas-receber.baixar');
    Route::post('contas-receber/baixa-massa', [ContasReceberController::class, 'baixaMassa'])->name('contas-receber.baixa-massa');

    // Boletos
    Route::resource('boletos', BoletoController::class)->except(['edit', 'update'])->names('boletos');
    Route::post('boletos/{boleto}/emitir', [BoletoController::class, 'emitir'])->name('boletos.emitir');
    Route::post('boletos/{boleto}/cancelar', [BoletoController::class, 'cancelar'])->name('boletos.cancelar');
    Route::post('boletos/{boleto}/marcar-pago', [BoletoController::class, 'marcarPago'])->name('boletos.marcar-pago');

    // Transações Pendentes (Open Banking)
    Route::get('/transacoes-pendentes', [TransacaoPendenteController::class, 'index'])->name('transacoes-pendentes.index');
    Route::post('/transacoes-pendentes/{transacao}/aprovar', [TransacaoPendenteController::class, 'aprovar'])->name('transacoes-pendentes.aprovar');
    Route::post('/transacoes-pendentes/{transacao}/ignorar', [TransacaoPendenteController::class, 'ignorar'])->name('transacoes-pendentes.ignorar');
    Route::post('/transacoes-pendentes/aprovar-lote', [TransacaoPendenteController::class, 'aprovarLote'])->name('transacoes-pendentes.aprovar-lote');

    Route::resource('contas-bancarias', ContaBancariaController::class)->names('contas-bancarias')->except(['show']);
    Route::resource('movimentacoes', MovimentacaoCaixaController::class)->names('movimentacoes')->except(['show']);

    Route::resource('categorias', CategoriaFinanceiraController::class)->names('categorias')->except(['show']);
    Route::resource('centros-custo', CentroCustoController::class)->names('centros-custo')->except(['show']);
    Route::resource('formas-pagamento', FormaPagamentoController::class)->names('formas-pagamento')->except(['show']);

    Route::resource('usuarios', UsuarioController::class)->names('usuarios')->except(['show']);
    Route::resource('produtos', ProdutoController::class)->names('produtos')->except(['show']);

    // Vendas
    Route::post('pedidos/status-massa', [PedidoVinculadoController::class, 'statusMassa'])->name('pedidos.status-massa');
    Route::resource('pedidos', PedidoVinculadoController::class)->names('pedidos');

    // NF-e
    Route::post('nfes/{nfe}/emitir', [NfeController::class, 'emitir'])->name('nfes.emitir');
    Route::post('nfes/{nfe}/cancelar', [NfeController::class, 'cancelar'])->name('nfes.cancelar');
    Route::get('nfes/{nfe}/danfe', [NfeController::class, 'danfe'])->name('nfes.danfe');
    Route::resource('nfes', NfeController::class)->names('nfes');

    // WhatsApp
    Route::get('/whatsapp', [WhatsAppController::class, 'index'])->name('whatsapp.index');
    Route::get('/whatsapp/conexoes', [WhatsAppController::class, 'conexoesIndex'])->name('whatsapp.conexoes.index');
    Route::get('/whatsapp/conexoes/create', [WhatsAppController::class, 'conexaoCreate'])->name('whatsapp.conexoes.create');
    Route::post('/whatsapp/conexoes', [WhatsAppController::class, 'conexaoStore'])->name('whatsapp.conexoes.store');
    Route::get('/whatsapp/conexoes/{config}/edit', [WhatsAppController::class, 'conexaoEdit'])->name('whatsapp.conexoes.edit');
    Route::put('/whatsapp/conexoes/{config}', [WhatsAppController::class, 'conexaoUpdate'])->name('whatsapp.conexoes.update');
    Route::delete('/whatsapp/conexoes/{config}', [WhatsAppController::class, 'conexaoDestroy'])->name('whatsapp.conexoes.destroy');
    Route::post('/whatsapp/conexoes/{config}/testar', [WhatsAppController::class, 'testar'])->name('whatsapp.conexoes.testar');
    Route::get('/whatsapp/regras', [WhatsAppController::class, 'regrasIndex'])->name('whatsapp.regras.index');
    Route::get('/whatsapp/regras/create', [WhatsAppController::class, 'regraCreate'])->name('whatsapp.regras.create');
    Route::post('/whatsapp/regras', [WhatsAppController::class, 'regraStore'])->name('whatsapp.regras.store');
    Route::get('/whatsapp/regras/{regra}/edit', [WhatsAppController::class, 'regraEdit'])->name('whatsapp.regras.edit');
    Route::put('/whatsapp/regras/{regra}', [WhatsAppController::class, 'regraUpdate'])->name('whatsapp.regras.update');
    Route::delete('/whatsapp/regras/{regra}', [WhatsAppController::class, 'regraDestroy'])->name('whatsapp.regras.destroy');
    Route::post('/whatsapp/regras/{regra}/toggle', [WhatsAppController::class, 'regraToggle'])->name('whatsapp.regras.toggle');

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

    // Relatórios + PDF
    Route::get('/relatorios/fluxo-caixa', [FluxoCaixaController::class, 'index'])->name('relatorios.fluxo-caixa');
    Route::get('/relatorios/fluxo-caixa/pdf', [FluxoCaixaController::class, 'pdf'])->name('relatorios.fluxo-caixa.pdf');
    Route::get('/relatorios/dre', [DreController::class, 'index'])->name('relatorios.dre');
    Route::get('/relatorios/dre/pdf', [DreController::class, 'pdf'])->name('relatorios.dre.pdf');
    Route::get('/relatorios/ponto-equilibrio', [PontoEquilibrioController::class, 'index'])->name('relatorios.ponto-equilibrio');
    Route::get('/relatorios/ponto-equilibrio/pdf', [PontoEquilibrioController::class, 'pdf'])->name('relatorios.ponto-equilibrio.pdf');

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
