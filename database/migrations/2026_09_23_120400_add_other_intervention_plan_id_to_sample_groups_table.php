<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sample_groups', function (Blueprint $table) {
            $table->foreignId('other_intervention_plan_id')
                ->nullable()
                ->after('drug_administration_plan_id')
                ->constrained('other_intervention_plans')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sample_groups', function (Blueprint $table) {
            $table->dropConstrainedForeignId('other_intervention_plan_id');
        });
    }
};
