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
        Schema::create('drug_administration_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_id')->constrained()->onDelete('cascade');
            $table->string('plan_name');
            $table->text('description')->nullable();
            $table->integer('animal_age_at_day_zero'); // Age in weeks when treatment starts
            $table->timestamps();

            $table->index('study_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drug_administration_plans');
    }
};
