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
        Schema::table('sample_groups', function (Blueprint $table) {
            // Add the new foreign key for drug administration plans
            $table->foreignId('drug_administration_plan_id')
                ->nullable()
                ->after('drug_treatment_id')
                ->constrained('drug_administration_plans')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sample_groups', function (Blueprint $table) {
            $table->dropForeign(['drug_administration_plan_id']);
            $table->dropColumn('drug_administration_plan_id');
        });
    }
};
