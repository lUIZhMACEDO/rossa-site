<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('measurements', function (Blueprint $table) {
            $table->dropUnique(['name', 'analysis_type_id']);
        });

        // unique index for non-NULL specimen types (clinical chemistry)
        DB::statement('
            CREATE UNIQUE INDEX measurements_with_specimen 
            ON measurements (name, analysis_type_id, specimen_type_id)
            WHERE specimen_type_id IS NOT NULL
        ');

        // create unique index for NULL specimen types (all others)
        DB::statement('
            CREATE UNIQUE INDEX measurements_without_specimen 
            ON measurements (name, analysis_type_id)
            WHERE specimen_type_id IS NULL
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS measurements_with_specimen');
        DB::statement('DROP INDEX IF EXISTS measurements_without_specimen');

        // restore the old constraint
        Schema::table('measurements', function (Blueprint $table) {
            $table->unique(['name', 'analysis_type_id']);
        });
    }
};
