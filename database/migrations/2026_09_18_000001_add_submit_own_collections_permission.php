<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::table('permissions')->where('name', 'submit-own-collections')->exists()) {
            return;
        }

        DB::table('permissions')->insert([
            'name' => 'submit-own-collections',
            'description' => 'Submit assigned collections for ROSSA review where you are the corresponding investigator',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('permissions')->where('name', 'submit-own-collections')->delete();
    }
};
