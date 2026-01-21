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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('type', 30); // cash, bank, credit, investment
            $table->string('currency', 3)->default('COP');

            $table->decimal('initial_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);

            // credit card
            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->decimal('credit_available', 15, 2)->default(0);
            $table->decimal('credit_interest_rate', 5, 2)->default(0);
            $table->date('credit_due_date')->nullable();
            $table->date('credit_payment_date')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
