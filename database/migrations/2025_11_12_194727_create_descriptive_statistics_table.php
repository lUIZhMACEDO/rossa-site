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
        Schema::create('descriptive_statistics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('statistics_job_id')->constrained()->cascadeOnDelete();

            // context/what stat is for
            $table->foreignId('sample_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('analysis_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('specimen_type_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('analyte_category_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('region', 100)->nullable();
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
                COALESCE(region, 'NULL')
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('descriptive_statistics');
    }
};
