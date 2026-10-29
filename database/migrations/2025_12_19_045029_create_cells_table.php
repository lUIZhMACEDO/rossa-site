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
        Schema::create('cells', function (Blueprint $table) {
            $table->id();
            $table->string('cell_name');
            $table->string('display_name');
            $table->string('cl_term_name')->nullable();
            $table->string('cl_id')->nullable();
            $table->string('ontology_status')->nullable();
            $table->text('tooltip')->nullable();
            $table->string('group')->nullable();
            $table->timestamps();

            // Unique compound index for upsert
            $table->unique(['cl_id', 'cell_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cells');
    }
};
