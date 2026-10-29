<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Prior COMPLETE was 4; SCAN_DATASETS now occupies 4 and COMPLETE is 5.
        DB::table('analysis_uploads')
            ->where('current_step', 4)
            ->update(['current_step' => 5]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('analysis_uploads')
            ->where('current_step', 5)
            ->update(['current_step' => 4]);
    }
};
