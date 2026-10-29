<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Mirrors StudyStatus conceptually but at the collection level. A collection
     * starts as a working draft while studies are being imported/reviewed, moves
     * to 'submitted' when the corresponding investigator submits the whole
     * collection (which submits every study within it), and to 'approved' once
     * ROSSA reviews and approves it — the point at which it becomes publicly
     * accessible.
     */
    public function up(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->string('status')->default('draft')->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('collections', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
