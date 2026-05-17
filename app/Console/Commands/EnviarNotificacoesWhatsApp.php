<?php

namespace App\Console\Commands;

use App\Models\ContaReceber;
use App\Models\IntegracaoConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class EnviarNotificacoesWhatsApp extends Command
{
    protected $signature   = 'whatsapp:notificar {--empresa= : ID da empresa}';
    protected $description = 'Envia notificações de vencimento via WhatsApp (Evolution API)';

    public function handle(): int
    {
        $empresaId = $this->option('empresa');

        $query = IntegracaoConfig::where('tipo', 'whatsapp')->where('ativo', true);
        if ($empresaId) {
            $query->where('empresa_id', $empresaId);
        }

        foreach ($query->get() as $cfg) {
            $this->info("Processando empresa #{$cfg->empresa_id}...");
            try {
                $this->enviarAvisos($cfg);
            } catch (\Throwable $e) {
                $this->error("Empresa #{$cfg->empresa_id}: {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }

    private function enviarAvisos(IntegracaoConfig $cfg): void
    {
        if (! $cfg->config('notif_vencimento', true)) {
            return;
        }

        $diasAntecedencia = (int) $cfg->config('dias_antecedencia', 3);
        $vencimento = today()->addDays($diasAntecedencia);

        $contas = ContaReceber::where('empresa_id', $cfg->empresa_id)
            ->whereIn('status', ['pendente', 'parcial'])
            ->whereDate('data_vencimento', $vencimento)
            ->with('cliente')
            ->get();

        $enviadas = 0;
        foreach ($contas as $conta) {
            $telefone = $conta->cliente?->telefone ?? $conta->cliente?->celular;
            if (! $telefone) {
                continue;
            }

            $telefone = preg_replace('/\D/', '', $telefone);
            if (strlen($telefone) < 10) {
                continue;
            }

            // Adiciona 55 (Brasil) se não tiver DDI
            if (! str_starts_with($telefone, '55')) {
                $telefone = '55' . $telefone;
            }

            $valor   = 'R$ ' . number_format($conta->valor_total - $conta->valor_recebido, 2, ',', '.');
            $data    = $conta->data_vencimento->format('d/m/Y');
            $nome    = $conta->cliente?->nome_razao_social ?? 'Cliente';
            $mensagem = "Olá, {$nome}! 👋\n\n" .
                "Seu boleto de *{$valor}* vence em *{$data}*.\n\n" .
                "Ref: {$conta->descricao}\n\n" .
                "Em caso de dúvidas, entre em contato conosco.";

            $this->enviarMensagem($cfg, $telefone, $mensagem);
            $enviadas++;
        }

        $this->line("  ✓ {$enviadas} notificações enviadas para vencimento {$vencimento->format('d/m/Y')}.");
    }

    private function enviarMensagem(IntegracaoConfig $cfg, string $numero, string $mensagem): void
    {
        $url      = rtrim($cfg->config('url'), '/') . '/message/sendText/' . $cfg->config('instancia');
        $apiKey   = $cfg->config('api_key');

        Http::timeout(10)
            ->withHeader('apikey', $apiKey)
            ->post($url, [
                'number'  => $numero,
                'text'    => $mensagem,
                'delay'   => 1200,
            ]);
    }
}
