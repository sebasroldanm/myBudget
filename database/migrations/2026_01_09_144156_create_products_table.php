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
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('category_id')
                ->constrained()
                ->restrictOnDelete();

            $table->foreignId('default_account_id')
                ->nullable()
                ->constrained('accounts')
                ->nullOnDelete();

            $table->string('vendor')->nullable();
            $table->string('name');
            $table->decimal('price', 15, 2);

            $table->boolean('is_recurring')->default(false);
            $table->decimal('expected_price', 15, 2)->nullable();
            $table->date('payment_date')->nullable();
            $table->string('periodicity')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('currency')->nullable();

            $table->enum('price_strategy', ['fixed', 'variable', 'estimate'])
                ->default('fixed');

            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();

            $table->softDeletes();

            $table->timestamps();

            $table->unique(['user_id', 'category_id', 'name', 'vendor']);

            $table->index(['user_id', 'is_active']);
            $table->index(['user_id', 'is_recurring']);
            $table->index(['category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
