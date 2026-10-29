<?php

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
        Schema::table('media_uploads', function (Blueprint $table) {
            $table->unsignedBigInteger('size')->nullable()->change();
            $table->string('status')->default('pending')->after('size');
            $table->uuid('upload_uuid')->nullable()->after('status');
            $table->unsignedBigInteger('bytes_received')->default(0)->after('upload_uuid');
            $table->unsignedInteger('chunk_size')->nullable()->after('bytes_received');
            $table->timestamps();

            $table->unique('upload_uuid');
            $table->index(['study_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('media_uploads', function (Blueprint $table) {
            $table->dropUnique(['upload_uuid']);
            $table->dropIndex(['study_id', 'status']);
            $table->dropColumn([
                'status',
                'upload_uuid',
                'bytes_received',
                'chunk_size',
                'created_at',
                'updated_at',
            ]);
            $table->integer('size')->nullable()->change();
        });
    }
};
