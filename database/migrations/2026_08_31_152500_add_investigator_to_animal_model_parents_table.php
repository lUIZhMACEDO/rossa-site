<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('animal_model_parents', function (Blueprint $table) {
            $table->string('investigator')->nullable()->after('animal_line_id');
        });
    }

    public function down(): void
    {
        Schema::table('animal_model_parents', function (Blueprint $table) {
            $table->dropColumn('investigator');
        });
    }
};
