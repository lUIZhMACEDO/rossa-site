<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('other_intervention_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_id')->constrained('studies')->cascadeOnDelete();
            $table->string('plan_name');
            $table->text('description')->nullable();
            $table->integer('animal_age_at_day_zero');

            // Mirrors drug_administration_plans.is_vehicle_control / control_for_plan_id exactly,
            // so a plan can be flagged as the control arm for another plan in the same study.
            $table->boolean('is_control_plan')->default(false);
            $table->foreignId('control_for_plan_id')
                ->nullable()
                ->constrained('other_intervention_plans')
                ->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('other_intervention_plans');
    }
};
