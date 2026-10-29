<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_upload_sample', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_upload_id')->constrained('media_uploads')->cascadeOnDelete();
            $table->foreignId('sample_id')->constrained('samples')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['media_upload_id', 'sample_id']);
        });

        // Preserve any existing single-sample assignments.
        $rows = DB::table('media_uploads')
            ->whereNotNull('sample_id')
            ->select('id', 'sample_id', 'created_at', 'updated_at')
            ->get();

        $now = now();
        foreach ($rows as $row) {
            DB::table('media_upload_sample')->insert([
                'media_upload_id' => $row->id,
                'sample_id' => $row->sample_id,
                'created_at' => $row->created_at ?? $now,
                'updated_at' => $row->updated_at ?? $now,
            ]);
        }

        Schema::table('media_uploads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sample_id');
        });
    }

    public function down(): void
    {
        Schema::table('media_uploads', function (Blueprint $table) {
            $table->foreignId('sample_id')->nullable()->after('analysis_upload_id')->constrained()->nullOnDelete();
        });

        $firstAssignments = DB::table('media_upload_sample')
            ->select('media_upload_id', DB::raw('MIN(sample_id) as sample_id'))
            ->groupBy('media_upload_id')
            ->get();

        foreach ($firstAssignments as $row) {
            DB::table('media_uploads')
                ->where('id', $row->media_upload_id)
                ->update(['sample_id' => $row->sample_id]);
        }

        Schema::dropIfExists('media_upload_sample');
    }
};
