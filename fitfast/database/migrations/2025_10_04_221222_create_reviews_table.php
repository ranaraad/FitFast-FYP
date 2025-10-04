<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->integer('rating'); // 1-5 stars
            $table->text('comment')->nullable();
            $table->timestamps();

            // Ensure a user can only review an item once
            $table->unique(['user_id', 'item_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('reviews');
    }
};
