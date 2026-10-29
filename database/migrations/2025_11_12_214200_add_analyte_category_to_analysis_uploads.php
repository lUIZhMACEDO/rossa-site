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
            $table->foreignId('analyte_category_id')->nullable()->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('analysis_uploads', function (Blueprint $table) {
            $table->dropForeign(['analyte_category_id']);
            $table->dropColumn('analyte_category_id');
        });
    }
};
