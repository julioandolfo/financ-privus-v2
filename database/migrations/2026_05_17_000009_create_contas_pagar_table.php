<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!\Schema::hasTable('contas_pagar')) {
            Schema::create('contas_pagar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fornecedor_id')->nullable()->constrained('fornecedores')->nullOnDelete();
            $table->foreignId('categoria_id')->nullable()->constrained('categorias_financeiras')->nullOnDelete();
            $table->foreignId('centro_custo_id')->nullable()->constrained('centros_custo')->nullOnDelete();
            $table->foreignId('forma_pagamento_id')->nullable()->constrained('formas_pagamento')->nullOnDelete();
            $table->foreignId('conta_bancaria_id')->nullable()->constrained('contas_bancarias')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('numero_documento', 50)->nullable();
            $table->string('descricao');
            $table->decimal('valor_total', 15, 2);
            $table->decimal('valor_pago', 15, 2)->default(0);
            $table->decimal('desconto', 15, 2)->default(0);
            $table->decimal('juros', 15, 2)->default(0);
            $table->decimal('multa', 15, 2)->default(0);
            $table->date('data_vencimento');
            $table->date('data_competencia')->nullable();
            $table->date('data_pagamento')->nullable();
            $table->enum('status', ['pendente', 'pago', 'parcial', 'cancelado', 'vencido'])->default('pendente');
            $table->boolean('tem_rateio')->default(false);
            $table->text('observacoes')->nullable();
            $table->string('anexo')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'status', 'data_vencimento']);
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('contas_pagar');
    }
};
