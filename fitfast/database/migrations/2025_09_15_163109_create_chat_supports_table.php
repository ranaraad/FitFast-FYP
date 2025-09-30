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
        Schema::create('chat_support', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null');
            $table->text('message');
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed'])->default('open');
            $table->enum('type', ['question', 'complaint', 'suggestion', 'technical'])->default('question');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            // Indexes for better performance
            $table->index('user_id');
            $table->index('admin_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_support');
    }
};
