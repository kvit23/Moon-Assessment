<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('back_in_stock_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamp('notified_at')->nullable(); // NULL = waiting, NOT NULL = notified
            $table->timestamps();

            //CRITICAL: Prevents duplicate subscriptions
            $table->unique(['user_id', 'product_id']);

            // Indexes for performance
            $table->index(['product_id', 'notified_at']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('back_in_stock_subscriptions');
    }
};