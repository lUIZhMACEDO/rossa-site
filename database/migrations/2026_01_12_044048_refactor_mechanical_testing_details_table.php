<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mechanical_testing_details', function (Blueprint $table) {
            // Remove the old fixed boolean column
            $table->dropColumn('fixed');

            // Add new storage and testing condition columns
            $table->string('storage_medium')->nullable();
            $table->text('storage_medium_other')->nullable();
            $table->string('test_conditioning')->nullable();
            $table->text('test_conditioning_other')->nullable();
            $table->string('storage_temperature')->nullable();
            $table->text('storage_temperature_other')->nullable();
            $table->string('testing_temperature')->nullable();
            $table->text('testing_temperature_other')->nullable();

            // Add preload decision column
            $table->boolean('use_preload')->default(false);

            // 3pt and 4pt bending
            $table->decimal('loading_rate_bending', 8, 4)->nullable();

            $table->decimal('loading_rate_compression', 8, 4)->nullable();
            $table->string('loading_rate_compression_unit')->default('mm/s')->nullable();

            $table->decimal('loading_rate_torsion', 8, 4)->nullable();
            $table->string('loading_rate_torsion_unit')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mechanical_testing_details', function (Blueprint $table) {
            // Remove new columns
            $table->dropColumn('storage_medium');
            $table->dropColumn('storage_medium_other');
            $table->dropColumn('test_conditioning');
            $table->dropColumn('test_conditioning_other');
            $table->dropColumn('storage_temperature');
            $table->dropColumn('storage_temperature_other');
            $table->dropColumn('testing_temperature');
            $table->dropColumn('testing_temperature_other');
            $table->dropColumn('use_preload');

            // Restore old fixed column
            $table->boolean('fixed')->nullable();

            $table->dropColumn('loading_rate_bending');
            $table->dropColumn('loading_rate_compression');
            $table->dropColumn('loading_rate_compression_unit');
            $table->dropColumn('loading_rate_torsion');
            $table->dropColumn('loading_rate_torsion_unit');
        });
    }
};
