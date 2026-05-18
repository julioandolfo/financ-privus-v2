<?php

namespace App\Http\Controllers;

use App\Models\CategoriaFinanceira;
use App\Models\PadraoImportacaoExtrato;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PadraoImportacaoController extends Controller
{
    public function index(): View
    {
        $empresaId = auth()->user()->empresa_id;

        $padroes = PadraoImportacaoExtrato::where('empresa_id', $empresaId)
            ->with('categoria')
            ->orderByDesc('prioridade')
            ->orderBy('descricao_contem')
            ->get();

        return view('padroes-importacao.index', compact('padroes'));
    }

    public function create(): View
    {
        $categorias = $this->getCategorias();
        return view('padroes-importacao.form', [
            'padrao'    => null,
            'categorias'=> $categorias,
            'action'    => route('padroes-importacao.store'),
            'method'    => 'POST',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $empresaId = auth()->user()->empresa_id;

        $data = $this->validateRequest($request, $empresaId);

        PadraoImportacaoExtrato::create(array_merge($data, [
            'empresa_id' => $empresaId,
            'ativo'      => $request->boolean('ativo', true),
        ]));

        return redirect()->route('padroes-importacao.index')
            ->with('success', 'Padrão de importação criado com sucesso.');
    }

    public function edit(PadraoImportacaoExtrato $padroesImportacao): View
    {
        $this->authorize($padroesImportacao);

        $categorias = $this->getCategorias();

        return view('padroes-importacao.form', [
            'padrao'     => $padroesImportacao,
            'categorias' => $categorias,
            'action'     => route('padroes-importacao.update', $padroesImportacao),
            'method'     => 'PUT',
        ]);
    }

    public function update(Request $request, PadraoImportacaoExtrato $padroesImportacao): RedirectResponse
    {
        $this->authorize($padroesImportacao);

        $empresaId = auth()->user()->empresa_id;
        $data      = $this->validateRequest($request, $empresaId);

        $padroesImportacao->update(array_merge($data, [
            'ativo' => $request->boolean('ativo'),
        ]));

        return redirect()->route('padroes-importacao.index')
            ->with('success', 'Padrão atualizado com sucesso.');
    }

    public function destroy(PadraoImportacaoExtrato $padroesImportacao): RedirectResponse
    {
        $this->authorize($padroesImportacao);
        $padroesImportacao->delete();

        return redirect()->route('padroes-importacao.index')
            ->with('success', 'Padrão removido.');
    }

    // -------------------------------------------------------------------------

    private function getCategorias()
    {
        return CategoriaFinanceira::where('empresa_id', auth()->user()->empresa_id)
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();
    }

    private function validateRequest(Request $request, int $empresaId): array
    {
        return $request->validate([
            'descricao_contem'    => ['required', 'string', 'max:255'],
            'tipo_correspondencia'=> ['required', 'in:contem,comeca_com,exato'],
            'tipo_transacao'      => ['required', 'in:debito,credito,ambos'],
            'categoria_id'        => ['nullable', 'exists:categorias_financeiras,id'],
            'descricao_padrao'    => ['nullable', 'string', 'max:255'],
            'prioridade'          => ['required', 'integer', 'min:0', 'max:127'],
        ]);
    }

    private function authorize(PadraoImportacaoExtrato $padrao): void
    {
        abort_if($padrao->empresa_id !== auth()->user()->empresa_id, 403);
    }
}
