<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('news_posts', function (Blueprint $table) {
            $table->timestamp('published_at')->nullable()->after('excerpt');
        });

        // Backfill existing posts so nothing displays as undated.
        DB::table('news_posts')->update(['published_at' => DB::raw('created_at')]);

        // Postgres: set NOT NULL directly, no doctrine/dbal needed.
        DB::statement('ALTER TABLE news_posts ALTER COLUMN published_at SET NOT NULL');
    }

    public function down(): void
    {
        Schema::table('news_posts', function (Blueprint $table) {
            $table->dropColumn('published_at');
        });
    }
};
