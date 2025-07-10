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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('additions', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->boolean('is_required')->default(true);
            $table->boolean('is_multiple')->default(false);
            $table->timestamps();
        });

        Schema::create('addition_category', function (Blueprint $table) {
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('addition_id')->constrained()->onDelete('cascade');
            $table->primary(['category_id', 'addition_id']);
        });

        Schema::create('addition_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('addition_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->decimal('price', 10, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('address');
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('barcode')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price_buy', 10, 2);
            $table->decimal('price_sell', 10, 2);
            $table->integer('stock');
            $table->foreignId('category_id')->constrained()->onDelete('restrict');
            $table->foreignId('unit_id')->constrained()->onDelete('restrict');
            $table->foreignId('supplier_id')->nullable()->constrained()->onDelete('set null');
            $table->boolean('status')->default(true);
            $table->string('image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
        Schema::dropIfExists('units');
        Schema::dropIfExists('addition_items');
        Schema::dropIfExists('addition_category');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('additions');
        Schema::dropIfExists('suppliers');
    }
};
