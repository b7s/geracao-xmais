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
        // Comentado para permitir cadastros duplicados temporariamente
        // A validação será feita apenas no nível da aplicação
        // Schema::table('associados', function (Blueprint $table) {
        //     $table->unique(['celular', 'data_nascimento'], 'associados_celular_data_nascimento_unique');
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::table('associados', function (Blueprint $table) {
        //     $table->dropUnique('associados_celular_data_nascimento_unique');
        // });
    }
}; 