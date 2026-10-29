<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('genetic_categories')
            ->where('name', 'Global Knockout - Double Knockout')
            ->update(['name' => 'Global - Double Knockout']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
