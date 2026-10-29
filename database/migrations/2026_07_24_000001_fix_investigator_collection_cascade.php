<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * investigators.collection_id was created with ON DELETE SET NULL. Deleting a
     * collection therefore tried to null the column, leaving the row with neither
     * a study_id nor a collection_id, which violates the
     * investigator_belongs_to_one_parent check constraint.
     *
     * A collection-level investigator has no meaning without its collection, so
     * the correct behavior is to delete it alongside the parent.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE investigators DROP CONSTRAINT IF EXISTS investigators_collection_id_foreign');

        DB::statement('
            ALTER TABLE investigators
            ADD CONSTRAINT investigators_collection_id_foreign
            FOREIGN KEY (collection_id) REFERENCES collections(id)
            ON DELETE CASCADE
        ');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE investigators DROP CONSTRAINT IF EXISTS investigators_collection_id_foreign');

        DB::statement('
            ALTER TABLE investigators
            ADD CONSTRAINT investigators_collection_id_foreign
            FOREIGN KEY (collection_id) REFERENCES collections(id)
            ON DELETE SET NULL
        ');
    }
};
