<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('media_uploads', function (Blueprint $table) {
            $table->foreignId('sample_group_id')
                ->nullable()
                ->after('study_id')
                ->constrained('sample_groups')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('media_uploads', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sample_group_id');
        });
    }
};
