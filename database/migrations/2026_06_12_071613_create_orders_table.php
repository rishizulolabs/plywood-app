<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('inquiry_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('distributor_profile_id')->constrained()->cascadeOnDelete();
            $table->decimal('total_amount', 12, 2);
            $table->enum('payment_status', ['pending', 'partial', 'paid'])->default('pending');
            $table->enum('fulfillment_status', ['processing', 'dispatched', 'delivered', 'cancelled'])->default('processing');
            $table->text('delivery_address');
            $table->string('invoice_path')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'fulfillment_status']);
            $table->index(['distributor_profile_id', 'fulfillment_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
