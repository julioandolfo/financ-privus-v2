<?php

namespace App\Http\Controllers;

use App\Models\CategoriaFinanceira;
use App\Models\ContaPagar;
use App\Models\ContaReceber;
use App\Models\PerfilConsolidacao;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PerfilConsolidacaoController extends Controller
{
    public function index(): View
    {
        $empresaId = auth()->user()->empresa_id;
        $userId    = auth()->id();

        $perfis = PerfilConsolidacao::where('empresa_id', $empresaId)
            ->where('ativo', true)
            ->where(function ($q) use ($userId) {
                $q->where('user_id', $userId)
                  ->orWhere('publico', true);
            })
            ->with('user')
            ->orderBy('nome')
            ->get();

        return view('perfis-consolidacao.index', compact('perfis'));
    }

    public function create(): View
    {
        $empresaId  = auth()->user()->empresa_id;
        $categorias = CategoriaFinanceira::where('empresa_id', $empresaId)
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();

        return view('perfis-consolidacao.create', compact('categorias'));
    }

    public function store(Request $request): RedirectResponse
    {
        $empresaId = auth()->user()->empresa_id;

        $data = $request->validate([
            'nome'        => ['required', 'string', 'max:100'],
            'descricao'   => ['nullable', 'string'],
            'periodo'     => ['required', 'in:mes_atual,trimestre,ano'],
            'tipo'        => ['required', 'in:receitas,despesas,ambos'],
            'categorias'  => ['nullable', 'array'],
            'categorias.*'=> ['integer', 'exists:categorias_financeiras,id'],
            'mostrar_grafico' => ['boolean'],
            'publico'     => ['boolean'],
        ]);

        PerfilConsolidacao::create([
            'empresa_id' => $empresaId,
            'user_id'    => auth()->id(),
            'nome'       => $data['nome'],
            'descricao'  => $data['descricao'] ?? null,
            'publico'    => $request->boolean('publico'),
            'configuracao' => [
                'periodo'         => $data['periodo'],
                'categorias'      => $data['categorias'] ?? [],
                'tipo'            => $data['tipo'],
                'mostrar_grafico' => $request->boolean('mostrar_grafico'),
            ],
        ]);

        return redirect()->route('perfis-consolidacao.index')
            ->with('success', 'Perfil de consolidação criado com sucesso.');
    }

    public function show(PerfilConsolidacao $perfisConsolidacao): View
    {
        $this->authorizeView($perfisConsolidacao);

        $config    = $perfisConsolidacao->configuracao;
        $empresaId = auth()->user()->empresa_id;

        [$dataInicio, $dataFim] = $this->periodoParaDatas($config['periodo'] ?? 'mes_atual');

        $categoriaIds = $config['categorias'] ?? [];
        $tipo         = $config['tipo'] ?? 'ambos';

        // Build base queries scoped by empresa and period
        $receitas  = 0.0;
        $despesas  = 0.0;
        $porCategoria = [];

        if (in_array($tipo, ['receitas', 'ambos'])) {
            $qReceber = ContaReceber::where('empresa_id', $empresaId)
                ->whereBetween('data_vencimento', [$dataInicio, $dataFim]);

            if (!empty($categoriaIds)) {
                $qReceber->whereIn('categoria_id', $categoriaIds);
            }

            $receitaRows = $qReceber->with('categoria')
                ->selectRaw('categoria_id, SUM(valor_total) as total')
                ->groupBy('categoria_id')
                ->get();

            foreach ($receitaRows as $row) {
                $receitas += (float) $row->total;
                $label     = $row->categoria?->nome ?? 'Sem categoria';
                $porCategoria[$label] = ($porCategoria[$label] ?? ['receitas' => 0, 'despesas' => 0]);
                $porCategoria[$label]['receitas'] += (float) $row->total;
            }
        }

        if (in_array($tipo, ['despesas', 'ambos'])) {
            $qPagar = ContaPagar::where('empresa_id', $empresaId)
                ->whereBetween('data_vencimento', [$dataInicio, $dataFim]);

            if (!empty($categoriaIds)) {
                $qPagar->whereIn('categoria_id', $categoriaIds);
            }

            $despesaRows = $qPagar->with('categoria')
                ->selectRaw('categoria_id, SUM(valor_total) as total')
                ->groupBy('categoria_id')
                ->get();

            foreach ($despesaRows as $row) {
                $despesas += (float) $row->total;
                $label     = $row->categoria?->nome ?? 'Sem categoria';
                $porCategoria[$label] = $porCategoria[$label] ?? ['receitas' => 0, 'despesas' => 0];
                $porCategoria[$label]['despesas'] += (float) $row->total;
            }
        }

        $resultado = $receitas - $despesas;

        ksort($porCategoria);

        return view('perfis-consolidacao.show', compact(
            'perfisConsolidacao',
            'config',
            'dataInicio',
            'dataFim',
            'receitas',
            'despesas',
            'resultado',
            'porCategoria',
        ));
    }

    public function edit(PerfilConsolidacao $perfisConsolidacao): View
    {
        $this->authorizeOwnerOrAdmin($perfisConsolidacao);

        $empresaId  = auth()->user()->empresa_id;
        $categorias = CategoriaFinanceira::where('empresa_id', $empresaId)
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();

        return view('perfis-consolidacao.edit', compact('perfisConsolidacao', 'categorias'));
    }

    public function update(Request $request, PerfilConsolidacao $perfisConsolidacao): RedirectResponse
    {
        $this->authorizeOwnerOrAdmin($perfisConsolidacao);

        $data = $request->validate([
            'nome'         => ['required', 'string', 'max:100'],
            'descricao'    => ['nullable', 'string'],
            'periodo'      => ['required', 'in:mes_atual,trimestre,ano'],
            'tipo'         => ['required', 'in:receitas,despesas,ambos'],
            'categorias'   => ['nullable', 'array'],
            'categorias.*' => ['integer', 'exists:categorias_financeiras,id'],
            'mostrar_grafico' => ['boolean'],
            'publico'      => ['boolean'],
        ]);

        $perfisConsolidacao->update([
            'nome'      => $data['nome'],
            'descricao' => $data['descricao'] ?? null,
            'publico'   => $request->boolean('publico'),
            'configuracao' => [
                'periodo'         => $data['periodo'],
                'categorias'      => $data['categorias'] ?? [],
                'tipo'            => $data['tipo'],
                'mostrar_grafico' => $request->boolean('mostrar_grafico'),
            ],
        ]);

        return redirect()->route('perfis-consolidacao.index')
            ->with('success', 'Perfil atualizado com sucesso.');
    }

    public function destroy(PerfilConsolidacao $perfisConsolidacao): RedirectResponse
    {
        $this->authorizeOwnerOrAdmin($perfisConsolidacao);
        $perfisConsolidacao->delete();

        return redirect()->route('perfis-consolidacao.index')
            ->with('success', 'Perfil removido.');
    }

    // -------------------------------------------------------------------------

    private function authorizeView(PerfilConsolidacao $perfil): void
    {
        $empresaId = auth()->user()->empresa_id;
        abort_if($perfil->empresa_id !== $empresaId, 403);
        abort_if(!$perfil->publico && $perfil->user_id !== auth()->id(), 403);
    }

    private function authorizeOwnerOrAdmin(PerfilConsolidacao $perfil): void
    {
        $empresaId = auth()->user()->empresa_id;
        abort_if($perfil->empresa_id !== $empresaId, 403);

        $isOwner = $perfil->user_id === auth()->id();
        $isAdmin = auth()->user()->papel === 'admin' || auth()->user()->is_admin ?? false;
        abort_if(!$isOwner && !$isAdmin, 403);
    }

    private function periodoParaDatas(string $periodo): array
    {
        return match ($periodo) {
            'trimestre' => [
                now()->startOfQuarter()->toDateString(),
                now()->endOfQuarter()->toDateString(),
            ],
            'ano' => [
                now()->startOfYear()->toDateString(),
                now()->endOfYear()->toDateString(),
            ],
            default => [ // mes_atual
                now()->startOfMonth()->toDateString(),
                now()->endOfMonth()->toDateString(),
            ],
        };
    }
}
