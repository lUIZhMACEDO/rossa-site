<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('core_facilities', function (Blueprint $table) {
            $table->string('secondary_contact_name')->nullable()->after('contact_email');
            $table->string('secondary_contact_email')->nullable()->after('secondary_contact_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('core_facilities', function (Blueprint $table) {
            $table->dropColumn(['secondary_contact_name', 'secondary_contact_email']);
        });
    }
};
