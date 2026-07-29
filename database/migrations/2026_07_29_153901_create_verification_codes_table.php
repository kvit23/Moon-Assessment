<?php

use App\Enums\VerificationTypeEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('verification_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('phone', 20)->index();
            $table->string('code', 6);
            $table->enum('type', VerificationTypeEnum::values())
                ->default(VerificationTypeEnum::PHONE_VERIFICATION->value);
            $table->unsignedSmallInteger('attempts')->default(0);
            $table->unsignedSmallInteger('max_attempts')->default(5);
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamp('blocked_at')->nullable();
            $table->timestamps();

            // Composite indexes for common queries
            $table->index(['user_id', 'type', 'used_at']);
            $table->index(['code', 'expires_at']);
            $table->index(['phone', 'type', 'used_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('verification_codes');
    }
};