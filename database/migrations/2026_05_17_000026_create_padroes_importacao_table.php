<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('padroes_importacao_extrato', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->string('descricao_contem', 255);
            $table->enum('tipo_correspondencia', ['contem', 'comeca_com', 'exato'])->default('contem');
            $table->enum('tipo_transacao', ['debito', 'credito', 'ambos'])->default('ambos');
            $table->foreignId('categoria_id')->nullable()->constrained('categorias_financeiras')->nullOnDelete();
            $table->string('descricao_padrao', 255)->nullable();
            $table->tinyInteger('prioridade')->default(0);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('padroes_importacao_extrato');
    }
};
