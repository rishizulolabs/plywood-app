<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('core_type')->nullable()->after('grade');
            $table->string('number_of_plies')->nullable()->after('core_type');
            $table->string('is_standard')->nullable()->after('is_isi_marked');
            $table->string('finish_surface')->nullable()->after('warranty');
            $table->string('density')->nullable()->after('finish_surface');
            $table->boolean('termite_borer_treatment')->default(false)->after('density');
            $table->string('weight_per_sheet')->nullable()->after('termite_borer_treatment');
            $table->text('application')->nullable()->after('weight_per_sheet');
            $table->string('glue_type')->nullable()->after('application');
            $table->string('country_of_origin')->nullable()->after('glue_type');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'core_type',
                'number_of_plies',
                'is_standard',
                'finish_surface',
                'density',
                'termite_borer_treatment',
                'weight_per_sheet',
                'application',
                'glue_type',
                'country_of_origin',
            ]);
        });
    }
};
