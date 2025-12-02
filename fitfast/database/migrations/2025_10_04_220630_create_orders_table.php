<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('total_amount', 10, 2);
            $table->string('status')->default('pending'); // pending, confirmed, shipped, delivered, cancelled
            $table->timestamps();

            $table->index('user_id');
            $table->index('store_id');
            $table->index('status');
            $table->index('total_amount');
            $table->index(['user_id', 'created_at']);
            $table->index(['store_id', 'status', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
