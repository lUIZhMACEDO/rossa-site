<?php

use App\Models\AnalysisUpload;
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
        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('label');
            $table->timestamps();
        });

        Schema::create('measurement_region', function (Blueprint $table) {
            $table->id();
            $table->foreignId('measurement_id')->constrained()->onDelete('cascade');
            $table->foreignId('region_id')->constrained('regions')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['measurement_id', 'region_id']);
        });

        Schema::table('analysis_uploads', function (Blueprint $table) {
            $table->foreignId('region_id')->nullable()->constrained('regions')->onDelete('set null');
        });

        // Seed and sync existing uploads.
        // only run first time... - assume others fresh
        // further modifications in later migrations
        // Artisan::call('db:seed',[
        //     '--class' => 'Database\\Seeders\\Measurements\\MeasurementSeeder',
        // ]);
        // AnalysisUpload::whereNull('region_id')->whereNotNull('region')->get()->each(function($upload) {
        //     $region = App\Models\Region::firstWhere('code', $upload->region);
        //     if ( $region ) {
        //         $upload->region_id = $region->id;
        //         $upload->save();
        //     }
        // });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('analysis_uploads', function (Blueprint $table) {
            $table->dropForeign(['region_id']);
            $table->dropColumn('region_id');
        });
        Schema::dropIfExists('measurement_region');
        Schema::dropIfExists('regions');
    }
};
