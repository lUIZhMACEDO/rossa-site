<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drug_administration_plans', function (Blueprint $table) {
            $table->boolean('is_vehicle_control')->default(false)->after('description');
            $table->foreignId('control_for_plan_id')->nullable()->after('is_vehicle_control')
                ->constrained('drug_administration_plans')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('drug_administration_plans', function (Blueprint $table) {
            $table->dropForeign(['control_for_plan_id']);
            $table->dropColumn(['is_vehicle_control', 'control_for_plan_id']);
        });
    }
};
