<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // drop existing statistics tables (CASCADE removes all dependencies)
        DB::statement('DROP TABLE IF EXISTS inferential_statistics CASCADE');
        DB::statement('DROP TABLE IF EXISTS descriptive_statistics CASCADE');
        DB::statement('DROP TABLE IF EXISTS statistics_jobs CASCADE');

        // recreate statistics_jobs with region_id
        Schema::create('statistics_jobs', function (Blueprint $table) {
            $table->id();

            // sample group id or comparison group id (descriptive or inferential)
            $table->foreignId('sample_group_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('comparison_group_id')->nullable()->constrained()->cascadeOnDelete();

            // context fields - unique key on analysis type, specimen type, analyte category, and region
            $table->foreignId('analysis_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('specimen_type_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('analyte_category_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('region_id')->nullable()->constrained('regions')->cascadeOnDelete();

            // job status and metadata
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->unsignedInteger('processing_version')->default(0)->comment('Incremented on each dispatch; jobs abort if version changes');
            $table->integer('measurements_count')->nullable()->comment('Number of measurements processed in this context');
            $table->timestamp('completed_at')->nullable();
            $table->text('error_message')->nullable();

            $table->timestamps();
        });

        // ensure there is ONE sample group id or comparison group id (use raw SQL for check constraint)
        DB::statement('
            ALTER TABLE statistics_jobs 
            ADD CONSTRAINT stats_jobs_one_target_check 
            CHECK (
                (sample_group_id IS NOT NULL AND comparison_group_id IS NULL) OR 
                (sample_group_id IS NULL AND comparison_group_id IS NOT NULL)
            )
        ');

        // computed context key (handle issue with null != null)
        DB::statement("
            ALTER TABLE statistics_jobs 
            ADD COLUMN context_key VARCHAR(255) GENERATED ALWAYS AS (
                CAST(analysis_type_id AS TEXT) || ':' ||
                COALESCE(CAST(specimen_type_id AS TEXT), 'NULL') || ':' ||
                COALESCE(CAST(analyte_category_id AS TEXT), 'NULL') || ':' ||
                COALESCE(CAST(region_id AS TEXT), 'NULL')
            ) STORED
        ");

        // unique constraints using the context_key
        Schema::table('statistics_jobs', function (Blueprint $table) {
            // one job per (group, context)
            $table->unique(['sample_group_id', 'context_key'], 'stats_jobs_descriptive_unique');
            $table->unique(['comparison_group_id', 'context_key'], 'stats_jobs_inferential_unique');

            $table->index('sample_group_id');
            $table->index('comparison_group_id');
            $table->index('context_key');
        });

        // DESCRIPTIVE
        Schema::create('descriptive_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('statistics_job_id')->constrained()->cascadeOnDelete();

            // context/what stat is for
            $table->foreignId('sample_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('analysis_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('specimen_type_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('analyte_category_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('region_id')->nullable()->constrained('regions')->cascadeOnDelete();
            $table->foreignId('measurement_id')->constrained()->cascadeOnDelete();

            // stats
            $table->integer('n')->comment('Sample size');
            $table->decimal('mean', 12, 4)->nullable();
            $table->decimal('median', 12, 4)->nullable();
            $table->decimal('sd', 12, 4)->nullable()->comment('Standard deviation');
            $table->decimal('variance', 12, 4)->nullable();
            $table->decimal('sem', 12, 4)->nullable()->comment('Standard error of mean');
            $table->decimal('cv', 12, 4)->nullable()->comment('Coefficient of variation (%)');
            $table->decimal('min', 12, 4)->nullable();
            $table->decimal('max', 12, 4)->nullable();
            $table->decimal('range', 12, 4)->nullable();
            $table->decimal('q1', 12, 4)->nullable()->comment('First quartile');
            $table->decimal('q3', 12, 4)->nullable()->comment('Third quartile');
            $table->decimal('iqr', 12, 4)->nullable()->comment('Interquartile range');
            $table->json('outliers_2sd')->nullable()->comment('Outliers beyond 2 SD');
            $table->json('outliers_3sd')->nullable()->comment('Outliers beyond 3 SD');

            $table->timestamps();
        });

        // computed context key (handle issue with null != null)
        DB::statement("
            ALTER TABLE descriptive_statistics 
            ADD COLUMN context_key VARCHAR(255) GENERATED ALWAYS AS (
                CAST(analysis_type_id AS TEXT) || ':' ||
                COALESCE(CAST(specimen_type_id AS TEXT), 'NULL') || ':' ||
                COALESCE(CAST(analyte_category_id AS TEXT), 'NULL') || ':' ||
                COALESCE(CAST(region_id AS TEXT), 'NULL')
            ) STORED
        ");

        // unique constraints using context_key
        Schema::table('descriptive_statistics', function (Blueprint $table) {
            // one stat per (context + sample_group + measurement)
            $table->unique(['context_key', 'sample_group_id', 'measurement_id'], 'descriptive_stats_unique');

            $table->index(['context_key', 'sample_group_id'], 'descriptive_stats_context_idx');
            $table->index('statistics_job_id');
            $table->index('context_key');
        });

        // INFERENTIAL
        Schema::create('inferential_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('statistics_job_id')->constrained()->cascadeOnDelete();

            // context/what stat is for
            $table->foreignId('comparison_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('analysis_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('specimen_type_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('analyte_category_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('region_id')->nullable()->constrained('regions')->cascadeOnDelete();
            $table->foreignId('measurement_id')->constrained()->cascadeOnDelete();

            // test details/results
            $table->string('test_type', 50)->comment('t_test, anova, tukey, shapiro_wilk, levene, etc.');
            $table->decimal('p_value', 10, 6)->nullable();
            $table->decimal('test_statistic', 12, 4)->nullable()->comment('t, F, etc.');
            $table->json('metadata')->nullable()->comment('Test-specific data (group comparisons, degrees of freedom, etc.)');
            $table->json('groups_tested')->nullable()->comment('Sample group IDs that had data for this test');
            $table->enum('status', ['pending', 'completed', 'failed', 'insufficient_data'])->default('completed');

            $table->timestamps();
        });

        // computed context key (handle issue with null != null)
        DB::statement("
            ALTER TABLE inferential_statistics 
            ADD COLUMN context_key VARCHAR(255) GENERATED ALWAYS AS (
                CAST(analysis_type_id AS TEXT) || ':' ||
                COALESCE(CAST(specimen_type_id AS TEXT), 'NULL') || ':' ||
                COALESCE(CAST(analyte_category_id AS TEXT), 'NULL') || ':' ||
                COALESCE(CAST(region_id AS TEXT), 'NULL')
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

    public function down(): void
    {
        // just drop them - can't rollback anyway
        // DB::statement('DROP TABLE IF EXISTS inferential_statistics CASCADE');
        // DB::statement('DROP TABLE IF EXISTS descriptive_statistics CASCADE');
        // DB::statement('DROP TABLE IF EXISTS statistics_jobs CASCADE');

        // re-run original migrations to restore
    }
};
