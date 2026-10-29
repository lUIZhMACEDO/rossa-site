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
        Schema::table('molecules', function (Blueprint $table) {
            // chembl_id will be null on custom molecules
            $table->string('chembl_id')->nullable()->change();
            $table->boolean('is_custom')->default(false)->after('chembl_id');
            // the user who created this custom molecule
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null')->after('is_custom');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('molecules', function (Blueprint $table) {
            $table->string('chembl_id')->nullable(false)->change();
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
            $table->dropColumn('is_custom');
        });
    }
};
