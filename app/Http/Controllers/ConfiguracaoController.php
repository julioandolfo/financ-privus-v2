<?php

namespace App\Http\Controllers;

use App\Models\Configuracao;
use Illuminate\Http\Request;

class ConfiguracaoController extends Controller
{
    public function index()
    {
        $empresaId = auth()->user()->empresa_id;

        $configs = Configuracao::where('empresa_id', $empresaId)
            ->get()
            ->keyBy('chave')
            ->map(fn($c) => $c->valor);

        return view('configuracoes.index', compact('configs'));
    }

    public function update(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;
        $grupo = $request->input('grupo', 'geral');

        $rules = match ($grupo) {
            'email' => [
                'email.smtp_host'      => ['nullable', 'string'],
                'email.smtp_port'      => ['nullable', 'integer'],
                'email.smtp_seguranca' => ['nullable', 'in:tls,ssl,none'],
                'email.smtp_usuario'   => ['nullable', 'string'],
                'email.senha'          => ['nullable', 'string'],
                'email.remetente_nome' => ['nullable', 'string', 'max:255'],
                'email.remetente_email'=> ['nullable', 'email'],
            ],
            'ia' => [
                'ia.insights_dashboard_habilitado' => ['boolean'],
                'ia.sugestao_categorias'           => ['boolean'],
                'ia.deteccao_duplicatas'           => ['boolean'],
                'api.openai_key'                   => ['nullable', 'string'],
                'api.openai_model'                 => ['nullable', 'string'],
            ],
            default => [
                'empresa.logo_url'           => ['nullable', 'url'],
                'geral.moeda'                => ['nullable', 'string', 'max:10'],
                'geral.casas_decimais'       => ['nullable', 'integer', 'min:0', 'max:4'],
                'geral.formato_data'         => ['nullable', 'string'],
            ],
        };

        $data = $request->validate($rules);

        foreach ($data as $chave => $valor) {
            if ($valor === null) {
                Configuracao::where('empresa_id', $empresaId)->where('chave', $chave)->delete();
                continue;
            }
            $tipo = is_bool($valor) ? 'boolean' : (is_int($valor) ? 'integer' : 'string');
            Configuracao::set($chave, $valor, $tipo, $empresaId);
        }

        return back()->with('success', 'Configurações salvas com sucesso.');
    }
}
