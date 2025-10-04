<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('cart_total', 10, 2)->default(0);
            $table->timestamps();

            // Ensure one cart per user
            $table->unique(['user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('carts');
    }
};
