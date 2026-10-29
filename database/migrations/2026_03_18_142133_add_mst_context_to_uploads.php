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
            $table->string('test_method', 50)->nullable();
            $table->string('bone_tested', 50)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('analysis_uploads', function (Blueprint $table) {
            $table->dropColumn('test_method');
            $table->dropColumn('bone_tested');
        });
    }
};
