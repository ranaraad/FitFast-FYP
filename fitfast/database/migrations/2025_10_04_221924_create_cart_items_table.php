<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cart_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->integer('quantity');
            $table->string('selected_size')->nullable();
            $table->string('selected_color')->nullable();
            $table->decimal('item_price', 10, 2); // Price at time of adding to cart
            $table->timestamps();

            // Ensure unique combination of cart, item, size, and color
            $table->unique(['cart_id', 'item_id', 'selected_size', 'selected_color']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('cart_items');
    }
};
