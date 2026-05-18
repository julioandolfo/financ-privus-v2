<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nfes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('pedido_id')->nullable()->constrained('pedidos_vinculados')->nullOnDelete();
            $table->foreignId('conta_receber_id')->nullable()->constrained('contas_receber')->nullOnDelete();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->string('numero', 20)->nullable();
            $table->string('serie', 5)->default('1');
            $table->string('chave_acesso', 50)->nullable()->unique();
            $table->enum('status', ['rascunho', 'processando', 'autorizada', 'cancelada', 'denegada'])->default('rascunho');
            $table->string('natureza_operacao', 255)->default('Venda de Mercadoria');
            $table->decimal('valor_produtos', 15, 2)->default(0);
            $table->decimal('valor_frete', 15, 2)->default(0);
            $table->decimal('valor_desconto', 15, 2)->default(0);
            $table->decimal('valor_total', 15, 2)->default(0);
            $table->date('data_emissao')->nullable();
            $table->date('data_competencia')->nullable();
            $table->text('xml_nfe')->nullable();
            $table->string('pdf_danfe_url', 500)->nullable();
            $table->string('link_danfe', 500)->nullable();
            $table->string('webmaniabr_id', 100)->nullable();
            $table->text('motivo_cancelamento')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'status']);
            $table->index(['empresa_id', 'data_emissao']);
            $table->index(['empresa_id', 'cliente_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nfes');
    }
};
