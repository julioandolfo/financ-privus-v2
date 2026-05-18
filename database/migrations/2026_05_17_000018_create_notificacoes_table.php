<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notificacoes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empresa_id')->constrained('empresas')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('tipo', 50); // 'vencimento', 'recorrencia', 'pagamento', 'sistema', 'alerta'
            $table->string('titulo', 255);
            $table->text('mensagem');
            $table->string('link', 255)->nullable();
            $table->string('icone', 50)->default('bell');
            $table->string('cor', 20)->default('blue'); // 'blue','red','green','yellow','orange'
            $table->boolean('lida')->default(false);
            $table->timestamp('lida_em')->nullable();
            $table->timestamps();

            $table->index(['empresa_id', 'user_id', 'lida']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificacoes');
    }
};
