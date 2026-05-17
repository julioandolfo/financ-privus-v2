<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parcelas_receber', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conta_receber_id')->constrained()->cascadeOnDelete();
            $table->integer('numero_parcela');
            $table->decimal('valor_parcela', 15, 2);
            $table->decimal('valor_recebido', 15, 2)->default(0);
            $table->decimal('desconto', 15, 2)->default(0);
            $table->decimal('juros', 15, 2)->default(0);
            $table->decimal('multa', 15, 2)->default(0);
            $table->date('data_vencimento');
            $table->date('data_recebimento')->nullable();
            $table->enum('status', ['pendente', 'recebido', 'cancelado', 'vencido'])->default('pendente');
            $table->foreignId('forma_recebimento_id')->nullable()->constrained('formas_pagamento')->nullOnDelete();
            $table->foreignId('conta_bancaria_id')->nullable()->constrained('contas_bancarias')->nullOnDelete();
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parcelas_receber');
    }
};
