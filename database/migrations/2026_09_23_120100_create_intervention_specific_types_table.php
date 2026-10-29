<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intervention_specific_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('intervention_category_id')
                ->constrained('intervention_categories')
                ->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            // A category's "Other" row is flagged here rather than matched by
            // name string, so the app can find it reliably regardless of label wording.
            $table->boolean('is_other')->default(false);
            $table->timestamps();

            $table->unique(['intervention_category_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intervention_specific_types');
    }
};
