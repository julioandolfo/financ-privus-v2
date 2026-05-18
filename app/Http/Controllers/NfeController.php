<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\ContaReceber;
use App\Models\Nfe;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class NfeController extends Controller
{
    // -------------------------------------------------------------------------
    // Index
    // -------------------------------------------------------------------------

    public function index(Request $request): View
    {
        $empresaId = auth()->user()->empresa_id;

        $query = Nfe::where('empresa_id', $empresaId)
            ->with('cliente')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->data_inicio, fn($q) => $q->whereDate('data_emissao', '>=', $request->data_inicio))
            ->when($request->data_fim, fn($q) => $q->whereDate('data_emissao', '<=', $request->data_fim))
            ->when($request->busca, fn($q) => $q->where(function ($sub) use ($request) {
                $sub->where('numero', 'like', "%{$request->busca}%")
                    ->orWhere('chave_acesso', 'like', "%{$request->busca}%")
                    ->orWhereHas('cliente', fn($cq) =>
                        $cq->where('nome_razao_social', 'like', "%{$request->busca}%")
                    );
            }));

        $stats = [
            'total'      => (clone $query)->count(),
            'autorizadas'=> (clone $query)->where('status', 'autorizada')->count(),
            'canceladas' => (clone $query)->where('status', 'cancelada')->count(),
            'rascunhos'  => (clone $query)->where('status', 'rascunho')->count(),
        ];

        $nfes = $query->orderByDesc('created_at')->paginate(25)->withQueryString();

        return view('nfes.index', compact('nfes', 'stats'));
    }

    // -------------------------------------------------------------------------
    // Create / Store
    // -------------------------------------------------------------------------

    public function create(): View
    {
        $empresaId     = auth()->user()->empresa_id;
        $clientes      = Cliente::where('empresa_id', $empresaId)->where('ativo', true)->orderBy('nome_razao_social')->get();
        $contasReceber = ContaReceber::where('empresa_id', $empresaId)
            ->whereIn('status', ['pendente', 'parcial', 'vencido'])
            ->with('cliente')
            ->orderByDesc('data_vencimento')
            ->get();

        return view('nfes.create', compact('clientes', 'contasReceber'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateNfe($request);
        $data['empresa_id'] = auth()->user()->empresa_id;
        $data['status']     = 'rascunho';

        $nfe = Nfe::create($data);

        return redirect()->route('nfes.show', $nfe)
            ->with('success', 'NF-e criada como rascunho com sucesso.');
    }

    // -------------------------------------------------------------------------
    // Show
    // -------------------------------------------------------------------------

    public function show(Nfe $nfe): View
    {
        $this->authorizeEmpresa($nfe);
        $nfe->load('cliente', 'contaReceber');

        $temToken = $this->hasWebmaniaBrToken($nfe->empresa_id);

        return view('nfes.show', compact('nfe', 'temToken'));
    }

    // -------------------------------------------------------------------------
    // Edit / Update
    // -------------------------------------------------------------------------

    public function edit(Nfe $nfe): View
    {
        $this->authorizeEmpresa($nfe);
        abort_if(! $nfe->podeEmitir(), 403, 'Apenas rascunhos podem ser editados.');

        $empresaId     = $nfe->empresa_id;
        $clientes      = Cliente::where('empresa_id', $empresaId)->where('ativo', true)->orderBy('nome_razao_social')->get();
        $contasReceber = ContaReceber::where('empresa_id', $empresaId)
            ->whereIn('status', ['pendente', 'parcial', 'vencido'])
            ->with('cliente')
            ->orderByDesc('data_vencimento')
            ->get();

        return view('nfes.edit', compact('nfe', 'clientes', 'contasReceber'));
    }

    public function update(Request $request, Nfe $nfe): RedirectResponse
    {
        $this->authorizeEmpresa($nfe);
        abort_if(! $nfe->podeEmitir(), 403, 'Apenas rascunhos podem ser editados.');

        $data = $this->validateNfe($request);
        $nfe->update($data);

        return redirect()->route('nfes.show', $nfe)
            ->with('success', 'NF-e atualizada com sucesso.');
    }

    // -------------------------------------------------------------------------
    // Emitir
    // -------------------------------------------------------------------------

    public function emitir(int $id): RedirectResponse
    {
        $empresaId = auth()->user()->empresa_id;
        $nfe       = Nfe::where('empresa_id', $empresaId)->findOrFail($id);

        abort_if(! $nfe->podeEmitir(), 422, 'Esta NF-e não pode ser emitida no status atual.');

        $token = DB::table('configuracoes')
            ->where('empresa_id', $empresaId)
            ->where('chave', 'webmaniabr_token')
            ->value('valor');

        if (! $token) {
            return back()->with('error', 'Configure o token do WebmaniaBR em Configurações > Integrações antes de emitir NF-e.');
        }

        $nfe->update(['status' => 'processando']);

        // TODO: implement actual WebmaniaBR API call
        // Example endpoint: POST https://webmaniabr.com/api/1/nfe/emissao/
        // Headers: Authorization: Bearer {token}
        // Catch all exceptions and revert status on failure

        return back()->with('success', 'NF-e enviada para processamento. Aguarde alguns instantes.');
    }

    // -------------------------------------------------------------------------
    // Cancelar
    // -------------------------------------------------------------------------

    public function cancelar(Request $request, int $id): RedirectResponse
    {
        $empresaId = auth()->user()->empresa_id;
        $nfe       = Nfe::where('empresa_id', $empresaId)->findOrFail($id);

        abort_if(! $nfe->podeCancelar(), 422, 'Esta NF-e não pode ser cancelada no status atual.');

        $request->validate([
            'motivo_cancelamento' => 'required|string|min:15|max:255',
        ]);

        $nfe->update([
            'status'               => 'cancelada',
            'motivo_cancelamento'  => $request->motivo_cancelamento,
        ]);

        return redirect()->route('nfes.show', $nfe)
            ->with('success', 'NF-e cancelada com sucesso.');
    }

    // -------------------------------------------------------------------------
    // DANFE
    // -------------------------------------------------------------------------

    public function danfe(int $id): RedirectResponse
    {
        $empresaId = auth()->user()->empresa_id;
        $nfe       = Nfe::where('empresa_id', $empresaId)->findOrFail($id);

        $url = $nfe->pdf_danfe_url ?? $nfe->link_danfe;

        abort_if(! $url, 404, 'DANFE não disponível para esta NF-e.');

        return redirect()->away($url);
    }

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function authorizeEmpresa(Nfe $nfe): void
    {
        abort_if($nfe->empresa_id !== auth()->user()->empresa_id, 403);
    }

    private function hasWebmaniaBrToken(int $empresaId): bool
    {
        return DB::table('configuracoes')
            ->where('empresa_id', $empresaId)
            ->where('chave', 'webmaniabr_token')
            ->whereNotNull('valor')
            ->where('valor', '!=', '')
            ->exists();
    }

    private function validateNfe(Request $request): array
    {
        return $request->validate([
            'cliente_id'         => 'nullable|exists:clientes,id',
            'conta_receber_id'   => 'nullable|exists:contas_receber,id',
            'pedido_id'          => 'nullable|exists:pedidos_vinculados,id',
            'natureza_operacao'  => 'required|string|max:255',
            'serie'              => 'required|string|max:5',
            'data_emissao'       => 'nullable|date',
            'data_competencia'   => 'nullable|date',
            'valor_produtos'     => 'required|numeric|min:0',
            'valor_frete'        => 'nullable|numeric|min:0',
            'valor_desconto'     => 'nullable|numeric|min:0',
            'valor_total'        => 'required|numeric|min:0',
            'observacoes'        => 'nullable|string',
        ]);
    }
}
