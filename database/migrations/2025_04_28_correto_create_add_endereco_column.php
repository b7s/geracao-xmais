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
        // Verifica se a coluna ainda não existe para evitar erros
        if (!Schema::hasColumn('associados', 'endereco')) {
            Schema::table('associados', function (Blueprint $table) {
                $table->text('endereco')->nullable()->after('cartao_beneficios_desde');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('associados', 'endereco')) {
            Schema::table('associados', function (Blueprint $table) {
                $table->dropColumn('endereco');
            });
        }
    }
}; 