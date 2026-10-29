<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('studies', function (Blueprint $table) {
            $table->foreignId('collection_id')
                ->nullable()
                ->after('id')
                ->constrained('collections')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('studies', function (Blueprint $table) {
            $table->dropConstrainedForeignId('collection_id');
        });
    }
};
