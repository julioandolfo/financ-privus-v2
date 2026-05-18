<?php

namespace App\Http\Controllers;

use App\Models\EvolutionConfig;
use App\Models\WhatsAppDestinatario;
use App\Models\WhatsAppRegra;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class WhatsAppController extends Controller
{
    // -------------------------------------------------------------------------
    // Dashboard
    // -------------------------------------------------------------------------

    public function index(): View
    {
        $empresaId = auth()->user()->empresa_id;

        $conexoes = EvolutionConfig::paraEmpresa($empresaId)
            ->orderBy('nome')
            ->get();

        $regras = WhatsAppRegra::where('empresa_id', $empresaId)
            ->with(['evolutionConfig', 'destinatarios'])
            ->orderBy('nome')
            ->get();

        $conexoesAtivas = $conexoes->where('ativo', true)->count();
        $regrasAtivas   = $regras->where('ativo', true)->count();

        return view('whatsapp.index', compact('conexoes', 'regras', 'conexoesAtivas', 'regrasAtivas'));
    }

    // -------------------------------------------------------------------------
    // Conexões (EvolutionConfig)
    // -------------------------------------------------------------------------

    public function conexoesIndex(): View
    {
        $empresaId = auth()->user()->empresa_id;

        $conexoes = EvolutionConfig::paraEmpresa($empresaId)
            ->withCount('regras')
            ->orderBy('nome')
            ->paginate(20)
            ->withQueryString();

        return view('whatsapp.conexoes.index', compact('conexoes'));
    }

    public function conexaoCreate(): View
    {
        $config = new EvolutionConfig();
        return view('whatsapp.conexoes.form', compact('config'));
    }

    public function conexaoStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nome'             => 'required|string|max:100',
            'provider'         => 'required|string|max:50',
            'base_url'         => 'required|url|max:255',
            'instance_name'    => 'nullable|string|max:100',
            'api_key'          => 'required|string|max:255',
            'numero_remetente' => 'nullable|string|max:30',
            'ativo'            => 'nullable|boolean',
        ]);

        $data['empresa_id'] = auth()->user()->empresa_id;
        $data['ativo']      = $request->boolean('ativo', true);

        EvolutionConfig::create($data);

        return redirect()->route('whatsapp.conexoes.index')
            ->with('success', 'Conexão criada com sucesso.');
    }

    public function conexaoEdit(int $id): View
    {
        $config = $this->findConfig($id);
        return view('whatsapp.conexoes.form', compact('config'));
    }

    public function conexaoUpdate(Request $request, int $id): RedirectResponse
    {
        $config = $this->findConfig($id);

        $data = $request->validate([
            'nome'             => 'required|string|max:100',
            'provider'         => 'required|string|max:50',
            'base_url'         => 'required|url|max:255',
            'instance_name'    => 'nullable|string|max:100',
            'api_key'          => 'nullable|string|max:255',
            'numero_remetente' => 'nullable|string|max:30',
            'ativo'            => 'nullable|boolean',
        ]);

        // Only update api_key if a new value was supplied
        if (empty($data['api_key'])) {
            unset($data['api_key']);
        }

        $data['ativo'] = $request->boolean('ativo', false);

        $config->update($data);

        return redirect()->route('whatsapp.conexoes.index')
            ->with('success', 'Conexão atualizada com sucesso.');
    }

    public function conexaoDestroy(int $id): RedirectResponse
    {
        $config = $this->findConfig($id);
        $config->delete();

        return redirect()->route('whatsapp.conexoes.index')
            ->with('success', 'Conexão removida.');
    }

    public function testar(int $id): JsonResponse
    {
        $config = $this->findConfig($id);

        try {
            $response = Http::withHeaders(['apikey' => $config->api_key])
                ->timeout(10)
                ->get($config->base_url . '/instance/fetchInstances');

            return response()->json([
                'ok'   => $response->successful(),
                'data' => $response->json(),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'ok'    => false,
                'error' => $e->getMessage(),
            ], 200);
        }
    }

    // -------------------------------------------------------------------------
    // Regras
    // -------------------------------------------------------------------------

    public function regrasIndex(): View
    {
        $empresaId = auth()->user()->empresa_id;

        $regras = WhatsAppRegra::where('empresa_id', $empresaId)
            ->with(['evolutionConfig', 'destinatarios'])
            ->withCount('destinatarios')
            ->orderBy('nome')
            ->paginate(20)
            ->withQueryString();

        return view('whatsapp.regras.index', compact('regras'));
    }

    public function regraCreate(): View
    {
        $empresaId = auth()->user()->empresa_id;
        $conexoes  = EvolutionConfig::paraEmpresa($empresaId)->ativos()->orderBy('nome')->get();
        $regra     = new WhatsAppRegra();

        return view('whatsapp.regras.form', compact('regra', 'conexoes'));
    }

    public function regraStore(Request $request): RedirectResponse
    {
        $data = $this->validateRegra($request);
        $data['empresa_id'] = auth()->user()->empresa_id;

        $destinatarios = $this->parseDestinatarios($request);

        DB::transaction(function () use ($data, $destinatarios) {
            $regra = WhatsAppRegra::create($data);

            foreach ($destinatarios as $dest) {
                $regra->destinatarios()->create($dest);
            }
        });

        return redirect()->route('whatsapp.regras.index')
            ->with('success', 'Regra criada com sucesso.');
    }

    public function regraEdit(int $id): View
    {
        $empresaId = auth()->user()->empresa_id;
        $regra     = $this->findRegra($id);
        $conexoes  = EvolutionConfig::paraEmpresa($empresaId)->ativos()->orderBy('nome')->get();

        $regra->load('destinatarios');

        return view('whatsapp.regras.form', compact('regra', 'conexoes'));
    }

    public function regraUpdate(Request $request, int $id): RedirectResponse
    {
        $regra = $this->findRegra($id);
        $data  = $this->validateRegra($request);

        $destinatarios = $this->parseDestinatarios($request);

        DB::transaction(function () use ($regra, $data, $destinatarios) {
            $regra->update($data);
            $regra->destinatarios()->delete();

            foreach ($destinatarios as $dest) {
                $regra->destinatarios()->create($dest);
            }
        });

        return redirect()->route('whatsapp.regras.index')
            ->with('success', 'Regra atualizada com sucesso.');
    }

    public function regraDestroy(int $id): RedirectResponse
    {
        $regra = $this->findRegra($id);
        $regra->delete();

        return redirect()->route('whatsapp.regras.index')
            ->with('success', 'Regra removida.');
    }

    public function regraToggle(int $id): RedirectResponse
    {
        $regra = $this->findRegra($id);
        $regra->update(['ativo' => ! $regra->ativo]);

        return back()->with('success', $regra->ativo ? 'Regra ativada.' : 'Regra desativada.');
    }

    // -------------------------------------------------------------------------
    // Helpers privados
    // -------------------------------------------------------------------------

    private function findConfig(int $id): EvolutionConfig
    {
        $empresaId = auth()->user()->empresa_id;

        $config = EvolutionConfig::where(function ($q) use ($empresaId) {
            $q->where('empresa_id', $empresaId)->orWhereNull('empresa_id');
        })->findOrFail($id);

        return $config;
    }

    private function findRegra(int $id): WhatsAppRegra
    {
        $empresaId = auth()->user()->empresa_id;

        return WhatsAppRegra::where('empresa_id', $empresaId)->findOrFail($id);
    }

    private function validateRegra(Request $request): array
    {
        return $request->validate([
            'nome'               => 'required|string|max:100',
            'evolution_config_id'=> 'nullable|exists:evolution_configs,id',
            'tipo'               => 'required|in:vencimentos,fluxo_caixa,dre,recorrencias,cobranca',
            'periodicidade'      => 'required|in:diario,semanal,mensal',
            'hora_envio'         => 'required|date_format:H:i',
            'dia_semana'         => 'nullable|integer|min:0|max:6',
            'dia_mes'            => 'nullable|integer|min:1|max:31',
            'ativo'              => 'nullable|boolean',
        ]) + ['ativo' => $request->boolean('ativo', true)];
    }

    private function parseDestinatarios(Request $request): array
    {
        $request->validate([
            'destinatarios'           => 'nullable|array',
            'destinatarios.*.nome'    => 'required_with:destinatarios|string|max:100',
            'destinatarios.*.telefone'=> 'required_with:destinatarios|string|max:20',
        ]);

        return collect($request->input('destinatarios', []))
            ->filter(fn($d) => ! empty($d['telefone']))
            ->map(fn($d) => [
                'nome'     => $d['nome'] ?? '',
                'telefone' => $d['telefone'],
            ])
            ->values()
            ->all();
    }
}
