<?php

namespace Database\Seeders;

use App\Models\FormaPagamento;
use Illuminate\Database\Seeder;

class BasicSeeder extends Seeder
{
    public function run(): void
    {
        $formas = [
            ['nome' => 'Dinheiro',           'tipo' => 'dinheiro',      'prazo_dias' => 0],
            ['nome' => 'PIX',                'tipo' => 'pix',           'prazo_dias' => 0],
            ['nome' => 'Boleto Bancário',    'tipo' => 'boleto',        'prazo_dias' => 3],
            ['nome' => 'Transferência (TED/DOC)', 'tipo' => 'transferencia', 'prazo_dias' => 1],
            ['nome' => 'Cartão de Crédito',  'tipo' => 'cartao_credito', 'prazo_dias' => 30],
            ['nome' => 'Cartão de Débito',   'tipo' => 'cartao_debito',  'prazo_dias' => 0],
            ['nome' => 'Cheque',             'tipo' => 'cheque',         'prazo_dias' => 0],
        ];

        foreach ($formas as $forma) {
            FormaPagamento::firstOrCreate(
                ['nome' => $forma['nome'], 'empresa_id' => null],
                array_merge($forma, ['empresa_id' => null, 'ativo' => true])
            );
        }

        $this->command->info('Formas de pagamento básicas criadas.');
    }
}
