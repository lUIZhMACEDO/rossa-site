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
        Schema::create('office_hours_sessions', function (Blueprint $table) {
            $table->id();
            $table->date('session_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('teams_link');
            $table->string('status')->default('active'); // active, cancelled
            $table->string('assigned_to')->nullable(); // name of backup staff if not Peter
            $table->text('notes')->nullable(); // internal notes visible only to admin
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_hours_sessions');
    }
};
