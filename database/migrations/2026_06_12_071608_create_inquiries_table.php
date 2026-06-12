<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inquiries', function (Blueprint $table) {
            $table->id();
            $table->string('inquiry_number')->unique();
            $table->foreignId('customer_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('distributor_profile_id')->constrained()->cascadeOnDelete();
            $table->enum('status', [
                'pending', 'quoted', 'negotiating', 'accepted', 'rejected', 'converted', 'cancelled',
            ])->default('pending');
            $table->text('customer_notes')->nullable();
            $table->string('delivery_city');
            $table->string('delivery_pincode');
            $table->date('expected_by')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'status']);
            $table->index(['distributor_profile_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inquiries');
    }
};
