<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drug_administration_events', function (Blueprint $table) {
            $table->string('frequency_unit_other')->nullable()->after('frequency_unit');
        });
    }

    public function down(): void
    {
        Schema::table('drug_administration_events', function (Blueprint $table) {
            $table->dropColumn('frequency_unit_other');
        });
    }
};
