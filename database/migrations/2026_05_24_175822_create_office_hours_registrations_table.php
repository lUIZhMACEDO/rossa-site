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
        Schema::create('office_hours_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('office_hours_sessions')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('notes')->nullable(); // what the user needs help with
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->unique(['session_id', 'user_id']); // prevent duplicate registrations
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_hours_registrations');
    }
};
