<?php

namespace App\Http\Controllers;

use App\Models\CategoriaFinanceira;
use App\Models\ContaBancaria;
use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\TransacaoPendente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TransacaoPendenteController extends Controller
{
    public function index(Request $request): View
    {
        $empresaId = auth()->user()->empresa_id;

        $query = TransacaoPendente::where('empresa_id', $empresaId)
            ->with(['contaBancaria', 'categoriaSugerida'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->tipo, fn($q) => $q->where('tipo', $request->tipo))
            ->when($request->conta_bancaria_id, fn($q) => $q->where('conta_bancaria_id', $request->conta_bancaria_id))
            ->when($request->data_inicio, fn($q) => $q->whereDate('data_transacao', '>=', $request->data_inicio))
            ->when($request->data_fim, fn($q) => $q->whereDate('data_transacao', '<=', $request->data_fim))
            ->orderBy('data_transacao', 'desc')
            ->orderBy('id', 'desc');

        $transacoes = $query->paginate(30)->withQueryString();

        // Stats
        $stats = TransacaoPendente::where('empresa_id', $empresaId)
            ->selectRaw("
                COUNT(CASE WHEN status = 'pendente' THEN 1 END) as total_pendentes,
                COUNT(CASE WHEN status = 'aprovada' THEN 1 END) as total_aprovadas,
                COUNT(CASE WHEN status = 'ignorada' THEN 1 END) as total_ignoradas,
                COALESCE(SUM(CASE WHEN status = 'pendente' THEN valor ELSE 0 END), 0) as valor_pendente
            ")
            ->first();

        $contasBancarias = ContaBancaria::where('empresa_id', $empresaId)
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();

        $categorias = CategoriaFinanceira::where('empresa_id', $empresaId)
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();

        return view('transacoes-pendentes.index', compact(
            'transacoes',
            'stats',
            'contasBancarias',
            'categorias',
        ));
    }

    public function aprovar(Request $request, int $id): JsonResponse
    {
        $empresaId = auth()->user()->empresa_id;

        $transacao = TransacaoPendente::where('empresa_id', $empresaId)
            ->where('status', 'pendente')
            ->findOrFail($id);

        $data = $request->validate([
            'categoria_id'       => 'nullable|exists:categorias_financeiras,id',
            'descricao'          => 'nullable|string|max:255',
            'data_vencimento'    => 'nullable|date',
            'conta_bancaria_id'  => 'nullable|exists:contas_bancarias,id',
        ]);

        $descricao        = $data['descricao'] ?? ($transacao->descricao_normalizada ?? $transacao->descricao_original);
        $categoriaId      = $data['categoria_id'] ?? $transacao->categoria_sugerida_id;
        $dataVencimento   = $data['data_vencimento'] ?? $transacao->data_transacao->format('Y-m-d');
        $contaBancariaId  = $data['conta_bancaria_id'] ?? $transacao->conta_bancaria_id;

        DB::transaction(function () use ($transacao, $empresaId, $descricao, $categoriaId, $dataVencimento, $contaBancariaId) {
            if ($transacao->tipo === 'debito') {
                $conta = ContaPagar::create([
                    'empresa_id'       => $empresaId,
                    'categoria_id'     => $categoriaId,
                    'conta_bancaria_id' => $contaBancariaId,
                    'user_id'          => auth()->id(),
                    'descricao'        => $descricao,
                    'valor_total'      => $transacao->valor,
                    'valor_pago'       => $transacao->valor,
                    'data_vencimento'  => $dataVencimento,
                    'data_pagamento'   => $transacao->data_transacao,
                    'status'           => 'pago',
                ]);

                $transacao->update([
                    'conta_pagar_id' => $conta->id,
                    'status'         => 'aprovada',
                    'aprovada_por'   => auth()->id(),
                    'aprovada_em'    => now(),
                ]);
            } else {
                $conta = ContaReceber::create([
                    'empresa_id'       => $empresaId,
                    'categoria_id'     => $categoriaId,
                    'conta_bancaria_id' => $contaBancariaId,
                    'user_id'          => auth()->id(),
                    'descricao'        => $descricao,
                    'valor_total'      => $transacao->valor,
                    'valor_recebido'   => $transacao->valor,
                    'data_vencimento'  => $dataVencimento,
                    'data_recebimento' => $transacao->data_transacao,
                    'status'           => 'recebido',
                ]);

                $transacao->update([
                    'conta_receber_id' => $conta->id,
                    'status'           => 'aprovada',
                    'aprovada_por'     => auth()->id(),
                    'aprovada_em'      => now(),
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Transação aprovada com sucesso.',
        ]);
    }

    public function ignorar(int $id): JsonResponse
    {
        $empresaId = auth()->user()->empresa_id;

        $transacao = TransacaoPendente::where('empresa_id', $empresaId)
            ->where('status', 'pendente')
            ->findOrFail($id);

        $transacao->update(['status' => 'ignorada']);

        return response()->json([
            'success' => true,
            'message' => 'Transação ignorada.',
        ]);
    }

    public function aprovarLote(Request $request): JsonResponse
    {
        $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer',
        ]);

        $empresaId = auth()->user()->empresa_id;

        $transacoes = TransacaoPendente::where('empresa_id', $empresaId)
            ->where('status', 'pendente')
            ->whereIn('id', $request->ids)
            ->get();

        $aprovadas = 0;
        $erros     = 0;

        DB::transaction(function () use ($transacoes, $empresaId, &$aprovadas, &$erros) {
            foreach ($transacoes as $transacao) {
                try {
                    $descricao = $transacao->descricao_normalizada ?? $transacao->descricao_original;

                    if ($transacao->tipo === 'debito') {
                        $conta = ContaPagar::create([
                            'empresa_id'       => $empresaId,
                            'categoria_id'     => $transacao->categoria_sugerida_id,
                            'conta_bancaria_id' => $transacao->conta_bancaria_id,
                            'user_id'          => auth()->id(),
                            'descricao'        => $descricao,
                            'valor_total'      => $transacao->valor,
                            'valor_pago'       => $transacao->valor,
                            'data_vencimento'  => $transacao->data_transacao,
                            'data_pagamento'   => $transacao->data_transacao,
                            'status'           => 'pago',
                        ]);

                        $transacao->update([
                            'conta_pagar_id' => $conta->id,
                            'status'         => 'aprovada',
                            'aprovada_por'   => auth()->id(),
                            'aprovada_em'    => now(),
                        ]);
                    } else {
                        $conta = ContaReceber::create([
                            'empresa_id'       => $empresaId,
                            'categoria_id'     => $transacao->categoria_sugerida_id,
                            'conta_bancaria_id' => $transacao->conta_bancaria_id,
                            'user_id'          => auth()->id(),
                            'descricao'        => $descricao,
                            'valor_total'      => $transacao->valor,
                            'valor_recebido'   => $transacao->valor,
                            'data_vencimento'  => $transacao->data_transacao,
                            'data_recebimento' => $transacao->data_transacao,
                            'status'           => 'recebido',
                        ]);

                        $transacao->update([
                            'conta_receber_id' => $conta->id,
                            'status'           => 'aprovada',
                            'aprovada_por'     => auth()->id(),
                            'aprovada_em'      => now(),
                        ]);
                    }

                    $aprovadas++;
                } catch (\Throwable) {
                    $erros++;
                }
            }
        });

        return response()->json([
            'success'   => true,
            'aprovadas' => $aprovadas,
            'erros'     => $erros,
            'message'   => "{$aprovadas} transação(ões) aprovada(s)" . ($erros > 0 ? ", {$erros} com erro(s)." : '.'),
        ]);
    }
}
