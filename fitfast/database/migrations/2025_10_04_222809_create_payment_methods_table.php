<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // credit_card, debit_card, paypal, etc.
            $table->text('details');
            $table->boolean('is_default')->default(false);
            $table->timestamps();

            // Add index for user lookup
            $table->index(['user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_methods');
    }
};
