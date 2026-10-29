<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inferential_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('statistics_job_id')->constrained()->cascadeOnDelete();

            // context/what stat is for
            $table->foreignId('comparison_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('analysis_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('specimen_type_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('analyte_category_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('region', 100)->nullable();
            $table->foreignId('measurement_id')->constrained()->cascadeOnDelete();

            // test details/results
            $table->string('test_type', 50)->comment('t_test, anova, tukey, shapiro_wilk, levene, etc.');
            $table->decimal('p_value', 10, 6)->nullable();
            $table->decimal('test_statistic', 12, 4)->nullable()->comment('t, F, etc.');
            $table->json('metadata')->nullable()->comment('Test-specific data (group comparisons, degrees of freedom, etc.)');
            $table->enum('status', ['pending', 'completed', 'failed'])->default('completed');
            $table->timestamps();
        });

        // computed context key (handle issue with null != null)
        DB::statement("
            ALTER TABLE inferential_statistics 
            ADD COLUMN context_key VARCHAR(255) GENERATED ALWAYS AS (
                CAST(analysis_type_id AS TEXT) || ':' ||
                COALESCE(CAST(specimen_type_id AS TEXT), 'NULL') || ':' ||
                COALESCE(CAST(analyte_category_id AS TEXT), 'NULL') || ':' ||
                COALESCE(region, 'NULL')
            ) STORED
        ");

        // unique constraints and indexes using context_key
        Schema::table('inferential_statistics', function (Blueprint $table) {
            // one result per (context, comparison_group, measurement, test_type)
            $table->unique(['context_key', 'comparison_group_id', 'measurement_id', 'test_type'], 'inferential_stats_unique');

            $table->index(['context_key', 'comparison_group_id'], 'inferential_stats_context_idx');
            $table->index('measurement_id');
            $table->index('test_type');
            $table->index('statistics_job_id');
            $table->index('context_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inferential_statistics');
    }
};
