<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Collection-level acquisition metadata defaults.
     *
     * Per-upload metadata already lives on MicroCtDetail / DexaDetail /
     * BoneHistoDetail, keyed to a specific analysis_upload. That works for the
     * single-study submission flow, but it means the settings have nowhere to
     * live until an upload row exists — so a corresponding investigator can't
     * record "this screen was scanned on a Scanco uCT 40 at 16um" until
     * someone has already run an import.
     *
     * These columns hold the collection's standard settings as a fact about
     * the collection itself, the same way funding_sources or completion_date
     * do. The measurement importer pre-fills from them (still editable
     * per-batch, since a given batch may legitimately differ), and each upload
     * continues to get its own real detail row at import time.
     */
    public function up(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            // Shared across modalities that ask for it
            $table->boolean('default_body_weight_included')->nullable()->after('status');

            // microCT
            $table->decimal('default_voxel_size', 8, 4)->nullable()->after('default_body_weight_included');
            $table->string('default_scan_mode')->nullable()->after('default_voxel_size');
            $table->string('default_scanner')->nullable()->after('default_scan_mode');
            $table->decimal('default_xray_tube_potential', 8, 2)->nullable()->after('default_scanner');
            $table->decimal('default_integration_time', 8, 2)->nullable()->after('default_xray_tube_potential');
            $table->string('default_analytics_software')->nullable()->after('default_integration_time');

            // DEXA
            $table->string('default_dexa_instrument')->nullable()->after('default_analytics_software');

            // Bone histomorphometry
            $table->string('default_measurement_software')->nullable()->after('default_dexa_instrument');
            $table->text('default_histological_approach')->nullable()->after('default_measurement_software');
        });
    }

    public function down(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->dropColumn([
                'default_body_weight_included',
                'default_voxel_size',
                'default_scan_mode',
                'default_scanner',
                'default_xray_tube_potential',
                'default_integration_time',
                'default_analytics_software',
                'default_dexa_instrument',
                'default_measurement_software',
                'default_histological_approach',
            ]);
        });
    }
};
