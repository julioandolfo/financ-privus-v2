<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos_vinculados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();

            $table->enum('origem', ['manual', 'woocommerce', 'marketplace'])->default('manual');
            $table->string('origem_id', 100)->nullable()->comment('External order ID (e.g. WooCommerce order ID)');
            $table->string('numero_pedido', 100);

            $table->enum('status', ['pendente', 'processando', 'concluido', 'cancelado', 'reembolsado'])->default('pendente');
            $table->string('status_origem', 50)->nullable()->comment('Original status from WooCommerce or marketplace');

            $table->decimal('valor_total', 15, 2)->default(0);
            $table->decimal('valor_custo_total', 15, 2)->default(0);
            $table->decimal('desconto', 15, 2)->default(0);

            $table->date('data_pedido');
            $table->text('observacoes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'status']);
            $table->index(['empresa_id', 'origem']);
            $table->index(['empresa_id', 'data_pedido']);
            $table->index(['empresa_id', 'cliente_id']);
            $table->index(['origem', 'origem_id']);
        });

        Schema::create('pedidos_itens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pedido_id')->constrained('pedidos_vinculados')->cascadeOnDelete();
            $table->foreignId('produto_id')->nullable()->constrained('produtos')->nullOnDelete();

            $table->string('codigo_produto_origem', 100)->nullable();
            $table->string('nome_produto', 255);

            $table->decimal('quantidade', 15, 3)->default(1);
            $table->decimal('valor_unitario', 15, 2)->default(0);
            $table->decimal('valor_total', 15, 2)->default(0);
            $table->decimal('custo_unitario', 15, 2)->default(0);
            $table->decimal('custo_total', 15, 2)->default(0);

            $table->timestamps();

            $table->index('pedido_id');
            $table->index('produto_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos_itens');
        Schema::dropIfExists('pedidos_vinculados');
    }
};
