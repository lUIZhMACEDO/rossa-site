<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('animal_models', function (Blueprint $table) {
            $table->text('animal_model_description')->nullable()->after('animal_model_name');
        });
    }

    public function down(): void
    {
        Schema::table('animal_models', function (Blueprint $table) {
            $table->dropColumn('animal_model_description');
        });
    }
};
