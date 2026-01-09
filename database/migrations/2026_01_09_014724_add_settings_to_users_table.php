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
            $table->string('timezone', 50)->default('America/Bogota')->after('email');
            $table->string('locale', 10)->default('es')->after('timezone');
            $table->string('currency_default', 3)->default('COP')->after('locale');
            $table->boolean('is_active')->default(true)->after('currency_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('timezone');
            $table->dropColumn('locale');
            $table->dropColumn('currency_default');
            $table->dropColumn('is_active');
        });
    }
};
