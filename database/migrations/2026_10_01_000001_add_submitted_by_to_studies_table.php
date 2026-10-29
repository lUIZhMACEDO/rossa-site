<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('studies', function (Blueprint $table) {
            $table->foreignId('submitted_by_user_id')
                ->nullable()
                ->after('submitted_at')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('studies', function (Blueprint $table) {
            $table->dropConstrainedForeignId('submitted_by_user_id');
        });
    }
};
