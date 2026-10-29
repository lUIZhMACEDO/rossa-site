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
        Schema::create('statistics_jobs', function (Blueprint $table) {
            $table->id();
            // sample group id or comparison group id (descriptive or inferential)
            $table->foreignId('sample_group_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('comparison_group_id')->nullable()->constrained()->cascadeOnDelete();
            // context fields - unique key on analysis type, specimen type, analyte category, and region
            // only want to run one job per context
            $table->foreignId('analysis_type_id')->constrained()->cascadeOnDelete();
            $table->foreignId('specimen_type_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('analyte_category_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('region', 100)->nullable();
            // job status and metadata
            $table->enum('status', ['pending', 'processing', 'completed', 'failed'])->default('pending');
            $table->unsignedInteger('processing_version')->default(0)->comment('Incremented on each dispatch; jobs abort if version changes');
            // number of unique/shared measurements in context
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
        // ensure uniqueness works correctly even when context fields are NULL
        DB::statement("
            ALTER TABLE statistics_jobs 
            ADD COLUMN context_key VARCHAR(255) GENERATED ALWAYS AS (
                CAST(analysis_type_id AS TEXT) || ':' ||
                COALESCE(CAST(specimen_type_id AS TEXT), 'NULL') || ':' ||
                COALESCE(CAST(analyte_category_id AS TEXT), 'NULL') || ':' ||
                COALESCE(region, 'NULL')
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('statistics_jobs');
    }
};
