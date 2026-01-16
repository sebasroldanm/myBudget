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
        Schema::create('budgets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('name', 100);
            $table->string('currency', 3)->default('COP');

            $table->date('period_start');
            $table->date('period_end');

            $table->string('status')->default('draft');

            $table->timestamps();

            $table->softDeletes();

            $table->unique(['user_id', 'period_start', 'period_end', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budgets');
    }
};
