<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!\Schema::hasTable('evolution_configs')) {
            Schema::create('evolution_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullOnDelete();
            $table->string('nome', 100);
            $table->string('provider', 50)->default('evolution');
            $table->string('base_url', 255);
            $table->string('instance_name', 100)->nullable();
            $table->string('api_key', 255);
            $table->string('numero_remetente', 30)->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();

            $table->index(['empresa_id', 'ativo']);
        });
        }

        if (!\Schema::hasTable('whatsapp_regras')) {
            Schema::create('whatsapp_regras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('evolution_config_id')->nullable()->constrained('evolution_configs')->nullOnDelete();
            $table->string('nome', 100);
            $table->enum('tipo', ['vencimentos', 'fluxo_caixa', 'dre', 'recorrencias', 'cobranca']);
            $table->enum('periodicidade', ['diario', 'semanal', 'mensal']);
            $table->time('hora_envio')->default('08:00:00');
            $table->tinyInteger('dia_semana')->nullable()->comment('0=domingo, 6=sabado');
            $table->tinyInteger('dia_mes')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamp('ultimo_envio')->nullable();
            $table->timestamps();

            $table->index(['empresa_id', 'ativo']);
            $table->index(['empresa_id', 'tipo']);
        });
        }

        if (!\Schema::hasTable('whatsapp_destinatarios')) {
            Schema::create('whatsapp_destinatarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('regra_id')->constrained('whatsapp_regras')->cascadeOnDelete();
            $table->string('nome', 100);
            $table->string('telefone', 20);
            $table->timestamps();

            $table->index('regra_id');
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('whatsapp_destinatarios');
        Schema::dropIfExists('whatsapp_regras');
        Schema::dropIfExists('evolution_configs');
    }
};
