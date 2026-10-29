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
            $table->foreignId('collection_id')
                ->nullable()
                ->after('study_id')
                ->constrained('collections')
                ->nullOnDelete();
        });

        // Make study_id nullable. Using raw SQL rather than ->change() to avoid
        // an unnecessary doctrine/dbal dependency for a single-column tweak.
        DB::statement('ALTER TABLE investigators ALTER COLUMN study_id DROP NOT NULL');

        // Enforce that every investigator belongs to exactly one parent:
        // a study OR a collection, never both, never neither.
        DB::statement(<<<'SQL'
            ALTER TABLE investigators
            ADD CONSTRAINT investigator_belongs_to_one_parent
            CHECK (
                (study_id IS NOT NULL AND collection_id IS NULL) OR
                (study_id IS NULL AND collection_id IS NOT NULL)
            )
        SQL);
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE investigators DROP CONSTRAINT IF EXISTS investigator_belongs_to_one_parent');

        Schema::table('investigators', function (Blueprint $table) {
            $table->dropConstrainedForeignId('collection_id');
        });

        // Restore NOT NULL. Note: this will fail if any collection-level
        // investigator rows (study_id null) exist at rollback time — those
        // rows would need to be reassigned or removed first.
        DB::statement('ALTER TABLE investigators ALTER COLUMN study_id SET NOT NULL');
    }
};
