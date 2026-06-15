<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['distributor_profile_id']);
            $table->foreignId('distributor_profile_id')->nullable()->change()->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['distributor_profile_id']);
            $table->foreignId('distributor_profile_id')->nullable(false)->change()->constrained()->cascadeOnDelete();
        });
    }
};
