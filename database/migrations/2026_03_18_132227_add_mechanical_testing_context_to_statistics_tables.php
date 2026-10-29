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
        // add columns to jobs and stats
        Schema::table('statistics_jobs', function (Blueprint $table) {
            $table->string('test_method', 50)->nullable();
            $table->string('bone_tested', 50)->nullable();
        });

        Schema::table('descriptive_statistics', function (Blueprint $table) {
            $table->string('test_method', 50)->nullable();
            $table->string('bone_tested', 50)->nullable();
        });

        Schema::table('inferential_statistics', function (Blueprint $table) {
            $table->string('test_method', 50)->nullable();
            $table->string('bone_tested', 50)->nullable();
        });

        // drop unique constraints that depend on context_key
        Schema::table('statistics_jobs', function (Blueprint $table) {
            $table->dropUnique('stats_jobs_descriptive_unique');
            $table->dropUnique('stats_jobs_inferential_unique');
            $table->dropIndex(['context_key']);
        });

        // drop and recreate context_key for jobs
        DB::statement('ALTER TABLE statistics_jobs DROP COLUMN context_key');
        DB::statement("
            ALTER TABLE statistics_jobs 
            ADD COLUMN context_key VARCHAR(255) GENERATED ALWAYS AS (
                CAST(analysis_type_id AS TEXT) || ':' ||
                COALESCE(CAST(specimen_type_id AS TEXT), 'NULL') || ':' ||
                COALESCE(CAST(analyte_category_id AS TEXT), 'NULL') || ':' ||
                COALESCE(CAST(region_id AS TEXT), 'NULL') || ':' ||
                COALESCE(test_method, 'NULL') || ':' ||
                COALESCE(bone_tested, 'NULL')
            ) STORED
        ");

        // recreate unique constraints and indexes for stats jobs
        Schema::table('statistics_jobs', function (Blueprint $table) {
            $table->unique(['sample_group_id', 'context_key'], 'stats_jobs_descriptive_unique');
            $table->unique(['comparison_group_id', 'context_key'], 'stats_jobs_inferential_unique');
            $table->index('context_key');
        });

        // drop unique constraint and indexes that depend on context_key for descriptive stats
        Schema::table('descriptive_statistics', function (Blueprint $table) {
            $table->dropUnique('descriptive_stats_unique');
            $table->dropIndex('descriptive_stats_context_idx');
            $table->dropIndex(['context_key']);
        });

        // drop and recreate context_key for descriptive stats
        DB::statement('ALTER TABLE descriptive_statistics DROP COLUMN context_key');
        DB::statement("
            ALTER TABLE descriptive_statistics 
            ADD COLUMN context_key VARCHAR(255) GENERATED ALWAYS AS (
                CAST(analysis_type_id AS TEXT) || ':' ||
                COALESCE(CAST(specimen_type_id AS TEXT), 'NULL') || ':' ||
                COALESCE(CAST(analyte_category_id AS TEXT), 'NULL') || ':' ||
                COALESCE(CAST(region_id AS TEXT), 'NULL') || ':' ||
                COALESCE(test_method, 'NULL') || ':' ||
                COALESCE(bone_tested, 'NULL')
            ) STORED
        ");

        // recreate unique constraint and indexes for descriptive stats
        Schema::table('descriptive_statistics', function (Blueprint $table) {
            $table->unique(['context_key', 'sample_group_id', 'measurement_id'], 'descriptive_stats_unique');
            $table->index(['context_key', 'sample_group_id'], 'descriptive_stats_context_idx');
            $table->index('context_key');
        });

        // drop unique constraint and indexes that depend on context_key for inferential stats
        Schema::table('inferential_statistics', function (Blueprint $table) {
            $table->dropUnique('inferential_stats_unique');
            $table->dropIndex('inferential_stats_context_idx');
            $table->dropIndex(['context_key']);
        });

        // drop and recreate context_key for inferential stats
        DB::statement('ALTER TABLE inferential_statistics DROP COLUMN context_key');
        DB::statement("
            ALTER TABLE inferential_statistics 
            ADD COLUMN context_key VARCHAR(255) GENERATED ALWAYS AS (
                CAST(analysis_type_id AS TEXT) || ':' ||
                COALESCE(CAST(specimen_type_id AS TEXT), 'NULL') || ':' ||
                COALESCE(CAST(analyte_category_id AS TEXT), 'NULL') || ':' ||
                COALESCE(CAST(region_id AS TEXT), 'NULL') || ':' ||
                COALESCE(test_method, 'NULL') || ':' ||
                COALESCE(bone_tested, 'NULL')
            ) STORED
        ");

        // recreate unique constraint and indexes for inferential stats
        Schema::table('inferential_statistics', function (Blueprint $table) {
            $table->unique(['context_key', 'comparison_group_id', 'measurement_id', 'test_type'], 'inferential_stats_unique');
            $table->index(['context_key', 'comparison_group_id'], 'inferential_stats_context_idx');
            $table->index('context_key');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // drop unique constraints and indexes that depend on context_key for stats jobs
        Schema::table('statistics_jobs', function (Blueprint $table) {
            $table->dropUnique('stats_jobs_descriptive_unique');
            $table->dropUnique('stats_jobs_inferential_unique');
            $table->dropIndex(['context_key']);
        });

        // recreate original context_key for stats jobs
        DB::statement('ALTER TABLE statistics_jobs DROP COLUMN context_key');
        DB::statement("
            ALTER TABLE statistics_jobs 
            ADD COLUMN context_key VARCHAR(255) GENERATED ALWAYS AS (
                CAST(analysis_type_id AS TEXT) || ':' ||
                COALESCE(CAST(specimen_type_id AS TEXT), 'NULL') || ':' ||
                COALESCE(CAST(analyte_category_id AS TEXT), 'NULL') || ':' ||
                COALESCE(CAST(region_id AS TEXT), 'NULL')
            ) STORED
        ");

        // recreate unique constraints and indexes for stats jobs
        Schema::table('statistics_jobs', function (Blueprint $table) {
            $table->unique(['sample_group_id', 'context_key'], 'stats_jobs_descriptive_unique');
            $table->unique(['comparison_group_id', 'context_key'], 'stats_jobs_inferential_unique');
            $table->index('context_key');
        });

        // drop unique constraint and indexes that depend on context_key for descriptive stats
        Schema::table('descriptive_statistics', function (Blueprint $table) {
            $table->dropUnique('descriptive_stats_unique');
            $table->dropIndex('descriptive_stats_context_idx');
            $table->dropIndex(['context_key']);
        });

        // recreate original context_key for descriptive stats
        DB::statement('ALTER TABLE descriptive_statistics DROP COLUMN context_key');
        DB::statement("
            ALTER TABLE descriptive_statistics 
            ADD COLUMN context_key VARCHAR(255) GENERATED ALWAYS AS (
                CAST(analysis_type_id AS TEXT) || ':' ||
                COALESCE(CAST(specimen_type_id AS TEXT), 'NULL') || ':' ||
                COALESCE(CAST(analyte_category_id AS TEXT), 'NULL') || ':' ||
                COALESCE(CAST(region_id AS TEXT), 'NULL')
            ) STORED
        ");

        // recreate unique constraint and indexes for descriptive stats
        Schema::table('descriptive_statistics', function (Blueprint $table) {
            $table->unique(['context_key', 'sample_group_id', 'measurement_id'], 'descriptive_stats_unique');
            $table->index(['context_key', 'sample_group_id'], 'descriptive_stats_context_idx');
            $table->index('context_key');
        });

        // drop unique constraint and indexes that depend on context_key for inferential stats
        Schema::table('inferential_statistics', function (Blueprint $table) {
            $table->dropUnique('inferential_stats_unique');
            $table->dropIndex('inferential_stats_context_idx');
            $table->dropIndex(['context_key']);
        });

        // recreate original context_key for inferential stats
        DB::statement('ALTER TABLE inferential_statistics DROP COLUMN context_key');
        DB::statement("
            ALTER TABLE inferential_statistics 
            ADD COLUMN context_key VARCHAR(255) GENERATED ALWAYS AS (
                CAST(analysis_type_id AS TEXT) || ':' ||
                COALESCE(CAST(specimen_type_id AS TEXT), 'NULL') || ':' ||
                COALESCE(CAST(analyte_category_id AS TEXT), 'NULL') || ':' ||
                COALESCE(CAST(region_id AS TEXT), 'NULL')
            ) STORED
        ");

        // recreate unique constraint and indexes for inferential stats
        Schema::table('inferential_statistics', function (Blueprint $table) {
            $table->unique(['context_key', 'comparison_group_id', 'measurement_id', 'test_type'], 'inferential_stats_unique');
            $table->index(['context_key', 'comparison_group_id'], 'inferential_stats_context_idx');
            $table->index('context_key');
        });

        // drop the new columns from stats jobs
        Schema::table('statistics_jobs', function (Blueprint $table) {
            $table->dropColumn(['test_method', 'bone_tested']);
        });

        // drop the new columns from descriptive stats
        Schema::table('descriptive_statistics', function (Blueprint $table) {
            $table->dropColumn(['test_method', 'bone_tested']);
        });

        // drop the new columns from inferential stats
        Schema::table('inferential_statistics', function (Blueprint $table) {
            $table->dropColumn(['test_method', 'bone_tested']);
        });
    }
};
