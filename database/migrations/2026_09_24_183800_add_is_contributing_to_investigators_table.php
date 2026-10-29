<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('investigators', function (Blueprint $table) {
            $table->boolean('is_contributing')->default(false)->after('is_submitting');
        });

        DB::table('investigators')
            ->where(function ($query) {
                $query->where('is_submitting', false)->orWhereNull('is_submitting');
            })
            ->where(function ($query) {
                $query->where('corresponding', false)->orWhereNull('corresponding');
            })
            ->update(['is_contributing' => true]);
    }

    public function down(): void
    {
        Schema::table('investigators', function (Blueprint $table) {
            $table->dropColumn('is_contributing');
        });
    }
};
