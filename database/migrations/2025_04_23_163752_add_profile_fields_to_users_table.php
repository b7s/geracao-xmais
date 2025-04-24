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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->nullable()->after('email');
            $table->date('birth_date')->nullable()->after('phone');
            $table->string('gender')->nullable()->after('birth_date');
            $table->string('cpf', 20)->nullable()->unique()->after('gender');
            $table->string('address')->nullable()->after('cpf');
            $table->string('city', 100)->nullable()->after('address');
            $table->string('state', 2)->nullable()->after('city');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone',
                'birth_date',
                'gender',
                'cpf',
                'address',
                'city',
                'state',
            ]);
        });
    }
};
