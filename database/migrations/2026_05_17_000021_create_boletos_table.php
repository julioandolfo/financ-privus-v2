<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('boletos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('conta_receber_id')->nullable()->constrained('contas_receber')->nullOnDelete();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->string('numero_boleto', 50)->nullable();
            $table->string('nosso_numero', 50)->nullable();
            $table->string('linha_digitavel', 100)->nullable();
            $table->string('codigo_barras', 100)->nullable();
            $table->string('url_boleto', 500)->nullable();
            $table->decimal('valor', 15, 2);
            $table->date('data_vencimento');
            $table->date('data_emissao')->nullable();
            $table->date('data_pagamento')->nullable();
            $table->enum('status', ['rascunho', 'emitido', 'pago', 'cancelado', 'vencido'])->default('rascunho');
            $table->string('banco', 50)->nullable();
            $table->string('banco_referencia_id', 100)->nullable();
            $table->text('pix_qrcode')->nullable();
            $table->text('pix_copia_cola')->nullable();
            $table->text('instrucoes')->nullable();
            $table->decimal('multa', 5, 2)->default(2.00);
            $table->decimal('juros', 5, 2)->default(1.00);
            $table->decimal('desconto', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'status', 'data_vencimento']);
            $table->index(['empresa_id', 'cliente_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('boletos');
    }
};
