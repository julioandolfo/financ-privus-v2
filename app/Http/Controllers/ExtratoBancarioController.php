<?php

namespace App\Http\Controllers;

use App\Models\CategoriaFinanceira;
use App\Models\ContaBancaria;
use App\Models\ExtratoBancario;
use App\Models\ExtratoLancamento;
use App\Models\MovimentacaoCaixa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExtratoBancarioController extends Controller
{
    public function index()
    {
        $empresaId = auth()->user()->empresa_id;

        $extratos = ExtratoBancario::where('empresa_id', $empresaId)
            ->with('contaBancaria')
            ->withCount([
                'lancamentos',
                'lancamentos as conciliados_count' => fn($q) => $q->where('conciliado', true),
                'lancamentos as pendentes_count'   => fn($q) => $q->where('conciliado', false)->where('ignorado', false),
            ])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('extratos.index', compact('extratos'));
    }

    public function create()
    {
        $empresaId = auth()->user()->empresa_id;
        $contas = ContaBancaria::where('empresa_id', $empresaId)->where('ativo', true)->orderBy('nome')->get();
        return view('extratos.create', compact('contas'));
    }

    public function store(Request $request)
    {
        $empresaId = auth()->user()->empresa_id;

        $request->validate([
            'conta_bancaria_id' => ['required', 'exists:contas_bancarias,id'],
            'arquivo'           => ['required', 'file', 'max:10240'],
        ]);

        $conta = ContaBancaria::where('id', $request->conta_bancaria_id)
            ->where('empresa_id', $empresaId)
            ->firstOrFail();

        $arquivo  = $request->file('arquivo');
        $ext      = strtolower($arquivo->getClientOriginalExtension());
        $tipo     = $ext === 'ofx' ? 'ofx' : 'csv';
        $conteudo = file_get_contents($arquivo->getRealPath());

        $lancamentos = $tipo === 'ofx'
            ? $this->parseOfx($conteudo)
            : $this->parseCsv($conteudo);

        if (empty($lancamentos)) {
            return back()->withErrors(['arquivo' => 'Nenhum lançamento encontrado. Verifique o formato do arquivo.']);
        }

        $datas = collect($lancamentos)->pluck('data_lancamento')->sort()->values();

        DB::transaction(function () use ($empresaId, $conta, $arquivo, $tipo, $lancamentos, $datas) {
            $extrato = ExtratoBancario::create([
                'empresa_id'       => $empresaId,
                'conta_bancaria_id'=> $conta->id,
                'nome_arquivo'     => $arquivo->getClientOriginalName(),
                'tipo'             => $tipo,
                'data_inicio'      => $datas->first(),
                'data_fim'         => $datas->last(),
            ]);

            $jaVinculados = ExtratoLancamento::whereNotNull('movimentacao_id')->pluck('movimentacao_id')->toArray();

            foreach ($lancamentos as $l) {
                $match = MovimentacaoCaixa::where('empresa_id', $empresaId)
                    ->where('conta_bancaria_id', $conta->id)
                    ->where('tipo', $l['tipo'] === 'credito' ? 'entrada' : 'saida')
                    ->where('valor', $l['valor'])
                    ->whereBetween('data_movimentacao', [
                        Carbon::parse($l['data_lancamento'])->subDays(3),
                        Carbon::parse($l['data_lancamento'])->addDays(3),
                    ])
                    ->whereNotIn('id', $jaVinculados)
                    ->first();

                if ($match) {
                    $jaVinculados[] = $match->id;
                    $match->update(['conciliado' => true]);
                }

                ExtratoLancamento::create([
                    'extrato_id'     => $extrato->id,
                    'fitid'          => $l['fitid'] ?? null,
                    'data_lancamento'=> $l['data_lancamento'],
                    'valor'          => $l['valor'],
                    'tipo'           => $l['tipo'],
                    'descricao'      => $l['descricao'],
                    'movimentacao_id'=> $match?->id,
                    'conciliado'     => (bool) $match,
                ]);
            }
        });

        return redirect()->route('extratos.index')->with('success', 'Extrato importado. Verifique os lançamentos auto-conciliados e finalize os pendentes.');
    }

    public function show(ExtratoBancario $extrato)
    {
        $this->authorizeExtrato($extrato);
        $empresaId = auth()->user()->empresa_id;

        $filtro = request('filtro', 'pendentes');

        $query = $extrato->lancamentos()->orderBy('data_lancamento')->orderBy('id');
        match($filtro) {
            'conciliados' => $query->where('conciliado', true),
            'ignorados'   => $query->where('ignorado', true),
            'pendentes'   => $query->where('conciliado', false)->where('ignorado', false),
            default       => null,
        };

        $lancamentos = $query->with('movimentacao.categoria')->paginate(50)->withQueryString();

        $jaVinculados = ExtratoLancamento::where('extrato_id', $extrato->id)
            ->whereNotNull('movimentacao_id')
            ->pluck('movimentacao_id');

        $candidatos = [];
        foreach ($lancamentos->items() as $lanc) {
            if (!$lanc->isPendente()) continue;

            $candidatos[$lanc->id] = MovimentacaoCaixa::where('empresa_id', $empresaId)
                ->where('conta_bancaria_id', $extrato->conta_bancaria_id)
                ->where('tipo', $lanc->tipo === 'credito' ? 'entrada' : 'saida')
                ->whereBetween('data_movimentacao', [
                    $lanc->data_lancamento->copy()->subDays(10),
                    $lanc->data_lancamento->copy()->addDays(10),
                ])
                ->whereNotIn('id', $jaVinculados)
                ->orderByRaw('ABS(DATEDIFF(data_movimentacao, ?)) ASC', [$lanc->data_lancamento])
                ->limit(6)
                ->get();
        }

        $resumo = [
            'total'       => $extrato->lancamentos()->count(),
            'conciliados' => $extrato->lancamentos()->where('conciliado', true)->count(),
            'pendentes'   => $extrato->lancamentos()->where('conciliado', false)->where('ignorado', false)->count(),
            'ignorados'   => $extrato->lancamentos()->where('ignorado', true)->count(),
        ];

        $categorias = CategoriaFinanceira::where('empresa_id', $empresaId)->orderBy('nome')->get();

        return view('extratos.show', compact(
            'extrato', 'lancamentos', 'candidatos', 'resumo', 'filtro', 'categorias'
        ));
    }

    public function conciliarLancamento(Request $request, ExtratoBancario $extrato, ExtratoLancamento $lancamento)
    {
        $this->authorizeExtrato($extrato);
        $this->authorizeLancamento($extrato, $lancamento);

        $request->validate(['movimentacao_id' => ['required', 'exists:movimentacoes_caixa,id']]);

        $movimentacao = MovimentacaoCaixa::findOrFail($request->movimentacao_id);

        DB::transaction(function () use ($lancamento, $movimentacao) {
            if ($lancamento->movimentacao_id && $lancamento->movimentacao_id !== $movimentacao->id) {
                MovimentacaoCaixa::find($lancamento->movimentacao_id)?->update(['conciliado' => false]);
            }
            $lancamento->update(['movimentacao_id' => $movimentacao->id, 'conciliado' => true, 'ignorado' => false]);
            $movimentacao->update(['conciliado' => true]);
        });

        return back()->with('success', 'Lançamento conciliado com sucesso.');
    }

    public function ignorarLancamento(ExtratoBancario $extrato, ExtratoLancamento $lancamento)
    {
        $this->authorizeExtrato($extrato);
        $this->authorizeLancamento($extrato, $lancamento);

        $lancamento->update(['ignorado' => true, 'conciliado' => false, 'movimentacao_id' => null]);

        return back()->with('success', 'Lançamento marcado como ignorado.');
    }

    public function desconciliarLancamento(ExtratoBancario $extrato, ExtratoLancamento $lancamento)
    {
        $this->authorizeExtrato($extrato);
        $this->authorizeLancamento($extrato, $lancamento);

        DB::transaction(function () use ($lancamento) {
            if ($lancamento->movimentacao) {
                $lancamento->movimentacao->update(['conciliado' => false]);
            }
            $lancamento->update(['conciliado' => false, 'ignorado' => false, 'movimentacao_id' => null]);
        });

        return back()->with('success', 'Conciliação desfeita.');
    }

    public function criarMovimentacao(Request $request, ExtratoBancario $extrato, ExtratoLancamento $lancamento)
    {
        $this->authorizeExtrato($extrato);
        $this->authorizeLancamento($extrato, $lancamento);
        $empresaId = auth()->user()->empresa_id;

        $data = $request->validate([
            'descricao'          => ['required', 'string', 'max:255'],
            'categoria_id'       => ['nullable', 'exists:categorias_financeiras,id'],
            'data_movimentacao'  => ['required', 'date'],
        ]);

        DB::transaction(function () use ($empresaId, $extrato, $lancamento, $data) {
            $tipo = $lancamento->tipo === 'credito' ? 'entrada' : 'saida';

            $movimentacao = MovimentacaoCaixa::create([
                'empresa_id'        => $empresaId,
                'conta_bancaria_id' => $extrato->conta_bancaria_id,
                'categoria_id'      => $data['categoria_id'] ?? null,
                'user_id'           => auth()->id(),
                'tipo'              => $tipo,
                'descricao'         => $data['descricao'],
                'valor'             => $lancamento->valor,
                'data_movimentacao' => $data['data_movimentacao'],
                'conciliado'        => true,
            ]);

            $delta = $tipo === 'entrada' ? $lancamento->valor : -$lancamento->valor;
            $extrato->contaBancaria->increment('saldo_atual', $delta);

            $lancamento->update([
                'movimentacao_id' => $movimentacao->id,
                'conciliado'      => true,
                'ignorado'        => false,
            ]);
        });

        return back()->with('success', 'Movimentação criada e lançamento conciliado.');
    }

    public function destroy(ExtratoBancario $extrato)
    {
        $this->authorizeExtrato($extrato);

        DB::transaction(function () use ($extrato) {
            $movIds = $extrato->lancamentos()->whereNotNull('movimentacao_id')->pluck('movimentacao_id');
            MovimentacaoCaixa::whereIn('id', $movIds)->update(['conciliado' => false]);
            $extrato->delete();
        });

        return redirect()->route('extratos.index')->with('success', 'Extrato removido.');
    }

    // -------------------------------------------------------------------------
    // OFX PARSER
    // -------------------------------------------------------------------------
    private function parseOfx(string $content): array
    {
        $content = str_replace(["\r\n", "\r"], "\n", $content);

        // Try XML-style (closing tags) then SGML-style (no closing tags)
        preg_match_all('/<STMTTRN>(.*?)<\/STMTTRN>/si', $content, $matches);
        if (empty($matches[1])) {
            preg_match_all('/<STMTTRN>(.*?)(?=<STMTTRN>|<\/BANKTRANLIST>|$)/si', $content, $matches);
        }

        $lancamentos = [];
        foreach ($matches[1] as $block) {
            $valorStr = $this->ofxTag($block, 'TRNAMT') ?? '0';
            $valor    = (float) str_replace(',', '.', $valorStr);
            $dataStr  = $this->ofxTag($block, 'DTPOSTED');

            if (!$dataStr) continue;

            try {
                $data = Carbon::createFromFormat('Ymd', substr(preg_replace('/\D/', '', $dataStr), 0, 8))->toDateString();
            } catch (\Exception) {
                continue;
            }

            $lancamentos[] = [
                'fitid'           => $this->ofxTag($block, 'FITID'),
                'data_lancamento' => $data,
                'valor'           => abs($valor),
                'tipo'            => $valor >= 0 ? 'credito' : 'debito',
                'descricao'       => $this->ofxTag($block, 'MEMO') ?: $this->ofxTag($block, 'NAME'),
            ];
        }

        return $lancamentos;
    }

    private function ofxTag(string $block, string $tag): ?string
    {
        if (preg_match("/<{$tag}>\s*(.*?)(?:<\/{$tag}>|\s*<|\s*$)/si", $block, $m)) {
            return trim($m[1]);
        }
        return null;
    }

    // -------------------------------------------------------------------------
    // CSV PARSER
    // -------------------------------------------------------------------------
    private function parseCsv(string $content): array
    {
        $sep   = substr_count($content, ';') > substr_count($content, ',') ? ';' : ',';
        $lines = array_filter(explode("\n", str_replace("\r", '', $content)), 'strlen');

        $lancamentos = [];
        $primeiraLinha = true;

        foreach ($lines as $line) {
            $cols = str_getcsv(trim($line), $sep);
            if (count($cols) < 3) continue;

            // Skip header
            if ($primeiraLinha) {
                $primeiraLinha = false;
                if (!preg_match('/^\d/', trim($cols[0]))) continue;
            }

            [$dataStr, $descricao] = [trim($cols[0]), trim($cols[1])];

            try {
                $data = $this->parseDate($dataStr);
            } catch (\Exception) {
                continue;
            }

            // 4-column: date, desc, debit, credit
            if (count($cols) >= 4) {
                $debito  = (float) str_replace(['.', ','], ['', '.'], trim($cols[2]));
                $credito = (float) str_replace(['.', ','], ['', '.'], trim($cols[3]));
                if ($debito > 0) {
                    $lancamentos[] = ['fitid' => null, 'data_lancamento' => $data, 'valor' => $debito,  'tipo' => 'debito',  'descricao' => $descricao];
                } elseif ($credito > 0) {
                    $lancamentos[] = ['fitid' => null, 'data_lancamento' => $data, 'valor' => $credito, 'tipo' => 'credito', 'descricao' => $descricao];
                }
                continue;
            }

            // 3-column: date, desc, value (positive=credito, negative=debito)
            $valor = (float) str_replace(['.', ','], ['', '.'], trim($cols[2]));
            if ($valor == 0) continue;

            $lancamentos[] = [
                'fitid'           => null,
                'data_lancamento' => $data,
                'valor'           => abs($valor),
                'tipo'            => $valor >= 0 ? 'credito' : 'debito',
                'descricao'       => $descricao,
            ];
        }

        return $lancamentos;
    }

    private function parseDate(string $str): string
    {
        foreach (['d/m/Y', 'd/m/y', 'd-m-Y', 'Y-m-d', 'm/d/Y'] as $fmt) {
            try { return Carbon::createFromFormat($fmt, $str)->toDateString(); } catch (\Exception) {}
        }
        return Carbon::parse($str)->toDateString();
    }

    // -------------------------------------------------------------------------
    private function authorizeExtrato(ExtratoBancario $extrato): void
    {
        abort_if($extrato->empresa_id !== auth()->user()->empresa_id, 403);
    }

    private function authorizeLancamento(ExtratoBancario $extrato, ExtratoLancamento $lancamento): void
    {
        abort_if($lancamento->extrato_id !== $extrato->id, 403);
    }
}
