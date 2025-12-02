<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->json('sizing_data')->nullable(); // Clothing measurements
            $table->integer('stock_quantity')->default(0);
            $table->json('color_variants')->nullable();
            $table->json('size_stock')->nullable();
            $table->string('garment_type')->nullable();
            $table->timestamps();

            $table->index('store_id');
            $table->index('category_id');
            $table->index('price');
            $table->index('stock_quantity');
            $table->index(['category_id', 'price']);
            $table->index(['store_id', 'created_at']);
            $table->fulltext('name');
            $table->fulltext('description');
        });


    }

    public function down()
    {
        Schema::dropIfExists('items');
    }
};
