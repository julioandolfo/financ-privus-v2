<?php

namespace App\Http\Controllers;

use App\Models\IntegracaoConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class IntegracaoController extends Controller
{
    public function index()
    {
        $empresaId = auth()->user()->empresa_id;

        $integracoes = IntegracaoConfig::where('empresa_id', $empresaId)->get()->keyBy('tipo');

        $tipos = IntegracaoConfig::tiposDisponiveis();

        return view('integracoes.index', compact('integracoes', 'tipos'));
    }

    public function configurar(string $tipo)
    {
        $tipos = IntegracaoConfig::tiposDisponiveis();
        abort_unless(isset($tipos[$tipo]), 404);

        $empresaId = auth()->user()->empresa_id;
        $integracao = IntegracaoConfig::firstOrNew(
            ['empresa_id' => $empresaId, 'tipo' => $tipo],
            ['nome' => $tipos[$tipo]['label'], 'configuracoes' => []]
        );

        return view("integracoes.{$tipo}", compact('integracao', 'tipo'));
    }

    public function salvar(Request $request, string $tipo)
    {
        $tipos = IntegracaoConfig::tiposDisponiveis();
        abort_unless(isset($tipos[$tipo]), 404);

        $empresaId = auth()->user()->empresa_id;

        $configs = match ($tipo) {
            'woocommerce' => $request->validate([
                'configs.url'             => ['required', 'url'],
                'configs.consumer_key'    => ['required', 'string'],
                'configs.consumer_secret' => ['required', 'string'],
                'configs.sync_pedidos'    => ['boolean'],
                'configs.sync_clientes'   => ['boolean'],
                'configs.sync_produtos'   => ['boolean'],
                'configs.categoria_id'    => ['nullable', 'exists:categorias_financeiras,id'],
                'configs.forma_pgto_id'   => ['nullable', 'exists:formas_pagamento,id'],
            ])['configs'],

            'whatsapp' => $request->validate([
                'configs.url'      => ['required', 'url'],
                'configs.api_key'  => ['required', 'string'],
                'configs.instancia'=> ['required', 'string'],
                'configs.notif_vencimento'    => ['boolean'],
                'configs.notif_pagamento'     => ['boolean'],
                'configs.dias_antecedencia'   => ['nullable', 'integer', 'min:1', 'max:30'],
            ])['configs'],

            'boleto' => $request->validate([
                'configs.banco'       => ['required', 'in:bradesco,itau,sicoob,sicredi,inter,bb,santander'],
                'configs.ambiente'    => ['required', 'in:sandbox,producao'],
                'configs.client_id'   => ['nullable', 'string'],
                'configs.client_secret' => ['nullable', 'string'],
                'configs.cert_path'   => ['nullable', 'string'],
                'configs.agencia'     => ['nullable', 'string'],
                'configs.conta'       => ['nullable', 'string'],
                'configs.convenio'    => ['nullable', 'string'],
                'configs.carteira'    => ['nullable', 'string'],
                'configs.cedente'     => ['nullable', 'string'],
                'configs.cnpj_cedente'=> ['nullable', 'string'],
            ])['configs'],

            'nfe' => $request->validate([
                'configs.ambiente'     => ['required', 'in:homologacao,producao'],
                'configs.serie'        => ['nullable', 'integer'],
                'configs.ultimo_numero'=> ['nullable', 'integer'],
                'configs.token_api'    => ['nullable', 'string'],
                'configs.inscricao_estadual' => ['nullable', 'string'],
                'configs.inscricao_municipal' => ['nullable', 'string'],
                'configs.regime_tributario'  => ['nullable', 'in:1,2,3'],
                'configs.csc'          => ['nullable', 'string'],
                'configs.csc_id'       => ['nullable', 'string'],
            ])['configs'],

            default => abort(404),
        };

        IntegracaoConfig::updateOrCreate(
            ['empresa_id' => $empresaId, 'tipo' => $tipo],
            [
                'nome'         => $tipos[$tipo]['label'],
                'ativo'        => $request->boolean('ativo'),
                'configuracoes'=> $configs,
            ]
        );

        return redirect()->route('integracoes.index')
            ->with('success', "Integração {$tipos[$tipo]['label']} configurada.");
    }

    public function testar(string $tipo)
    {
        $empresaId = auth()->user()->empresa_id;
        $integracao = IntegracaoConfig::where('empresa_id', $empresaId)->where('tipo', $tipo)->firstOrFail();

        $resultado = match ($tipo) {
            'woocommerce' => $this->testarWooCommerce($integracao),
            'whatsapp'    => $this->testarWhatsApp($integracao),
            default       => ['ok' => false, 'mensagem' => 'Teste não disponível para este tipo.'],
        };

        if ($resultado['ok']) {
            $integracao->update(['status_sync' => 'ok', 'ultimo_erro' => null]);
        } else {
            $integracao->update(['status_sync' => 'erro', 'ultimo_erro' => $resultado['mensagem']]);
        }

        return back()->with($resultado['ok'] ? 'success' : 'error', $resultado['mensagem']);
    }

    private function testarWooCommerce(IntegracaoConfig $i): array
    {
        try {
            $url = rtrim($i->config('url'), '/') . '/wp-json/wc/v3/system_status';
            $resp = Http::timeout(10)
                ->withBasicAuth($i->config('consumer_key'), $i->config('consumer_secret'))
                ->get($url);

            if ($resp->successful()) {
                return ['ok' => true, 'mensagem' => 'Conexão WooCommerce OK.'];
            }

            return ['ok' => false, 'mensagem' => 'Falha na conexão: HTTP ' . $resp->status()];
        } catch (\Throwable $e) {
            return ['ok' => false, 'mensagem' => 'Erro: ' . $e->getMessage()];
        }
    }

    private function testarWhatsApp(IntegracaoConfig $i): array
    {
        try {
            $url  = rtrim($i->config('url'), '/') . '/instance/fetchInstances';
            $resp = Http::timeout(8)
                ->withHeader('apikey', $i->config('api_key'))
                ->get($url);

            if ($resp->successful()) {
                return ['ok' => true, 'mensagem' => 'Conexão Evolution API OK.'];
            }

            return ['ok' => false, 'mensagem' => 'Falha: HTTP ' . $resp->status()];
        } catch (\Throwable $e) {
            return ['ok' => false, 'mensagem' => 'Erro: ' . $e->getMessage()];
        }
    }
}
