<?php

namespace App\Console\Commands;

use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\DespesaRecorrente;
use App\Models\ReceitaRecorrente;
use Illuminate\Console\Command;

class ProcessarRecorrencias extends Command
{
    protected $signature   = 'recorrencias:processar {--dry-run : Simula sem gravar}';
    protected $description = 'Gera contas a pagar/receber de recorrências pendentes';

    public function handle(): int
    {
        $dry = $this->option('dry-run');

        $despesas = DespesaRecorrente::paraGerar()->with('empresa')->get();
        $receitas = ReceitaRecorrente::paraGerar()->with('empresa')->get();

        $this->info("Despesas a gerar: {$despesas->count()} | Receitas a gerar: {$receitas->count()}");

        if ($dry) {
            $despesas->each(fn($d) => $this->line("  [DRY] Despesa: {$d->descricao} | Venc: {$d->proxima_geracao}"));
            $receitas->each(fn($r) => $this->line("  [DRY] Receita: {$r->descricao} | Venc: {$r->proxima_geracao}"));
            return self::SUCCESS;
        }

        $geradas = 0;
        $erros   = 0;

        foreach ($despesas as $rec) {
            try {
                ContaPagar::create([
                    'empresa_id'            => $rec->empresa_id,
                    'user_id'               => $rec->user_id,
                    'fornecedor_id'         => $rec->fornecedor_id,
                    'categoria_id'          => $rec->categoria_id,
                    'centro_custo_id'       => $rec->centro_custo_id,
                    'forma_pagamento_id'    => $rec->forma_pagamento_id,
                    'conta_bancaria_id'     => $rec->conta_bancaria_id,
                    'despesa_recorrente_id' => $rec->id,
                    'descricao'             => $rec->descricao,
                    'valor_total'           => $rec->valor,
                    'valor_pago'            => 0,
                    'data_vencimento'       => $rec->proxima_geracao,
                    'status'                => $rec->status_inicial,
                    'observacoes'           => $rec->observacoes,
                ]);

                $proxima = $rec->calcularProximaGeracao();
                $rec->increment('ocorrencias_geradas');
                $rec->update(['proxima_geracao' => $proxima, 'ultima_geracao' => today()]);
                $geradas++;
            } catch (\Throwable $e) {
                $this->error("Despesa #{$rec->id}: {$e->getMessage()}");
                $erros++;
            }
        }

        foreach ($receitas as $rec) {
            try {
                ContaReceber::create([
                    'empresa_id'            => $rec->empresa_id,
                    'user_id'               => $rec->user_id,
                    'cliente_id'            => $rec->cliente_id,
                    'categoria_id'          => $rec->categoria_id,
                    'centro_custo_id'       => $rec->centro_custo_id,
                    'forma_recebimento_id'  => $rec->forma_pagamento_id,
                    'conta_bancaria_id'     => $rec->conta_bancaria_id,
                    'receita_recorrente_id' => $rec->id,
                    'descricao'             => $rec->descricao,
                    'valor_total'           => $rec->valor,
                    'valor_recebido'        => 0,
                    'data_vencimento'       => $rec->proxima_geracao,
                    'status'                => $rec->status_inicial,
                    'observacoes'           => $rec->observacoes,
                ]);

                $proxima = $rec->calcularProximaGeracao();
                $rec->increment('ocorrencias_geradas');
                $rec->update(['proxima_geracao' => $proxima, 'ultima_geracao' => today()]);
                $geradas++;
            } catch (\Throwable $e) {
                $this->error("Receita #{$rec->id}: {$e->getMessage()}");
                $erros++;
            }
        }

        $this->info("Concluído: {$geradas} gerada(s), {$erros} erro(s).");

        return $erros > 0 ? self::FAILURE : self::SUCCESS;
    }
}
