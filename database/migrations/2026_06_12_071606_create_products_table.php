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
            $table->foreignId('distributor_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('thickness')->nullable();
            $table->string('size')->nullable();
            $table->string('grade')->nullable();
            $table->string('brand')->nullable();
            $table->boolean('is_isi_marked')->default(false);
            $table->string('warranty')->nullable();
            $table->unsignedInteger('min_order_qty')->default(1);
            $table->string('unit')->default('sheet');
            $table->boolean('in_stock')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['distributor_profile_id', 'category_id']);
            $table->index('in_stock');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
