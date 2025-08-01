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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->string('invoice');
            $table->dateTime('date');
            $table->string('customer_name');
            $table->decimal('total_price', 10, 2);
            $table->foreignId('table_id')->constrained('tables')->onDelete('cascade');
            $table->foreignId('action_by')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('cashier_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->boolean('is_payment')->default(false);
            $table->enum('status', ['approved', 'rejected', 'cancelled', 'pending', 'success'])->default('pending');
            $table->timestamps();
        });

        Schema::create('transaction_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->decimal('price', 10, 2);
            $table->integer('qty');
            $table->decimal('sub_price', 10, 2);
            $table->json('additions')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('transaction_details');
    }
};
