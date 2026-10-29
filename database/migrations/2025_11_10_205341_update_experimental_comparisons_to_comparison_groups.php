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
        Schema::dropIfExists('experimental_comparisons');
        Schema::create('comparison_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('sample_group_ids');
            $table->json('measurement_overlap_data')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comparison_groups');
        Schema::create('experimental_comparisons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_id')->constrained('studies')->onDelete('cascade');
            $table->foreignId('experimental_sample_group_id')->constrained('sample_groups')->onDelete('cascade');
            $table->foreignId('control_sample_group_id')->constrained('sample_groups')->onDelete('cascade');
            $table->timestamps();
        });
    }
};
