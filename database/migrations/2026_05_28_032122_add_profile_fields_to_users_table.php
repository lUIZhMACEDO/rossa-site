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
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('name');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('department')->nullable()->after('last_name');
            $table->string('organization')->nullable()->after('department');
            $table->foreignId('country_id')->nullable()->after('organization')->constrained()->nullOnDelete();
            $table->string('state')->nullable()->after('country_id');
            $table->string('institution_type')->nullable()->after('state');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('country_id');
            $table->dropColumn([
                'first_name',
                'last_name',
                'department',
                'organization',
                'state',
                'institution_type',
            ]);
        });
    }
};
