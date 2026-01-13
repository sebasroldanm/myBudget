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
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->foreignId('from_account_id')
                ->constrained('accounts')
                ->cascadeOnDelete();

            $table->foreignId('to_account_id')
                ->constrained('accounts')
                ->cascadeOnDelete();

            $table->decimal('amount', 15, 2);

            // Multidivisa
            $table->string('from_currency', 3);
            $table->string('to_currency', 3);

            $table->decimal('exchange_rate', 15, 8)->nullable();
            $table->decimal('amount_converted', 15, 2)->nullable();

            $table->date('transfer_date');
            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
