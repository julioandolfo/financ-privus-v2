<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!\Schema::hasTable('despesas_recorrentes')) {
            Schema::create('despesas_recorrentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('fornecedor_id')->nullable()->constrained('fornecedores')->nullOnDelete();
            $table->foreignId('categoria_id')->nullable()->constrained('categorias_financeiras')->nullOnDelete();
            $table->foreignId('centro_custo_id')->nullable()->constrained('centros_custo')->nullOnDelete();
            $table->foreignId('forma_pagamento_id')->nullable()->constrained('formas_pagamento')->nullOnDelete();
            $table->foreignId('conta_bancaria_id')->nullable()->constrained('contas_bancarias')->nullOnDelete();

            $table->string('descricao');
            $table->decimal('valor', 15, 2);
            $table->enum('frequencia', ['diaria','semanal','quinzenal','mensal','bimestral','trimestral','semestral','anual','personalizado'])->default('mensal');
            $table->unsignedTinyInteger('dia_mes')->nullable();
            $table->unsignedTinyInteger('dia_semana')->nullable();
            $table->unsignedSmallInteger('intervalo_dias')->nullable();
            $table->date('data_inicio');
            $table->date('data_fim')->nullable();
            $table->unsignedInteger('max_ocorrencias')->nullable();
            $table->unsignedInteger('ocorrencias_geradas')->default(0);
            $table->date('proxima_geracao')->nullable();
            $table->date('ultima_geracao')->nullable();
            $table->unsignedTinyInteger('antecedencia_dias')->default(5);
            $table->enum('status_inicial', ['pendente','pago'])->default('pendente');
            $table->boolean('criar_automaticamente')->default(true);
            $table->enum('ajuste_fim_semana', ['manter','antecipar','postergar'])->default('manter');
            $table->boolean('ativo')->default(true);
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
        }

        if (!\Schema::hasTable('receitas_recorrentes')) {
            Schema::create('receitas_recorrentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas');
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->foreignId('categoria_id')->nullable()->constrained('categorias_financeiras')->nullOnDelete();
            $table->foreignId('centro_custo_id')->nullable()->constrained('centros_custo')->nullOnDelete();
            $table->foreignId('forma_pagamento_id')->nullable()->constrained('formas_pagamento')->nullOnDelete();
            $table->foreignId('conta_bancaria_id')->nullable()->constrained('contas_bancarias')->nullOnDelete();

            $table->string('descricao');
            $table->decimal('valor', 15, 2);
            $table->enum('frequencia', ['diaria','semanal','quinzenal','mensal','bimestral','trimestral','semestral','anual','personalizado'])->default('mensal');
            $table->unsignedTinyInteger('dia_mes')->nullable();
            $table->unsignedTinyInteger('dia_semana')->nullable();
            $table->unsignedSmallInteger('intervalo_dias')->nullable();
            $table->date('data_inicio');
            $table->date('data_fim')->nullable();
            $table->unsignedInteger('max_ocorrencias')->nullable();
            $table->unsignedInteger('ocorrencias_geradas')->default(0);
            $table->date('proxima_geracao')->nullable();
            $table->date('ultima_geracao')->nullable();
            $table->unsignedTinyInteger('antecedencia_dias')->default(5);
            $table->enum('status_inicial', ['pendente','recebido'])->default('pendente');
            $table->boolean('criar_automaticamente')->default(true);
            $table->enum('ajuste_fim_semana', ['manter','antecipar','postergar'])->default('manter');
            $table->boolean('ativo')->default(true);
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
        }

        // Link contas geradas de volta à recorrência
        Schema::table('contas_pagar', function (Blueprint $table) {
            $table->foreignId('despesa_recorrente_id')->nullable()->after('user_id')
                ->constrained('despesas_recorrentes')->nullOnDelete();
        });

        Schema::table('contas_receber', function (Blueprint $table) {
            $table->foreignId('receita_recorrente_id')->nullable()->after('user_id')
                ->constrained('receitas_recorrentes')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('contas_receber', fn($t) => $t->dropForeignIdFor(\App\Models\ReceitaRecorrente::class));
        Schema::table('contas_pagar',   fn($t) => $t->dropForeignIdFor(\App\Models\DespesaRecorrente::class));
        Schema::dropIfExists('receitas_recorrentes');
        Schema::dropIfExists('despesas_recorrentes');
    }
};
