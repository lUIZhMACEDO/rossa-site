<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Postgres: ALTER COLUMN TYPE works directly, no doctrine/dbal needed.
        DB::statement('ALTER TABLE news_posts ALTER COLUMN excerpt TYPE character varying(500)');
    }

    public function down(): void
    {
        // Reversing this truncates any excerpt longer than 160 chars back down.
        // If you've already saved longer excerpts, decide whether you actually
        // want this rollback path before running it.
        DB::statement('ALTER TABLE news_posts ALTER COLUMN excerpt TYPE character varying(160)');
    }
};
