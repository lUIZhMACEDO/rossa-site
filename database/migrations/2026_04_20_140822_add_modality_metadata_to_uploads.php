<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('analysis_uploads', function (Blueprint $table) {
            $table->boolean('body_weight_included')->nullable()->after('bone_tested');
        });

        Schema::create('micro_ct_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analysis_upload_id')->unique()->constrained()->cascadeOnDelete();
            $table->decimal('voxel_size', 8, 4)->nullable();
            $table->string('scan_mode')->nullable();
            $table->string('scanner')->nullable();
            $table->decimal('xray_tube_potential', 8, 2)->nullable();
            $table->decimal('integration_time', 8, 2)->nullable();
            $table->string('analytics_software')->nullable();
            $table->timestamps();
        });

        Schema::create('dexa_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analysis_upload_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('dexa_instrument')->nullable();
            $table->timestamps();
        });

        Schema::create('bone_histo_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analysis_upload_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('measurement_software')->nullable();
            $table->text('histological_approach')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bone_histo_details');
        Schema::dropIfExists('dexa_details');
        Schema::dropIfExists('micro_ct_details');

        Schema::table('analysis_uploads', function (Blueprint $table) {
            $table->dropColumn('body_weight_included');
        });
    }
};
