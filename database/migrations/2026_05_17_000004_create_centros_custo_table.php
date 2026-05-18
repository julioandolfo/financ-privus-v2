<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!\Schema::hasTable('centros_custo')) {
            Schema::create('centros_custo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained()->cascadeOnDelete();
            $table->string('codigo', 20)->nullable();
            $table->string('nome');
            $table->foreignId('centro_pai_id')->nullable()->constrained('centros_custo')->nullOnDelete();
            $table->boolean('ativo')->default(true);
            $table->timestamps();
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('centros_custo');
    }
};
