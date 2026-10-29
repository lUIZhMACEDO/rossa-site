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
        Schema::table('mechanical_testing_details', function (Blueprint $table) {
            // Drop old columns
            $table->dropColumn('support_point_1');
            $table->dropColumn('support_point_2');
            $table->dropColumn('loading_point_1');
            $table->dropColumn('loading_point_2');

            $table->string('bone_tested')->nullable();

            // 3pt bending
            $table->decimal('length_3pt', 8, 2)->nullable();
            // 4pt bending
            $table->decimal('length_outer_4pt', 8, 2)->nullable();
            $table->decimal('length_inner_4pt', 8, 2)->nullable();
            // compression
            $table->decimal('compression_height', 8, 2)->nullable();

            $table->boolean('fixed')->nullable();

            $table->dropColumn('loading_rate');
            $table->dropColumn('loading_rate_unit');

            $table->renameColumn('length_3pt', 'support_span_3pt');
            $table->renameColumn('length_outer_4pt', 'support_span_4pt');
            $table->renameColumn('length_inner_4pt', 'loading_span_4pt');
            $table->renameColumn('torsion_span', 'gage_length_torsion');
            $table->renameColumn('compression_height', 'gage_length_compression');

            $table->string('preload_value')->nullable();
            $table->string('preload_unit')->nullable();

            $table->decimal('off_axis_angle', 5, 2)->nullable();

            $table->text('anatomical_reference')->nullable();

            $table->text('orientation_other')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mechanical_testing_details', function (Blueprint $table) {
            $table->dropColumn('preload_value');
            $table->dropColumn('preload_unit');
            $table->dropColumn('off_axis_angle');
            $table->dropColumn('anatomical_reference');
            $table->dropColumn('orientation_other');

            $table->renameColumn('support_span_3pt', 'length_3pt');
            $table->renameColumn('support_span_4pt', 'length_outer_4pt');
            $table->renameColumn('loading_span_4pt', 'length_inner_4pt');
            $table->renameColumn('gage_length_torsion', 'torsion_span');
            $table->renameColumn('gage_length_compression', 'compression_height');

            $table->decimal('loading_rate', 8, 2)->nullable();
            $table->string('loading_rate_unit')->nullable();

            // Remove newly added columns
            $table->dropColumn('bone_tested');
            $table->dropColumn('length_3pt');
            $table->dropColumn('length_outer_4pt');
            $table->dropColumn('length_inner_4pt');
            $table->dropColumn('compression_height');
            $table->dropColumn('fixed');

            // Restore old columns
            $table->decimal('support_point_1', 8, 2)->nullable();
            $table->decimal('support_point_2', 8, 2)->nullable();
            $table->decimal('loading_point_1', 8, 2)->nullable();
            $table->decimal('loading_point_2', 8, 2)->nullable();
        });
    }
};
