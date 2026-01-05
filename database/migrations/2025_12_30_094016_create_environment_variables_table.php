<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('environment_variables', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->comment('Chave da variável de ambiente');
            $table->text('value')->comment('Valor da variável de ambiente');
            $table->string('description')->nullable()->comment('Descrição da variável');
            $table->boolean('is_active')->default(true)->comment('Se a variável está ativa');
            $table->boolean('is_encrypted')->default(false)->comment('Se o valor está criptografado');
            $table->string('category')->nullable()->comment('Categoria da variável (tokens, configs, etc)');
            $table->timestamps();
            
            // Índices para melhor performance
            $table->index(['key', 'is_active']);
            $table->index('category');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('environment_variables');
    }
};
