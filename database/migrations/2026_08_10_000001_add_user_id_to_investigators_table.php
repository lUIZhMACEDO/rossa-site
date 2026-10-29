<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('investigators', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->after('study_id')
                ->constrained('users')
                ->nullOnDelete();
        });

        DB::statement(<<<'SQL'
            UPDATE investigators
            SET user_id = users.id
            FROM users
            WHERE LOWER(investigators.email) = LOWER(users.email)
        SQL);
    }

    public function down(): void
    {
        Schema::table('investigators', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
