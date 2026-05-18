<?php

namespace Database\Seeders;

use App\Models\CategoriaFinanceira;
use App\Models\CentroCusto;
use App\Models\Cliente;
use App\Models\ContaBancaria;
use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\Empresa;
use App\Models\FormaPagamento;
use App\Models\Fornecedor;
use App\Models\MovimentacaoCaixa;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Empresa
        $empresa = Empresa::firstOrCreate(
            ['cnpj' => '12.345.678/0001-90'],
            [
                'razao_social'  => 'Empresa Demo Ltda',
                'nome_fantasia' => 'Demo Financeiro',
                'codigo'        => 'DEMO',
                'ativo'         => true,
            ]
        );

        // Admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@demo.com'],
            [
                'name'       => 'Administrador',
                'password'   => Hash::make('password'),
                'empresa_id' => $empresa->id,
                'role'       => 'admin',
                'ativo'      => true,
            ]
        );

        // Categorias
        $cats = collect([
            ['nome' => 'Receitas',       'tipo' => 'receita'],
            ['nome' => 'Serviços',       'tipo' => 'receita'],
            ['nome' => 'Produtos',       'tipo' => 'receita'],
            ['nome' => 'Despesas',       'tipo' => 'despesa'],
            ['nome' => 'Aluguel',        'tipo' => 'despesa'],
            ['nome' => 'Salários',       'tipo' => 'despesa'],
            ['nome' => 'Marketing',      'tipo' => 'despesa'],
            ['nome' => 'Infraestrutura', 'tipo' => 'despesa'],
        ])->map(fn($c) => CategoriaFinanceira::firstOrCreate(
            ['empresa_id' => $empresa->id, 'nome' => $c['nome']],
            ['tipo' => $c['tipo'], 'ativo' => true]
        ));

        $catReceita  = $cats->where('nome', 'Serviços')->first();
        $catDespesa  = $cats->where('nome', 'Despesas')->first();
        $catAluguel  = $cats->where('nome', 'Aluguel')->first();

        // Centro de custo
        $centro = CentroCusto::firstOrCreate(
            ['empresa_id' => $empresa->id, 'nome' => 'Administrativo'],
            ['ativo' => true]
        );

        // Formas de pagamento
        $pix  = FormaPagamento::firstOrCreate(
            ['nome' => 'PIX'],
            ['empresa_id' => null, 'tipo' => 'pix', 'ativo' => true]
        );
        $ted  = FormaPagamento::firstOrCreate(
            ['nome' => 'TED/DOC'],
            ['empresa_id' => null, 'tipo' => 'transferencia', 'ativo' => true]
        );
        $boleto = FormaPagamento::firstOrCreate(
            ['nome' => 'Boleto'],
            ['empresa_id' => null, 'tipo' => 'boleto', 'ativo' => true]
        );

        // Conta bancária
        $conta = ContaBancaria::firstOrCreate(
            ['empresa_id' => $empresa->id, 'numero_conta' => '12345-6'],
            [
                'nome'          => 'Conta Principal — Itaú',
                'banco_nome'    => 'Itaú',
                'banco_codigo'  => '341',
                'agencia'       => '0001',
                'tipo_conta'    => 'corrente',
                'saldo_inicial' => 10000.00,
                'saldo_atual'   => 10000.00,
                'ativo'         => true,
            ]
        );

        // Clientes demo
        $clientes = collect(['Acme Corp', 'Tech Solutions', 'Construtora Alpha', 'Mercado Beta'])
            ->map(fn($n) => Cliente::firstOrCreate(
                ['empresa_id' => $empresa->id, 'nome_razao_social' => $n],
                ['email' => strtolower(str_replace([' ', '/'], ['', ''], $n)) . '@demo.com', 'tipo' => 'pessoa_juridica', 'ativo' => true]
            ));

        // Fornecedores demo
        $fornecedores = collect(['Locadora de Espaços SA', 'Software House Ltda', 'Marketing Digital ME'])
            ->map(fn($n) => Fornecedor::firstOrCreate(
                ['empresa_id' => $empresa->id, 'nome_razao_social' => $n],
                ['tipo' => 'pessoa_juridica', 'ativo' => true]
            ));

        // Contas a receber
        foreach ($clientes as $i => $cliente) {
            ContaReceber::firstOrCreate(
                ['empresa_id' => $empresa->id, 'numero_documento' => 'REC-' . ($i + 1)],
                [
                    'empresa_id'   => $empresa->id,
                    'user_id'      => $admin->id,
                    'cliente_id'   => $cliente->id,
                    'categoria_id' => $catReceita->id,
                    'descricao'    => 'Serviços de consultoria — ' . $cliente->nome_razao_social,
                    'valor_total'  => rand(2000, 15000),
                    'valor_recebido' => 0,
                    'data_vencimento' => today()->addDays(rand(-10, 30)),
                    'status'       => 'pendente',
                ]
            );
        }

        // Contas a pagar
        $pagamentos = [
            ['desc' => 'Aluguel do escritório', 'valor' => 3500, 'dias' => 5,  'cat' => $catAluguel],
            ['desc' => 'Licença de software',    'valor' => 850,  'dias' => 15, 'cat' => $catDespesa],
            ['desc' => 'Energia elétrica',       'valor' => 420,  'dias' => 20, 'cat' => $catDespesa],
            ['desc' => 'Internet',                'valor' => 250,  'dias' => 25, 'cat' => $catDespesa],
        ];

        foreach ($pagamentos as $i => $p) {
            ContaPagar::firstOrCreate(
                ['empresa_id' => $empresa->id, 'descricao' => $p['desc']],
                [
                    'empresa_id'        => $empresa->id,
                    'user_id'           => $admin->id,
                    'fornecedor_id'     => $fornecedores->get($i % $fornecedores->count())->id,
                    'categoria_id'      => $p['cat']->id,
                    'forma_pagamento_id'=> $pix->id,
                    'conta_bancaria_id' => $conta->id,
                    'descricao'         => $p['desc'],
                    'valor_total'       => $p['valor'],
                    'valor_pago'        => 0,
                    'data_vencimento'   => today()->addDays($p['dias']),
                    'status'            => 'pendente',
                ]
            );
        }

        // Movimentações recentes
        $movs = [
            ['tipo' => 'entrada', 'desc' => 'Recebimento de serviços — Tech Solutions', 'valor' => 8500],
            ['tipo' => 'entrada', 'desc' => 'Consultoria mensal — Acme Corp',            'valor' => 5200],
            ['tipo' => 'saida',   'desc' => 'Pagamento de salários',                     'valor' => 12000],
            ['tipo' => 'saida',   'desc' => 'Aluguel maio/2026',                          'valor' => 3500],
            ['tipo' => 'entrada', 'desc' => 'Contrato de manutenção',                    'valor' => 2800],
        ];

        $saldo = $conta->saldo_atual;
        foreach ($movs as $i => $m) {
            MovimentacaoCaixa::firstOrCreate(
                ['empresa_id' => $empresa->id, 'descricao' => $m['desc']],
                [
                    'empresa_id'        => $empresa->id,
                    'conta_bancaria_id' => $conta->id,
                    'categoria_id'      => $m['tipo'] === 'entrada' ? $catReceita->id : $catDespesa->id,
                    'user_id'           => $admin->id,
                    'tipo'              => $m['tipo'],
                    'descricao'         => $m['desc'],
                    'valor'             => $m['valor'],
                    'data_movimentacao' => today()->subDays(rand(1, 30)),
                    'conciliado'        => $i % 2 === 0,
                ]
            );

            $delta = $m['tipo'] === 'entrada' ? $m['valor'] : -$m['valor'];
            $saldo += $delta;
        }

        $conta->update(['saldo_atual' => $saldo]);

        $this->command->info("Demo criado: empresa={$empresa->id}, login=admin@demo.com / password");
    }
}
