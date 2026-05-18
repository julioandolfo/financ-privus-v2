<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\PedidoItem;
use App\Models\PedidoVinculado;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PedidoVinculadoController extends Controller
{
    // -------------------------------------------------------------------------
    // index — list with filters, paginated 25
    // -------------------------------------------------------------------------

    public function index(Request $request): View
    {
        $empresaId = auth()->user()->empresa_id;

        $query = PedidoVinculado::doEmpresa($empresaId)
            ->with(['cliente'])
            ->when($request->filled('origem'),     fn($q) => $q->where('origem', $request->origem))
            ->when($request->filled('status'),     fn($q) => $q->where('status', $request->status))
            ->when($request->filled('cliente_id'), fn($q) => $q->where('cliente_id', $request->cliente_id))
            ->when($request->filled('data_inicio'), fn($q) => $q->whereDate('data_pedido', '>=', $request->data_inicio))
            ->when($request->filled('data_fim'),    fn($q) => $q->whereDate('data_pedido', '<=', $request->data_fim))
            ->when($request->filled('busca'), fn($q) => $q->where(function ($q) use ($request) {
                $q->where('numero_pedido', 'like', '%' . $request->busca . '%')
                  ->orWhereHas('cliente', fn($c) => $c->where('nome_razao_social', 'like', '%' . $request->busca . '%')
                                                       ->orWhere('nome_fantasia',   'like', '%' . $request->busca . '%'));
            }))
            ->orderByDesc('data_pedido')
            ->orderByDesc('id');

        $pedidos = $query->paginate(25)->withQueryString();

        // Stats for the stats bar
        $statsQuery = PedidoVinculado::doEmpresa($empresaId);
        $totalPedidos   = $statsQuery->count();
        $valorTotal     = $statsQuery->sum('valor_total');
        $custoTotal     = $statsQuery->sum('valor_custo_total');
        $margemMedia    = $valorTotal > 0
            ? (($valorTotal - $custoTotal) / $valorTotal) * 100
            : 0;

        $porStatus = PedidoVinculado::doEmpresa($empresaId)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        $clientes = Cliente::where('empresa_id', $empresaId)
            ->where('ativo', true)
            ->orderBy('nome_razao_social')
            ->get();

        return view('pedidos.index', compact(
            'pedidos',
            'totalPedidos',
            'valorTotal',
            'custoTotal',
            'margemMedia',
            'porStatus',
            'clientes',
        ));
    }

    // -------------------------------------------------------------------------
    // create — show form
    // -------------------------------------------------------------------------

    public function create(): View
    {
        $empresaId = auth()->user()->empresa_id;

        $clientes = Cliente::where('empresa_id', $empresaId)
            ->where('ativo', true)
            ->orderBy('nome_razao_social')
            ->get();

        return view('pedidos.create', compact('clientes'));
    }

    // -------------------------------------------------------------------------
    // store — create order with items
    // -------------------------------------------------------------------------

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'cliente_id'    => 'nullable|exists:clientes,id',
            'origem'        => 'required|in:manual,woocommerce,marketplace',
            'origem_id'     => 'nullable|string|max:100',
            'numero_pedido' => 'required|string|max:100',
            'status'        => 'required|in:pendente,processando,concluido,cancelado,reembolsado',
            'status_origem' => 'nullable|string|max:50',
            'desconto'      => 'nullable|numeric|min:0',
            'data_pedido'   => 'required|date',
            'observacoes'   => 'nullable|string',
            'itens'         => 'required|array|min:1',
            'itens.*.nome_produto'           => 'required|string|max:255',
            'itens.*.produto_id'             => 'nullable|exists:produtos,id',
            'itens.*.codigo_produto_origem'  => 'nullable|string|max:100',
            'itens.*.quantidade'             => 'required|numeric|min:0.001',
            'itens.*.valor_unitario'         => 'required|numeric|min:0',
            'itens.*.custo_unitario'         => 'nullable|numeric|min:0',
        ]);

        $empresaId = auth()->user()->empresa_id;

        $pedido = DB::transaction(function () use ($data, $empresaId): PedidoVinculado {
            $pedido = PedidoVinculado::create([
                'empresa_id'    => $empresaId,
                'cliente_id'    => $data['cliente_id'] ?? null,
                'origem'        => $data['origem'],
                'origem_id'     => $data['origem_id'] ?? null,
                'numero_pedido' => $data['numero_pedido'],
                'status'        => $data['status'],
                'status_origem' => $data['status_origem'] ?? null,
                'desconto'      => $data['desconto'] ?? 0,
                'data_pedido'   => $data['data_pedido'],
                'observacoes'   => $data['observacoes'] ?? null,
                'valor_total'       => 0,
                'valor_custo_total' => 0,
            ]);

            foreach ($data['itens'] as $item) {
                $quantidade    = (float) $item['quantidade'];
                $valorUnit     = (float) $item['valor_unitario'];
                $custoUnit     = (float) ($item['custo_unitario'] ?? 0);

                PedidoItem::create([
                    'pedido_id'             => $pedido->id,
                    'produto_id'            => $item['produto_id'] ?? null,
                    'codigo_produto_origem' => $item['codigo_produto_origem'] ?? null,
                    'nome_produto'          => $item['nome_produto'],
                    'quantidade'            => $quantidade,
                    'valor_unitario'        => $valorUnit,
                    'valor_total'           => round($quantidade * $valorUnit, 2),
                    'custo_unitario'        => $custoUnit,
                    'custo_total'           => round($quantidade * $custoUnit, 2),
                ]);
            }

            $pedido->recalcularTotais();

            return $pedido;
        });

        return redirect()
            ->route('pedidos.show', $pedido)
            ->with('success', 'Pedido criado com sucesso.');
    }

    // -------------------------------------------------------------------------
    // show — detail view
    // -------------------------------------------------------------------------

    public function show(PedidoVinculado $pedido): View
    {
        $this->authorizeEmpresa($pedido);

        $pedido->load(['cliente', 'itens.produto']);

        // Linked contas a receber if column exists
        $contasReceber = collect();
        if (DB::getSchemaBuilder()->hasColumn('contas_receber', 'pedido_id')) {
            $contasReceber = \App\Models\ContaReceber::where('pedido_id', $pedido->id)->with('cliente')->get();
        }

        return view('pedidos.show', compact('pedido', 'contasReceber'));
    }

    // -------------------------------------------------------------------------
    // edit — edit form
    // -------------------------------------------------------------------------

    public function edit(PedidoVinculado $pedido): View
    {
        $this->authorizeEmpresa($pedido);

        $pedido->load(['itens.produto']);

        $empresaId = auth()->user()->empresa_id;

        $clientes = Cliente::where('empresa_id', $empresaId)
            ->where('ativo', true)
            ->orderBy('nome_razao_social')
            ->get();

        return view('pedidos.edit', compact('pedido', 'clientes'));
    }

    // -------------------------------------------------------------------------
    // update — update order and items
    // -------------------------------------------------------------------------

    public function update(Request $request, PedidoVinculado $pedido): RedirectResponse
    {
        $this->authorizeEmpresa($pedido);

        $data = $request->validate([
            'cliente_id'    => 'nullable|exists:clientes,id',
            'origem'        => 'required|in:manual,woocommerce,marketplace',
            'origem_id'     => 'nullable|string|max:100',
            'numero_pedido' => 'required|string|max:100',
            'status'        => 'required|in:pendente,processando,concluido,cancelado,reembolsado',
            'status_origem' => 'nullable|string|max:50',
            'desconto'      => 'nullable|numeric|min:0',
            'data_pedido'   => 'required|date',
            'observacoes'   => 'nullable|string',
            'itens'         => 'required|array|min:1',
            'itens.*.nome_produto'           => 'required|string|max:255',
            'itens.*.produto_id'             => 'nullable|exists:produtos,id',
            'itens.*.codigo_produto_origem'  => 'nullable|string|max:100',
            'itens.*.quantidade'             => 'required|numeric|min:0.001',
            'itens.*.valor_unitario'         => 'required|numeric|min:0',
            'itens.*.custo_unitario'         => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($pedido, $data): void {
            $pedido->update([
                'cliente_id'    => $data['cliente_id'] ?? null,
                'origem'        => $data['origem'],
                'origem_id'     => $data['origem_id'] ?? null,
                'numero_pedido' => $data['numero_pedido'],
                'status'        => $data['status'],
                'status_origem' => $data['status_origem'] ?? null,
                'desconto'      => $data['desconto'] ?? 0,
                'data_pedido'   => $data['data_pedido'],
                'observacoes'   => $data['observacoes'] ?? null,
            ]);

            // Replace all items
            $pedido->itens()->delete();

            foreach ($data['itens'] as $item) {
                $quantidade = (float) $item['quantidade'];
                $valorUnit  = (float) $item['valor_unitario'];
                $custoUnit  = (float) ($item['custo_unitario'] ?? 0);

                PedidoItem::create([
                    'pedido_id'             => $pedido->id,
                    'produto_id'            => $item['produto_id'] ?? null,
                    'codigo_produto_origem' => $item['codigo_produto_origem'] ?? null,
                    'nome_produto'          => $item['nome_produto'],
                    'quantidade'            => $quantidade,
                    'valor_unitario'        => $valorUnit,
                    'valor_total'           => round($quantidade * $valorUnit, 2),
                    'custo_unitario'        => $custoUnit,
                    'custo_total'           => round($quantidade * $custoUnit, 2),
                ]);
            }

            $pedido->recalcularTotais();
        });

        return redirect()
            ->route('pedidos.show', $pedido)
            ->with('success', 'Pedido atualizado com sucesso.');
    }

    // -------------------------------------------------------------------------
    // destroy — soft delete
    // -------------------------------------------------------------------------

    public function destroy(PedidoVinculado $pedido): RedirectResponse
    {
        $this->authorizeEmpresa($pedido);
        $pedido->delete();

        return redirect()
            ->route('pedidos.index')
            ->with('success', 'Pedido removido com sucesso.');
    }

    // -------------------------------------------------------------------------
    // statusMassa — bulk status update (JSON body: {ids:[], status:''})
    // -------------------------------------------------------------------------

    public function statusMassa(Request $request): JsonResponse|RedirectResponse
    {
        $data = $request->validate([
            'ids'    => 'required|array|min:1',
            'ids.*'  => 'integer',
            'status' => 'required|in:pendente,processando,concluido,cancelado,reembolsado',
        ]);

        $empresaId = auth()->user()->empresa_id;

        $updated = PedidoVinculado::doEmpresa($empresaId)
            ->whereIn('id', $data['ids'])
            ->update(['status' => $data['status']]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => "{$updated} pedido(s) atualizado(s).",
                'updated' => $updated,
            ]);
        }

        return redirect()
            ->route('pedidos.index')
            ->with('success', "{$updated} pedido(s) atualizado(s) para '{$data['status']}'.");
    }

    // -------------------------------------------------------------------------
    // Private helpers
    // -------------------------------------------------------------------------

    private function authorizeEmpresa(PedidoVinculado $pedido): void
    {
        abort_if($pedido->empresa_id !== auth()->user()->empresa_id, 403);
    }
}
