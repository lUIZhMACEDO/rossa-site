<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('other_intervention_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('other_intervention_plans')->cascadeOnDelete();

            // Category/Specific Type follow the same lookup-table-plus-escape-hatch
            // pattern as drug_administration_events.vehicle_id / vehicle_other.
            $table->foreignId('intervention_category_id')->constrained('intervention_categories');
            $table->foreignId('intervention_specific_type_id')
                ->nullable()
                ->constrained('intervention_specific_types')
                ->nullOnDelete();
            $table->string('category_other')->nullable();
            $table->string('specific_type_other')->nullable();

            $table->string('name');
            $table->text('method_description');
            $table->string('source_details')->nullable();

            $table->decimal('frequency', 8, 2);
            $table->string('frequency_unit');
            $table->string('frequency_unit_other')->nullable();

            $table->integer('start_day');
            $table->integer('end_day')->nullable();

            // Mirrors drug_administration_events.event_order for multi-event plans
            // (e.g., a diet plan later paired with a supplemental treatment event).
            $table->integer('event_order')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('other_intervention_events');
    }
};
