<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Allow invitations to people who do not have a ROSSA account yet.
     * Such invitations store only the email; user_id is filled in when the
     * person registers with that address and accepts.
     */
    public function up(): void
    {
        // Raw SQL rather than ->change() to avoid a doctrine/dbal dependency,
        // matching the approach used in the investigators migrations.
        DB::statement('ALTER TABLE study_contributor_invitations ALTER COLUMN user_id DROP NOT NULL');

        Schema::table('study_contributor_invitations', function (Blueprint $table) {
            $table->index(['study_id', 'email', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('study_contributor_invitations', function (Blueprint $table) {
            $table->dropIndex(['study_id', 'email', 'status']);
        });

        // Email-only invitations cannot satisfy NOT NULL, so remove them first.
        DB::table('study_contributor_invitations')->whereNull('user_id')->delete();

        DB::statement('ALTER TABLE study_contributor_invitations ALTER COLUMN user_id SET NOT NULL');
    }
};
