<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->nullable()->constrained('empresas')->nullOnDelete();
            $table->string('chave', 100);
            $table->text('valor')->nullable();
            $table->enum('tipo', ['string', 'integer', 'boolean', 'json'])->default('string');
            $table->timestamps();

            $table->unique(['empresa_id', 'chave']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracoes');
    }
};
