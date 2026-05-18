<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!\Schema::hasTable('transacoes_pendentes')) {
            Schema::create('transacoes_pendentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('conta_bancaria_id')->nullable()->constrained('contas_bancarias')->nullOnDelete();
            $table->enum('tipo', ['debito', 'credito']);
            $table->decimal('valor', 15, 2);
            $table->date('data_transacao');
            $table->string('descricao_original', 500);
            $table->string('descricao_normalizada', 255)->nullable();
            $table->enum('status', ['pendente', 'aprovada', 'ignorada'])->default('pendente');
            $table->foreignId('categoria_sugerida_id')->nullable()->constrained('categorias_financeiras')->nullOnDelete();
            $table->foreignId('conta_pagar_id')->nullable()->constrained('contas_pagar')->nullOnDelete();
            $table->foreignId('conta_receber_id')->nullable()->constrained('contas_receber')->nullOnDelete();
            $table->foreignId('aprovada_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('aprovada_em')->nullable();
            $table->text('observacao')->nullable();
            $table->string('origem', 50)->default('manual');
            $table->timestamps();

            $table->index(['empresa_id', 'status', 'data_transacao']);
            $table->index(['empresa_id', 'tipo']);
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('transacoes_pendentes');
    }
};
