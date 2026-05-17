<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias_produto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('nome');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('categoria_id')->nullable()->constrained('categorias_produto')->nullOnDelete();

            $table->string('codigo', 50)->nullable();
            $table->string('sku', 100)->nullable();
            $table->string('codigo_barras', 50)->nullable();
            $table->string('nome');
            $table->text('descricao')->nullable();

            $table->decimal('custo_unitario', 15, 4)->default(0);
            $table->decimal('preco_venda', 15, 4)->default(0);
            $table->string('unidade_medida', 20)->default('UN');

            $table->decimal('estoque', 15, 3)->default(0);
            $table->decimal('estoque_minimo', 15, 3)->default(0);

            // Fiscal
            $table->string('ncm', 10)->nullable();
            $table->string('cest', 10)->nullable();
            $table->string('cfop', 10)->nullable();
            $table->decimal('aliquota_icms', 5, 2)->nullable();
            $table->decimal('aliquota_ipi', 5, 2)->nullable();
            $table->decimal('aliquota_pis', 5, 2)->nullable();
            $table->decimal('aliquota_cofins', 5, 2)->nullable();
            $table->string('origem_fiscal', 1)->nullable();
            $table->enum('tipo', ['produto', 'servico'])->default('produto');

            // WooCommerce
            $table->unsignedBigInteger('woo_id')->nullable()->index();

            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['empresa_id', 'codigo']);
            $table->index(['empresa_id', 'ativo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produtos');
        Schema::dropIfExists('categorias_produto');
    }
};
