<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained()->cascadeOnDelete();
            $table->string('gateway');
            $table->string('gateway_transaction_id')->nullable()->unique();
            $table->string('module');
            $table->string('city')->nullable();
            $table->decimal('amount', 10, 2);
            $table->string('currency')->default('SAR');
            $table->string('status')->default('pending');
            $table->string('reference')->nullable()->unique();
            $table->string('idempotency_key')->unique();
            $table->json('metadata')->nullable();
            $table->json('gateway_response')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index('gateway');
            $table->index('status');
            $table->index('module');
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
