<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integracoes_config', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('tipo', 50); // woocommerce, whatsapp, boleto, nfe, banco_openbanking
            $table->string('nome')->nullable();
            $table->boolean('ativo')->default(false);
            $table->json('configuracoes')->nullable();
            $table->timestamp('ultimo_sync')->nullable();
            $table->string('status_sync', 50)->nullable(); // ok, erro, sincronizando
            $table->text('ultimo_erro')->nullable();
            $table->timestamps();

            $table->unique(['empresa_id', 'tipo']);
        });

        Schema::create('integracoes_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('tipo', 50);
            $table->enum('nivel', ['info', 'warning', 'error', 'success'])->default('info');
            $table->string('mensagem');
            $table->json('dados')->nullable();
            $table->timestamps();

            $table->index(['empresa_id', 'tipo', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integracoes_logs');
        Schema::dropIfExists('integracoes_config');
    }
};
