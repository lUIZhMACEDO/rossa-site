<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('studies', function (Blueprint $table) {
            $table->boolean('performs_other_intervention')
                ->default(false)
                ->after('performs_gonadectomy');
        });
    }

    public function down(): void
    {
        Schema::table('studies', function (Blueprint $table) {
            $table->dropColumn('performs_other_intervention');
        });
    }
};
