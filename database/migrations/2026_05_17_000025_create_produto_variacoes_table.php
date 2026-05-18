<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produto_variacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produto_id')->constrained('produtos')->cascadeOnDelete();
            $table->string('atributo', 50);
            $table->string('valor', 100);
            $table->string('sku', 100)->nullable();
            $table->decimal('preco_adicional', 15, 2)->default(0);
            $table->decimal('custo', 15, 2)->nullable();
            $table->decimal('estoque', 10, 3)->default(0);
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });

        Schema::create('produto_fotos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produto_id')->constrained('produtos')->cascadeOnDelete();
            $table->string('path', 500);
            $table->string('nome_original', 255)->nullable();
            $table->boolean('principal')->default(false);
            $table->tinyInteger('ordem')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produto_fotos');
        Schema::dropIfExists('produto_variacoes');
    }
};
