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
        Schema::table('sample_groups', function (Blueprint $table) {
            $table->dropForeign(['drug_treatment_id']);
            $table->dropColumn(['drug_treatment_id']);
        });

        Schema::dropIfExists('drug_treatment_lines');
        Schema::dropIfExists('drug_treatments');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
