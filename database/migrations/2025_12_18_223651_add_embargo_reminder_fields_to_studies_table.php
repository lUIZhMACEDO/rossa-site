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
        Schema::table('studies', function (Blueprint $table) {
            $table->date('embargo_start_date')->nullable()->after('embargo_months');
            $table->date('embargo_reminder_date')->nullable()->after('embargo_start_date');
            $table->timestamp('embargo_reminder_sent_at')->nullable()->after('embargo_reminder_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('studies', function (Blueprint $table) {
            $table->dropColumn(['embargo_start_date', 'embargo_reminder_date', 'embargo_reminder_sent_at']);
        });
    }
};
