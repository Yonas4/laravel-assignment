<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_gateway_logs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('transaction_id')->constrained('payment_transactions')->cascadeOnDelete();
            $table->string('gateway');
            $table->string('event');
            $table->string('direction');
            $table->json('payload')->nullable();
            $table->timestamps();
            // NO softDeletes — logs are immutable
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_gateway_logs');
    }
};
