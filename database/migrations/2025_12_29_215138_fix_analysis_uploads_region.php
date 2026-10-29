<?php

use App\Models\Region;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // migrate existing data
        $regions = Region::pluck('id', 'code');

        DB::table('analysis_uploads')->whereNotNull('region')->get()->each(function ($row) use ($regions) {
            if (isset($regions[$row->region])) {
                DB::table('analysis_uploads')->where('id', $row->id)->update(['region_id' => $regions[$row->region]]);
            }
        });

        // drop old region column
        Schema::table('analysis_uploads', function (Blueprint $table) {
            $table->dropColumn('region');
        });
    }

    public function down(): void
    {
        // add region column back
        Schema::table('analysis_uploads', function (Blueprint $table) {
            $table->string('region', 100)->nullable()->after('analyte_category_id');
        });

        // migrate data back
        $regions = Region::pluck('code', 'id');

        DB::table('analysis_uploads')->whereNotNull('region_id')->get()->each(function ($row) use ($regions) {
            if (isset($regions[$row->region_id])) {
                DB::table('analysis_uploads')->where('id', $row->id)->update(['region' => $regions[$row->region_id]]);
            }
        });

    }
};
