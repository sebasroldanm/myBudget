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
        Schema::create('transaction_logs', function (Blueprint $table) {
            $table->ulid('id')->primary();

            $table->unsignedBigInteger('transaction_id')->index();
            $table->unsignedBigInteger('transfer_id')->nullable()->index();

            $table->string('event'); // created, updated, deleted, locked, unlocked
            $table->timestamp('event_at');

            // ===== Transaction snapshot =====
            $table->string('transaction_type'); // income / expense
            $table->decimal('amount', 15, 2);
            $table->string('currency_amount');
            $table->date('transaction_date');
            $table->boolean('is_locked');
            $table->text('transaction_description')->nullable();

            // ===== Account snapshot =====
            $table->unsignedBigInteger('account_id')->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_type')->nullable();
            $table->string('account_currency')->nullable();

            // ===== Balance snapshot =====
            $table->decimal('balance_before', 15, 2)->nullable();
            $table->string('currency_balance_before')->nullable();
            $table->decimal('balance_after', 15, 2)->nullable();
            $table->string('currency_balance_after')->nullable();

            // ===== Product snapshot =====
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('product_name')->nullable();
            $table->string('product_vendor')->nullable();
            $table->string('product_currency')->nullable();
            $table->boolean('product_is_recurring')->nullable();
            $table->string('product_price_strategy')->nullable();
            $table->decimal('product_price', 15, 2)->nullable();
            $table->decimal('product_expected_price', 15, 2)->nullable();

            // ===== Request context =====
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_agent')->nullable(); // Navegador y OS
            $table->string('ip_address', 45)->nullable();

            // ===== Metadata =====
            $table->json('meta')->nullable();

            $table->timestamps();

            $table->index(['transaction_id', 'event']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_logs');
    }
};
