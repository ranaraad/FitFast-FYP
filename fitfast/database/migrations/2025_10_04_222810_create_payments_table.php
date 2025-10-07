<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('payment_method_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('transaction_id')->nullable()->comment('Payment gateway transaction ID');
            $table->string('status')->default('pending'); // pending, processing, completed, failed, refunded
            $table->timestamps();

            // Add indexes for performance
            $table->index(['transaction_id']);
            $table->index(['status']);
            $table->index(['created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
