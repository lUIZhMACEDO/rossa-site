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
        Schema::table('analysis_uploads', function (Blueprint $table) {
            $table->integer('header_row_index')->nullable();
            $table->json('column_measurement_mappings')->default(json_encode([]));
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('analysis_uploads', function (Blueprint $table) {
            $table->dropColumn('header_row_index');
            $table->dropColumn('column_measurement_mappings');
        });
    }
};
