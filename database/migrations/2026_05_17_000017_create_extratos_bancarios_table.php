<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extratos_bancarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('conta_bancaria_id')->constrained('contas_bancarias');
            $table->string('nome_arquivo', 255);
            $table->enum('tipo', ['ofx', 'csv']);
            $table->date('data_inicio')->nullable();
            $table->date('data_fim')->nullable();
            $table->timestamps();

            $table->index(['empresa_id', 'conta_bancaria_id']);
        });

        Schema::create('extratos_lancamentos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('extrato_id')->constrained('extratos_bancarios')->cascadeOnDelete();
            $table->string('fitid', 100)->nullable();
            $table->date('data_lancamento');
            $table->decimal('valor', 15, 2);
            $table->enum('tipo', ['credito', 'debito']);
            $table->string('descricao', 500)->nullable();
            $table->foreignId('movimentacao_id')->nullable()->constrained('movimentacoes_caixa')->nullOnDelete();
            $table->boolean('conciliado')->default(false);
            $table->boolean('ignorado')->default(false);
            $table->timestamps();

            $table->index(['extrato_id', 'conciliado', 'ignorado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extratos_lancamentos');
        Schema::dropIfExists('extratos_bancarios');
    }
};
