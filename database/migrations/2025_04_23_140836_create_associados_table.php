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
        Schema::create('associados', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('sobrenome');
            $table->string('celular');
            $table->date('data_nascimento');
            $table->string('whatsapp')->nullable();
            $table->string('email')->nullable();
            $table->text('observacoes')->nullable();
            $table->boolean('frequenta_eventos')->default(false);
            $table->boolean('grupo_whatsapp')->default(false);
            $table->string('instagram')->nullable();
            $table->boolean('cartao_beneficios')->default(false);
            $table->date('cartao_beneficios_desde')->nullable();
            $table->text('endereco')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('associados');
    }
};
