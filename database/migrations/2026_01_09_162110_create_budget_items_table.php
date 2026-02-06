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
        Schema::create('budget_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            
            $table->decimal('expected_amount', 15, 2);
            $table->decimal('actual_amount', 15, 2)->default(0);
            
            $table->date('payment_date')->nullable();
            $table->date('pay_date')->nullable();
            $table->boolean('is_paid')->default(false);
            $table->text('notes')->nullable();
            
            $table->foreignId('account_id')->nullable()->constrained();
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_items');
    }
};
