<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('currency')->default('SAR');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('package_services', function (Blueprint $table) {
            $table->foreignUlid('package_id')->constrained()->cascadeOnDelete();
            $table->foreignUlid('service_id')->constrained()->cascadeOnDelete();
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            // NO softDeletes — pivot tables don't soft-delete

            $table->primary(['package_id', 'service_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_services');
        Schema::dropIfExists('packages');
    }
};
