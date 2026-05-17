<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MigrateLegado extends Command
{
    protected $signature = 'migrate:legado
                            {--fresh : Limpa as tabelas do novo sistema antes de importar}
                            {--only=* : Migrar apenas estas entidades (ex: --only=clientes --only=fornecedores)}
                            {--skip=* : Pular estas entidades}';

    protected $description = 'Migra os dados do sistema legado (financeiro) para o novo sistema Laravel';

    private \Illuminate\Database\Connection $legado;

    private array $errors = [];

    public function handle(): int
    {
        try {
            $this->legado = DB::connection('legado');
            $this->legado->statement('SELECT 1');
        } catch (\Throwable $e) {
            $this->error('Não foi possível conectar ao banco legado: ' . $e->getMessage());
            $this->line('Configure LEGADO_DB_HOST, LEGADO_DB_DATABASE, LEGADO_DB_USERNAME e LEGADO_DB_PASSWORD no .env');
            return self::FAILURE;
        }

        $etapas = [
            'empresas'              => fn() => $this->migrarEmpresas(),
            'categorias'            => fn() => $this->migrarCategorias(),
            'centros_custo'         => fn() => $this->migrarCentrosCusto(),
            'formas_pagamento'      => fn() => $this->migrarFormasPagamento(),
            'usuarios'              => fn() => $this->migrarUsuarios(),
            'clientes'              => fn() => $this->migrarClientes(),
            'fornecedores'          => fn() => $this->migrarFornecedores(),
            'contas_bancarias'      => fn() => $this->migrarContasBancarias(),
            'contas_pagar'          => fn() => $this->migrarContasPagar(),
            'contas_receber'        => fn() => $this->migrarContasReceber(),
            'parcelas_receber'      => fn() => $this->migrarParcelasReceber(),
        ];

        $only = $this->option('only');
        $skip = $this->option('skip');

        if ($only) {
            $etapas = array_filter($etapas, fn($k) => in_array($k, $only), ARRAY_FILTER_USE_KEY);
        }
        if ($skip) {
            $etapas = array_filter($etapas, fn($k) => !in_array($k, $skip), ARRAY_FILTER_USE_KEY);
        }

        if ($this->option('fresh')) {
            if (!$this->confirm('⚠️  Isso irá APAGAR todos os dados do novo sistema antes de importar. Continuar?')) {
                return self::FAILURE;
            }
            $this->limparTabelas(array_keys($etapas));
        }

        $this->info('');
        $this->info('=== Iniciando migração do sistema legado ===');
        $this->info('');

        foreach ($etapas as $nome => $fn) {
            $this->info("► {$nome}...");
            try {
                $fn();
            } catch (\Throwable $e) {
                $this->errors[] = "[{$nome}] " . $e->getMessage();
                $this->warn("  ✗ Erro: " . $e->getMessage());
            }
        }

        $this->info('');
        $this->info('=== Migração concluída ===');

        if ($this->errors) {
            $this->warn('');
            $this->warn('Erros encontrados:');
            foreach ($this->errors as $err) {
                $this->warn("  - {$err}");
            }
            return self::FAILURE;
        }

        $this->info('Todos os dados foram migrados com sucesso!');
        return self::SUCCESS;
    }

    // -------------------------------------------------------------------------
    // EMPRESAS
    // -------------------------------------------------------------------------
    private function migrarEmpresas(): void
    {
        $rows = $this->legado->table('empresas')->get();
        $count = 0;

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($rows as $r) {
            DB::table('empresas')->updateOrInsert(
                ['id' => $r->id],
                [
                    'id'                   => $r->id,
                    'codigo'               => $r->codigo,
                    'razao_social'         => $r->razao_social,
                    'nome_fantasia'        => $r->nome_fantasia ?? null,
                    'cnpj'                 => $r->cnpj ?? null,
                    'grupo_empresarial_id' => $r->grupo_empresarial_id ?? null,
                    'ativo'                => $r->ativo ?? true,
                    'configuracoes'        => $r->configuracoes ?? null,
                    'created_at'           => $r->data_cadastro ?? now(),
                    'updated_at'           => $r->data_cadastro ?? now(),
                ]
            );
            $count++;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $this->line("  ✓ {$count} empresas");
    }

    // -------------------------------------------------------------------------
    // CATEGORIAS FINANCEIRAS
    // -------------------------------------------------------------------------
    private function migrarCategorias(): void
    {
        $rows = $this->legado->table('categorias_financeiras')->orderBy('id')->get();
        $count = 0;

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($rows as $r) {
            DB::table('categorias_financeiras')->updateOrInsert(
                ['id' => $r->id],
                [
                    'id'              => $r->id,
                    'empresa_id'      => $r->empresa_id,
                    'codigo'          => $r->codigo ?? null,
                    'nome'            => $r->nome,
                    'tipo'            => $r->tipo ?? 'ambos',
                    'categoria_pai_id'=> $r->categoria_pai_id ?? null,
                    'ativo'           => $r->ativo ?? true,
                    'created_at'      => $r->data_cadastro ?? now(),
                    'updated_at'      => $r->data_cadastro ?? now(),
                ]
            );
            $count++;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $this->line("  ✓ {$count} categorias financeiras");
    }

    // -------------------------------------------------------------------------
    // CENTROS DE CUSTO
    // -------------------------------------------------------------------------
    private function migrarCentrosCusto(): void
    {
        $rows = $this->legado->table('centros_custo')->orderBy('id')->get();
        $count = 0;

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($rows as $r) {
            DB::table('centros_custo')->updateOrInsert(
                ['id' => $r->id],
                [
                    'id'           => $r->id,
                    'empresa_id'   => $r->empresa_id,
                    'codigo'       => $r->codigo ?? null,
                    'nome'         => $r->nome,
                    'centro_pai_id'=> $r->centro_pai_id ?? null,
                    'ativo'        => $r->ativo ?? true,
                    'created_at'   => $r->data_cadastro ?? now(),
                    'updated_at'   => $r->data_cadastro ?? now(),
                ]
            );
            $count++;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $this->line("  ✓ {$count} centros de custo");
    }

    // -------------------------------------------------------------------------
    // FORMAS DE PAGAMENTO
    // -------------------------------------------------------------------------
    private function migrarFormasPagamento(): void
    {
        $rows = $this->legado->table('formas_pagamento')->get();
        $count = 0;

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($rows as $r) {
            DB::table('formas_pagamento')->updateOrInsert(
                ['id' => $r->id],
                [
                    'id'         => $r->id,
                    'empresa_id' => $r->empresa_id ?? null,
                    'codigo'     => $r->codigo ?? null,
                    'nome'       => $r->nome,
                    'tipo'       => $r->tipo ?? 'ambos',
                    'padrao'     => false,
                    'ativo'      => $r->ativo ?? true,
                    'created_at' => $r->data_cadastro ?? now(),
                    'updated_at' => $r->data_cadastro ?? now(),
                ]
            );
            $count++;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $this->line("  ✓ {$count} formas de pagamento");
    }

    // -------------------------------------------------------------------------
    // USUÁRIOS
    // -------------------------------------------------------------------------
    private function migrarUsuarios(): void
    {
        $rows = $this->legado->table('usuarios')->get();
        $count = 0;
        $senhaAviso = false;

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($rows as $r) {
            $senha = $r->senha ?? '';

            // Detecta se já é bcrypt ($2y$) ou argon2, caso contrário força reset
            $isBcrypt = str_starts_with($senha, '$2y$') || str_starts_with($senha, '$2b$') || str_starts_with($senha, '$argon');
            if (!$isBcrypt) {
                $senha = Hash::make('Alterar@' . $r->id); // senha temporária
                $senhaAviso = true;
            }

            DB::table('users')->updateOrInsert(
                ['id' => $r->id],
                [
                    'id'         => $r->id,
                    'empresa_id' => $r->empresa_id ?? null,
                    'name'       => $r->nome,
                    'email'      => $r->email,
                    'password'   => $senha,
                    'role'       => 'user',
                    'ativo'      => $r->ativo ?? true,
                    'created_at' => $r->data_cadastro ?? now(),
                    'updated_at' => $r->data_cadastro ?? now(),
                ]
            );
            $count++;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $this->line("  ✓ {$count} usuários");

        if ($senhaAviso) {
            $this->warn('  ⚠  Senhas não-bcrypt detectadas. Senha temporária: Alterar@{id} — peça para os usuários resetarem.');
        }
    }

    // -------------------------------------------------------------------------
    // CLIENTES
    // -------------------------------------------------------------------------
    private function migrarClientes(): void
    {
        $total = $this->legado->table('clientes')->count();
        $count = 0;

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $this->legado->table('clientes')->orderBy('id')->chunk(500, function ($rows) use (&$count) {
            $insert = [];
            foreach ($rows as $r) {
                $insert[] = [
                    'id'              => $r->id,
                    'empresa_id'      => $r->empresa_id,
                    'codigo'          => null,
                    'tipo'            => $r->tipo ?? 'juridica',
                    'nome_razao_social'=> $r->nome_razao_social,
                    'nome_fantasia'   => null,
                    'cpf_cnpj'        => $r->cpf_cnpj ?? null,
                    'email'           => $r->email ?? null,
                    'telefone'        => $r->telefone ?? null,
                    'celular'         => null,
                    'endereco'        => $this->normalizarEndereco($r->endereco ?? null),
                    'observacoes'     => null,
                    'ativo'           => $r->ativo ?? true,
                    'created_at'      => $r->data_cadastro ?? now(),
                    'updated_at'      => $r->data_cadastro ?? now(),
                    'deleted_at'      => null,
                ];
                $count++;
            }
            DB::table('clientes')->upsert($insert, ['id'], array_keys($insert[0] ?? []));
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $this->line("  ✓ {$count}/{$total} clientes");
    }

    // -------------------------------------------------------------------------
    // FORNECEDORES
    // -------------------------------------------------------------------------
    private function migrarFornecedores(): void
    {
        $total = $this->legado->table('fornecedores')->count();
        $count = 0;

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $this->legado->table('fornecedores')->orderBy('id')->chunk(500, function ($rows) use (&$count) {
            $insert = [];
            foreach ($rows as $r) {
                $insert[] = [
                    'id'              => $r->id,
                    'empresa_id'      => $r->empresa_id,
                    'codigo'          => null,
                    'tipo'            => $r->tipo ?? 'juridica',
                    'nome_razao_social'=> $r->nome_razao_social,
                    'nome_fantasia'   => null,
                    'cpf_cnpj'        => $r->cpf_cnpj ?? null,
                    'email'           => $r->email ?? null,
                    'telefone'        => $r->telefone ?? null,
                    'celular'         => null,
                    'endereco'        => $this->normalizarEndereco($r->endereco ?? null),
                    'observacoes'     => null,
                    'ativo'           => $r->ativo ?? true,
                    'created_at'      => $r->data_cadastro ?? now(),
                    'updated_at'      => $r->data_cadastro ?? now(),
                    'deleted_at'      => null,
                ];
                $count++;
            }
            DB::table('fornecedores')->upsert($insert, ['id'], array_keys($insert[0] ?? []));
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $this->line("  ✓ {$count}/{$total} fornecedores");
    }

    // -------------------------------------------------------------------------
    // CONTAS BANCÁRIAS
    // -------------------------------------------------------------------------
    private function migrarContasBancarias(): void
    {
        // Suporta tanto 'contas_bancarias' quanto 'contatos_bancarios' (nome antigo)
        $tabela = $this->legado->getSchemaBuilder()->hasTable('contas_bancarias')
            ? 'contas_bancarias'
            : 'contatos_bancarios';

        $rows = $this->legado->table($tabela)->get();
        $count = 0;

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($rows as $r) {
            $nome = $r->nome ?? trim(($r->banco_nome ?? '') . ' - ' . ($r->agencia ?? '') . '/' . ($r->conta ?? ''));
            if (!$nome) {
                $nome = 'Conta ' . $r->id;
            }

            DB::table('contas_bancarias')->updateOrInsert(
                ['id' => $r->id],
                [
                    'id'          => $r->id,
                    'empresa_id'  => $r->empresa_id,
                    'nome'        => $nome,
                    'banco_codigo'=> $r->banco_codigo ?? null,
                    'banco_nome'  => $r->banco_nome ?? null,
                    'agencia'     => $r->agencia ?? null,
                    'conta'       => $r->conta ?? null,
                    'tipo_conta'  => $this->normalizarTipoConta($r->tipo_conta ?? 'corrente'),
                    'saldo_inicial'=> $r->saldo_inicial ?? 0,
                    'saldo_atual' => $r->saldo_atual ?? 0,
                    'ativo'       => $r->ativo ?? true,
                    'created_at'  => $r->data_cadastro ?? now(),
                    'updated_at'  => $r->data_cadastro ?? now(),
                ]
            );
            $count++;
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $this->line("  ✓ {$count} contas bancárias (de '{$tabela}')");
    }

    // -------------------------------------------------------------------------
    // CONTAS A PAGAR
    // -------------------------------------------------------------------------
    private function migrarContasPagar(): void
    {
        $total = $this->legado->table('contas_pagar')->count();
        $count = 0;

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $this->legado->table('contas_pagar')->orderBy('id')->chunk(500, function ($rows) use (&$count) {
            $insert = [];
            foreach ($rows as $r) {
                $insert[] = [
                    'id'                 => $r->id,
                    'empresa_id'         => $r->empresa_id,
                    'fornecedor_id'      => $r->fornecedor_id ?? null,
                    'categoria_id'       => $r->categoria_id ?? null,
                    'centro_custo_id'    => $r->centro_custo_id ?? null,
                    'forma_pagamento_id' => $r->forma_pagamento_id ?? null,
                    'conta_bancaria_id'  => $r->conta_bancaria_id ?? null,
                    'user_id'            => $r->usuario_cadastro_id ?? null,
                    'numero_documento'   => $r->numero_documento ?? null,
                    'descricao'          => $r->descricao,
                    'valor_total'        => $r->valor_total ?? 0,
                    'valor_pago'         => $r->valor_pago ?? 0,
                    'desconto'           => $r->desconto ?? 0,
                    'juros'              => $r->juros ?? 0,
                    'multa'              => $r->multa ?? 0,
                    'data_vencimento'    => $r->data_vencimento,
                    'data_competencia'   => $r->data_competencia ?? null,
                    'data_pagamento'     => $r->data_pagamento ?? null,
                    'status'             => $this->normalizarStatusPagar($r->status ?? 'pendente'),
                    'tem_rateio'         => $r->tem_rateio ?? false,
                    'observacoes'        => $r->observacoes ?? null,
                    'created_at'         => $r->data_cadastro ?? now(),
                    'updated_at'         => $r->data_cadastro ?? now(),
                    'deleted_at'         => null,
                ];
                $count++;
            }
            DB::table('contas_pagar')->upsert($insert, ['id'], array_keys($insert[0] ?? []));
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $this->line("  ✓ {$count}/{$total} contas a pagar");
    }

    // -------------------------------------------------------------------------
    // CONTAS A RECEBER
    // -------------------------------------------------------------------------
    private function migrarContasReceber(): void
    {
        $total = $this->legado->table('contas_receber')->count();
        $count = 0;

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $this->legado->table('contas_receber')->orderBy('id')->chunk(500, function ($rows) use (&$count) {
            $insert = [];
            foreach ($rows as $r) {
                $insert[] = [
                    'id'                  => $r->id,
                    'empresa_id'          => $r->empresa_id,
                    'cliente_id'          => $r->cliente_id ?? null,
                    'categoria_id'        => $r->categoria_id ?? null,
                    'centro_custo_id'     => $r->centro_custo_id ?? null,
                    'forma_recebimento_id'=> $r->forma_recebimento_id ?? null,
                    'conta_bancaria_id'   => $r->conta_bancaria_id ?? null,
                    'user_id'             => $r->usuario_cadastro_id ?? null,
                    'numero_documento'    => $r->numero_documento ?? null,
                    'descricao'           => $r->descricao,
                    'valor_total'         => $r->valor_total ?? 0,
                    'valor_recebido'      => $r->valor_recebido ?? 0,
                    'desconto'            => $r->desconto ?? 0,
                    'juros'               => $r->juros ?? 0,
                    'multa'               => $r->multa ?? 0,
                    'data_vencimento'     => $r->data_vencimento,
                    'data_competencia'    => $r->data_competencia ?? null,
                    'data_recebimento'    => $r->data_recebimento ?? null,
                    'status'              => $this->normalizarStatusReceber($r->status ?? 'pendente'),
                    'num_parcelas'        => $r->num_parcelas ?? 1,
                    'tem_rateio'          => $r->tem_rateio ?? false,
                    'observacoes'         => $r->observacoes ?? null,
                    'created_at'          => $r->data_cadastro ?? now(),
                    'updated_at'          => $r->data_cadastro ?? now(),
                    'deleted_at'          => null,
                ];
                $count++;
            }
            DB::table('contas_receber')->upsert($insert, ['id'], array_keys($insert[0] ?? []));
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $this->line("  ✓ {$count}/{$total} contas a receber");
    }

    // -------------------------------------------------------------------------
    // PARCELAS A RECEBER
    // -------------------------------------------------------------------------
    private function migrarParcelasReceber(): void
    {
        if (!$this->legado->getSchemaBuilder()->hasTable('parcelas_receber')) {
            $this->line('  – parcelas_receber: tabela não encontrada no legado, pulando.');
            return;
        }

        $total = $this->legado->table('parcelas_receber')->count();
        $count = 0;

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        $this->legado->table('parcelas_receber')->orderBy('id')->chunk(500, function ($rows) use (&$count) {
            $insert = [];
            foreach ($rows as $r) {
                $insert[] = [
                    'id'                  => $r->id,
                    'conta_receber_id'    => $r->conta_receber_id,
                    'numero_parcela'      => $r->numero_parcela ?? 1,
                    'valor_parcela'       => $r->valor_parcela ?? 0,
                    'valor_recebido'      => $r->valor_recebido ?? 0,
                    'desconto'            => $r->desconto ?? 0,
                    'juros'               => $r->juros ?? 0,
                    'multa'               => $r->multa ?? 0,
                    'data_vencimento'     => $r->data_vencimento,
                    'data_recebimento'    => $r->data_recebimento ?? null,
                    'status'              => $this->normalizarStatusReceber($r->status ?? 'pendente'),
                    'created_at'          => $r->created_at ?? now(),
                    'updated_at'          => $r->updated_at ?? now(),
                ];
                $count++;
            }
            DB::table('parcelas_receber')->upsert($insert, ['id'], array_keys($insert[0] ?? []));
        });

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        $this->line("  ✓ {$count}/{$total} parcelas a receber");
    }

    // -------------------------------------------------------------------------
    // HELPERS
    // -------------------------------------------------------------------------
    private function limparTabelas(array $etapas): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        $mapa = [
            'parcelas_receber' => 'parcelas_receber',
            'contas_receber'   => 'contas_receber',
            'contas_pagar'     => 'contas_pagar',
            'contas_bancarias' => 'contas_bancarias',
            'clientes'         => 'clientes',
            'fornecedores'     => 'fornecedores',
            'usuarios'         => 'users',
            'formas_pagamento' => 'formas_pagamento',
            'centros_custo'    => 'centros_custo',
            'categorias'       => 'categorias_financeiras',
            'empresas'         => 'empresas',
        ];
        foreach ($etapas as $etapa) {
            if (isset($mapa[$etapa])) {
                DB::table($mapa[$etapa])->truncate();
                $this->line("  Truncated: {$mapa[$etapa]}");
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    private function normalizarEndereco(mixed $endereco): ?string
    {
        if (empty($endereco)) return null;
        if ($this->isJson($endereco)) return $endereco; // já é JSON
        return json_encode(['logradouro' => $endereco], JSON_UNESCAPED_UNICODE);
    }

    private function normalizarTipoConta(string $tipo): string
    {
        $map = [
            'conta_corrente' => 'corrente',
            'conta corrente' => 'corrente',
            'corrente'       => 'corrente',
            'poupanca'       => 'poupanca',
            'poupança'       => 'poupanca',
            'investimento'   => 'investimento',
            'caixa'          => 'caixa',
        ];
        return $map[strtolower(trim($tipo))] ?? 'corrente';
    }

    private function normalizarStatusPagar(string $status): string
    {
        $map = [
            'pendente'  => 'pendente',
            'pago'      => 'pago',
            'vencido'   => 'vencido',
            'cancelado' => 'cancelado',
            'parcial'   => 'parcial',
            // variações do legado
            'aberto'    => 'pendente',
            'atrasado'  => 'vencido',
            'quitado'   => 'pago',
        ];
        return $map[strtolower(trim($status))] ?? 'pendente';
    }

    private function normalizarStatusReceber(string $status): string
    {
        $map = [
            'pendente'  => 'pendente',
            'pago'      => 'pago',
            'recebido'  => 'pago',
            'vencido'   => 'vencido',
            'cancelado' => 'cancelado',
            'parcial'   => 'parcial',
            'aberto'    => 'pendente',
            'atrasado'  => 'vencido',
            'quitado'   => 'pago',
        ];
        return $map[strtolower(trim($status))] ?? 'pendente';
    }

    private function isJson(string $str): bool
    {
        json_decode($str);
        return json_last_error() === JSON_ERROR_NONE;
    }
}
