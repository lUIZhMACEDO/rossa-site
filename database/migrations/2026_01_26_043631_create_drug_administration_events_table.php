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
        Schema::create('drug_administration_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('drug_administration_plans')->onDelete('cascade');

            // Drug identification - either ChEMBL or manual entry
            $table->foreignId('molecule_id')->nullable()->constrained('molecules')->onDelete('set null');

            // Drug source information
            $table->enum('drug_source_type', ['commercial', 'custom', 'academic', 'gift', 'other'])->nullable();
            $table->string('drug_source_details')->nullable(); // Company name, catalog number, etc.

            // Dosing information
            $table->decimal('dose', 10, 4);
            $table->string('dose_unit');

            // Administration route
            $table->string('route');
            $table->string('route_other')->nullable();

            // Timing information
            $table->integer('start_day');
            $table->integer('end_day')->nullable(); // Null for single dose

            // Frequency
            $table->decimal('frequency', 10, 2);
            $table->string('frequency_unit');

            // Event ordering for display
            $table->integer('event_order')->default(0);

            $table->timestamps();

            $table->index('plan_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drug_administration_events');
    }
};
