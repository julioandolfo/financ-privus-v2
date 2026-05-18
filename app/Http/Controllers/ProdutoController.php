<?php

namespace App\Http\Controllers;

use App\Models\CategoriaProduto;
use App\Models\Produto;
use App\Models\ProdutoFoto;
use App\Models\ProdutoVariacao;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProdutoController extends Controller
{
    public function index(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        $produtos = Produto::where('empresa_id', $empresaId)
            ->when($request->filled('search'), fn($q) => $q->where(function ($q) use ($request) {
                $q->where('nome', 'like', '%' . $request->search . '%')
                  ->orWhere('codigo', 'like', '%' . $request->search . '%')
                  ->orWhere('sku', 'like', '%' . $request->search . '%');
            }))
            ->when($request->filled('tipo'), fn($q) => $q->where('tipo', $request->tipo))
            ->when($request->filled('categoria_id'), fn($q) => $q->where('categoria_id', $request->categoria_id))
            ->when($request->filled('estoque_baixo'), fn($q) => $q->whereColumn('estoque', '<=', 'estoque_minimo'))
            ->with('categoria')
            ->orderBy('nome')
            ->paginate(30)
            ->withQueryString();

        $categorias = CategoriaProduto::where('empresa_id', $empresaId)->orderBy('nome')->get();
        $totalEstoqueBaixo = Produto::where('empresa_id', $empresaId)
            ->whereColumn('estoque', '<=', 'estoque_minimo')
            ->count();

        return view('produtos.index', compact('produtos', 'categorias', 'totalEstoqueBaixo'));
    }

    public function create()
    {
        $empresaId = auth()->user()->empresa_id;
        $categorias = CategoriaProduto::where('empresa_id', $empresaId)->orderBy('nome')->get();
        return view('produtos.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        $data = $request->validate([
            'nome'            => ['required', 'string', 'max:255'],
            'codigo'          => ['nullable', 'string', 'max:50', 'unique:produtos,codigo,NULL,id,empresa_id,' . $empresaId],
            'sku'             => ['nullable', 'string', 'max:100'],
            'codigo_barras'   => ['nullable', 'string', 'max:50'],
            'tipo'            => ['required', 'in:produto,servico'],
            'descricao'       => ['nullable', 'string'],
            'custo_unitario'  => ['nullable', 'numeric', 'min:0'],
            'preco_venda'     => ['required', 'numeric', 'min:0'],
            'unidade_medida'  => ['nullable', 'string', 'max:20'],
            'estoque'         => ['nullable', 'numeric', 'min:0'],
            'estoque_minimo'  => ['nullable', 'numeric', 'min:0'],
            'categoria_id'    => ['nullable', 'exists:categorias_produto,id'],
            'ncm'             => ['nullable', 'string', 'max:10'],
            'cfop'            => ['nullable', 'string', 'max:10'],
        ]);

        Produto::create(array_merge($data, [
            'empresa_id' => $empresaId,
            'user_id'    => auth()->id(),
        ]));

        return redirect()->route('produtos.index')
            ->with('success', 'Produto criado com sucesso.');
    }

    public function edit(Produto $produto)
    {
        $this->authorize($produto);
        $empresaId = auth()->user()->empresa_id;
        $categorias = CategoriaProduto::where('empresa_id', $empresaId)->orderBy('nome')->get();
        return view('produtos.edit', compact('produto', 'categorias'));
    }

    public function update(Request $request, Produto $produto)
    {
        $this->authorize($produto);
        $empresaId = auth()->user()->empresa_id;

        $data = $request->validate([
            'nome'            => ['required', 'string', 'max:255'],
            'codigo'          => ['nullable', 'string', 'max:50', 'unique:produtos,codigo,' . $produto->id . ',id,empresa_id,' . $empresaId],
            'sku'             => ['nullable', 'string', 'max:100'],
            'codigo_barras'   => ['nullable', 'string', 'max:50'],
            'tipo'            => ['required', 'in:produto,servico'],
            'descricao'       => ['nullable', 'string'],
            'custo_unitario'  => ['nullable', 'numeric', 'min:0'],
            'preco_venda'     => ['required', 'numeric', 'min:0'],
            'unidade_medida'  => ['nullable', 'string', 'max:20'],
            'estoque'         => ['nullable', 'numeric', 'min:0'],
            'estoque_minimo'  => ['nullable', 'numeric', 'min:0'],
            'categoria_id'    => ['nullable', 'exists:categorias_produto,id'],
            'ncm'             => ['nullable', 'string', 'max:10'],
            'cfop'            => ['nullable', 'string', 'max:10'],
        ]);

        $produto->update($data);

        return redirect()->route('produtos.index')
            ->with('success', 'Produto atualizado.');
    }

    public function destroy(Produto $produto)
    {
        $this->authorize($produto);
        $produto->delete();
        return back()->with('success', 'Produto removido.');
    }

    // -------------------------------------------------------------------------
    // Variações
    // -------------------------------------------------------------------------

    public function variacoes(Produto $produto): JsonResponse
    {
        $this->authorize($produto);

        return response()->json(
            $produto->variacoes()->orderBy('atributo')->orderBy('valor')->get()
        );
    }

    public function storeVariacao(Request $request, Produto $produto): JsonResponse
    {
        $this->authorize($produto);

        $data = $request->validate([
            'atributo'        => ['required', 'string', 'max:50'],
            'valor'           => ['required', 'string', 'max:100'],
            'sku'             => ['nullable', 'string', 'max:100'],
            'preco_adicional' => ['nullable', 'numeric', 'min:0'],
            'custo'           => ['nullable', 'numeric', 'min:0'],
            'estoque'         => ['nullable', 'numeric', 'min:0'],
        ]);

        $variacao = $produto->variacoes()->create($data);

        return response()->json($variacao, 201);
    }

    public function destroyVariacao(Produto $produto, ProdutoVariacao $variacao): JsonResponse
    {
        $this->authorize($produto);
        abort_if($variacao->produto_id !== $produto->id, 404);

        $variacao->delete();

        return response()->json(['ok' => true]);
    }

    // -------------------------------------------------------------------------
    // Fotos
    // -------------------------------------------------------------------------

    public function uploadFoto(Request $request, Produto $produto): JsonResponse
    {
        $this->authorize($produto);

        $request->validate([
            'foto' => ['required', 'file', 'image', 'max:5120'], // 5 MB
        ]);

        $file          = $request->file('foto');
        $nomeOriginal  = $file->getClientOriginalName();
        $path          = $file->store('produtos/' . $produto->id, 'public');

        // If this is the first photo, mark it as principal
        $temFotos = $produto->fotos()->exists();

        $foto = $produto->fotos()->create([
            'path'          => $path,
            'nome_original' => $nomeOriginal,
            'principal'     => !$temFotos,
            'ordem'         => $produto->fotos()->max('ordem') + 1,
        ]);

        return response()->json([
            'id'       => $foto->id,
            'path'     => $foto->path,
            'url'      => asset('storage/' . $foto->path),
            'principal'=> $foto->principal,
        ], 201);
    }

    public function destroyFoto(Produto $produto, ProdutoFoto $foto): JsonResponse
    {
        $this->authorize($produto);
        abort_if($foto->produto_id !== $produto->id, 404);

        Storage::disk('public')->delete($foto->path);
        $foto->delete();

        // If deleted photo was principal, assign principal to the next one
        if ($foto->principal) {
            $next = $produto->fotos()->orderBy('ordem')->first();
            $next?->update(['principal' => true]);
        }

        return response()->json(['ok' => true]);
    }

    private function authorize(Produto $produto): void
    {
        abort_if($produto->empresa_id !== auth()->user()->empresa_id, 403);
    }
}
