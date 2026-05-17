<?php

namespace App\Console\Commands;

use App\Models\CategoriaFinanceira;
use App\Models\Cliente;
use App\Models\ContaReceber;
use App\Models\IntegracaoConfig;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class SincronizarWooCommerce extends Command
{
    protected $signature   = 'woocommerce:sincronizar {--empresa= : ID da empresa}';
    protected $description = 'Sincroniza pedidos e clientes do WooCommerce';

    public function handle(): int
    {
        $empresaId = $this->option('empresa');

        $query = IntegracaoConfig::where('tipo', 'woocommerce')->where('ativo', true);
        if ($empresaId) {
            $query->where('empresa_id', $empresaId);
        }

        $integracoes = $query->get();

        if ($integracoes->isEmpty()) {
            $this->warn('Nenhuma integração WooCommerce ativa.');
            return self::SUCCESS;
        }

        foreach ($integracoes as $integracao) {
            $this->info("Sincronizando empresa #{$integracao->empresa_id}...");
            try {
                $this->sincronizar($integracao);
                $integracao->update(['ultimo_sync' => now(), 'status_sync' => 'ok', 'ultimo_erro' => null]);
            } catch (\Throwable $e) {
                $this->error("Erro: {$e->getMessage()}");
                $integracao->update(['status_sync' => 'erro', 'ultimo_erro' => $e->getMessage()]);
            }
        }

        return self::SUCCESS;
    }

    private function sincronizar(IntegracaoConfig $cfg): void
    {
        $baseUrl = rtrim($cfg->config('url'), '/') . '/wp-json/wc/v3';
        $auth    = [$cfg->config('consumer_key'), $cfg->config('consumer_secret')];

        if ($cfg->config('sync_clientes', true)) {
            $this->sincronizarClientes($cfg, $baseUrl, $auth);
        }

        if ($cfg->config('sync_pedidos', true)) {
            $this->sincronizarPedidos($cfg, $baseUrl, $auth);
        }
    }

    private function sincronizarClientes(IntegracaoConfig $cfg, string $baseUrl, array $auth): void
    {
        $page = 1;
        $synced = 0;

        do {
            $resp = Http::timeout(30)
                ->withBasicAuth(...$auth)
                ->get("{$baseUrl}/customers", ['page' => $page, 'per_page' => 100]);

            $clientes = $resp->json();
            if (empty($clientes) || ! is_array($clientes)) {
                break;
            }

            foreach ($clientes as $c) {
                Cliente::updateOrCreate(
                    ['empresa_id' => $cfg->empresa_id, 'email' => $c['email']],
                    [
                        'nome_razao_social' => trim(($c['first_name'] ?? '') . ' ' . ($c['last_name'] ?? '')) ?: $c['email'],
                        'email'             => $c['email'],
                        'telefone'          => $c['billing']['phone'] ?? null,
                        'cpf_cnpj'          => $c['meta_data'][0]['value'] ?? null,
                        'ativo'             => true,
                        'tipo'              => 'pessoa_fisica',
                    ]
                );
                $synced++;
            }

            $page++;
        } while (count($clientes) === 100);

        $this->line("  ✓ {$synced} clientes sincronizados.");
    }

    private function sincronizarPedidos(IntegracaoConfig $cfg, string $baseUrl, array $auth): void
    {
        $since   = $cfg->ultimo_sync?->toIso8601String() ?? now()->subDay()->toIso8601String();
        $page    = 1;
        $synced  = 0;

        do {
            $resp = Http::timeout(30)
                ->withBasicAuth(...$auth)
                ->get("{$baseUrl}/orders", [
                    'page'       => $page,
                    'per_page'   => 100,
                    'after'      => $since,
                    'status'     => 'processing,completed',
                ]);

            $pedidos = $resp->json();
            if (empty($pedidos) || ! is_array($pedidos)) {
                break;
            }

            $categoriaId = $cfg->config('categoria_id');
            $formaPgtoId = $cfg->config('forma_pgto_id');

            foreach ($pedidos as $p) {
                $clienteEmail = $p['billing']['email'] ?? null;
                $cliente = $clienteEmail
                    ? Cliente::where('empresa_id', $cfg->empresa_id)->where('email', $clienteEmail)->first()
                    : null;

                $nomeCliente = trim(($p['billing']['first_name'] ?? '') . ' ' . ($p['billing']['last_name'] ?? '')) ?: 'Cliente WooCommerce';
                $numero = 'WOO-' . $p['id'];

                ContaReceber::updateOrCreate(
                    ['empresa_id' => $cfg->empresa_id, 'numero_documento' => $numero],
                    [
                        'empresa_id'        => $cfg->empresa_id,
                        'user_id'           => 1,
                        'cliente_id'        => $cliente?->id,
                        'categoria_id'      => $categoriaId,
                        'forma_recebimento_id' => $formaPgtoId,
                        'numero_documento'  => $numero,
                        'descricao'         => "Pedido #{$p['id']} WooCommerce — {$nomeCliente}",
                        'valor_total'       => $p['total'],
                        'valor_recebido'    => in_array($p['status'], ['completed', 'processing']) ? $p['total'] : 0,
                        'data_vencimento'   => now()->toDateString(),
                        'data_recebimento'  => in_array($p['status'], ['completed']) ? now()->toDateString() : null,
                        'status'            => in_array($p['status'], ['completed']) ? 'recebido' : 'pendente',
                    ]
                );
                $synced++;
            }

            $page++;
        } while (count($pedidos) === 100);

        $this->line("  ✓ {$synced} pedidos sincronizados.");
    }
}
