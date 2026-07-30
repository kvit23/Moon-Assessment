<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('sku')->unique();
            $table->decimal('price', 10, 2);
            $table->decimal('cost', 10, 2)->nullable();
            $table->integer('stock_quantity')->default(0);
            $table->integer('reorder_level')->default(5);
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->string('image')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('sku');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};