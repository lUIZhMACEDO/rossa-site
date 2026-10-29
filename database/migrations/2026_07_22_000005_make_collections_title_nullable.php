<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE collections ALTER COLUMN title DROP NOT NULL');
    }

    public function down(): void
    {
        // Restore NOT NULL. Will fail if any titleless collections exist at
        // rollback time — those would need a title or removal first.
        DB::statement('ALTER TABLE collections ALTER COLUMN title SET NOT NULL');
    }
};
