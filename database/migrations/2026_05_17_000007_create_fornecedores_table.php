<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!\Schema::hasTable('fornecedores')) {
            Schema::create('fornecedores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->cascadeOnDelete();
            $table->string('codigo', 20)->nullable();
            $table->enum('tipo', ['fisica', 'juridica'])->default('juridica');
            $table->string('nome_razao_social');
            $table->string('nome_fantasia')->nullable();
            $table->string('cpf_cnpj', 18)->nullable();
            $table->string('email')->nullable();
            $table->string('telefone', 20)->nullable();
            $table->string('celular', 20)->nullable();
            $table->json('endereco')->nullable();
            $table->text('observacoes')->nullable();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['empresa_id', 'ativo']);
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('fornecedores');
    }
};
