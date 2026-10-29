<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collections', function (Blueprint $table) {
            $table->id();
            $table->string('identifier')->nullable(); // ROSSA collection ID, mirrors studies.identifier
            $table->string('title');
            $table->text('summary')->nullable();
            $table->text('funding_sources')->nullable();
            $table->text('conflicts')->nullable();
            $table->date('completion_date')->nullable();
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collections');
    }
};
