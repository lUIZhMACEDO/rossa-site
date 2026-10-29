<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('drug_administration_events', function (Blueprint $table) {
            $table->foreignId('vehicle_id')->nullable()->after('drug_source_details')
                ->constrained('vehicles')->nullOnDelete();
            $table->string('vehicle_other')->nullable()->after('vehicle_id');
            $table->decimal('administration_volume', 8, 3)->nullable()->after('vehicle_other');
            $table->string('administration_volume_unit')->nullable()->default('ml/kg')->after('administration_volume');
            $table->boolean('is_vehicle')->default(false)->after('administration_volume_unit');
        });
    }

    public function down(): void
    {
        Schema::table('drug_administration_events', function (Blueprint $table) {
            $table->dropForeign(['vehicle_id']);
            $table->dropColumn(['vehicle_id', 'vehicle_other', 'administration_volume', 'administration_volume_unit', 'is_vehicle']);
        });
    }
};
