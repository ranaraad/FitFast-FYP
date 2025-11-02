<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->string('tracking_id')->nullable()->comment('3rd party API tracking ID');
            $table->string('carrier')->nullable()->comment('aramex, dhl, etc.');
            $table->dateTime('estimated_delivery')->default(DB::raw('DATE_ADD(NOW(), INTERVAL 3 DAY)'));
            $table->string('status')->default('pending'); // pending, shipped, in_transit, out_for_delivery, delivered, failed
            $table->text('address');
            $table->timestamps();

            // Add index for tracking and performance
            $table->index(['tracking_id']);
            $table->index(['status']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('deliveries');
    }
};
