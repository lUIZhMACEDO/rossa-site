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
        Schema::table('inferential_statistics', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->json('groups_tested')->nullable()->comment('Sample group IDs that had data for this test');
            $table->enum('status', ['pending', 'completed', 'failed', 'insufficient_data'])->default('completed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inferential_statistics', function (Blueprint $table) {
            $table->dropColumns(['groups_tested', 'status']);
            $table->enum('status', ['pending', 'completed', 'failed'])->default('completed');
        });
    }
};
