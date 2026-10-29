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
        Schema::table('studies', function (Blueprint $table) {
            $table->boolean('skips_image_data')->default(false);
        });

        // Grandfather existing studies that never uploaded images.
        DB::table('studies')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('media_uploads')
                    ->whereColumn('media_uploads.study_id', 'studies.id');
            })
            ->update(['skips_image_data' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('studies', function (Blueprint $table) {
            $table->dropColumn('skips_image_data');
        });
    }
};
