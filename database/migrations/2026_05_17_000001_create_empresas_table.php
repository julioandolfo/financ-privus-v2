<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!\Schema::hasTable('empresas')) {
            Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 20)->unique();
            $table->string('razao_social');
            $table->string('nome_fantasia')->nullable();
            $table->string('cnpj', 18)->nullable()->unique();
            $table->foreignId('grupo_empresarial_id')->nullable()->constrained('empresas')->nullOnDelete();
            $table->boolean('ativo')->default(true);
            $table->json('configuracoes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('empresas');
    }
};
