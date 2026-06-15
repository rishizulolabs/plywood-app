<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restock_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('distributor_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_amount', 12, 2);
            $table->enum('status', ['pending', 'approved', 'fulfilled', 'cancelled'])->default('pending');
            $table->timestamps();

            $table->index(['distributor_profile_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restock_requests');
    }
};
