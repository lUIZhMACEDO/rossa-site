<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('media_upload_sample');

        Schema::table('media_uploads', function (Blueprint $table) {
            if (Schema::hasColumn('media_uploads', 'analysis_upload_id')) {
                $table->dropConstrainedForeignId('analysis_upload_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('media_uploads', function (Blueprint $table) {
            $table->foreignId('analysis_upload_id')
                ->nullable()
                ->after('sample_group_id')
                ->constrained()
                ->nullOnDelete();
        });

        Schema::create('media_upload_sample', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_upload_id')->constrained('media_uploads')->cascadeOnDelete();
            $table->foreignId('sample_id')->constrained('samples')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['media_upload_id', 'sample_id']);
        });
    }
};
