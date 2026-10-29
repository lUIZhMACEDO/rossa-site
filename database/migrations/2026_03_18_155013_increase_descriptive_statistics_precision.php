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
        Schema::table('descriptive_statistics', function (Blueprint $table) {
            $table->decimal('mean', 15, 4)->nullable()->change();
            $table->decimal('median', 15, 4)->nullable()->change();
            $table->decimal('sd', 15, 4)->nullable()->comment('Standard deviation')->change();
            $table->decimal('variance', 20, 4)->nullable()->change();
            $table->decimal('sem', 15, 4)->nullable()->comment('Standard error of mean')->change();
            $table->decimal('cv', 15, 4)->nullable()->comment('Coefficient of variation (%)')->change();
            $table->decimal('min', 15, 4)->nullable()->change();
            $table->decimal('max', 15, 4)->nullable()->change();
            $table->decimal('range', 15, 4)->nullable()->change();
            $table->decimal('q1', 15, 4)->nullable()->comment('First quartile')->change();
            $table->decimal('q3', 15, 4)->nullable()->comment('Third quartile')->change();
            $table->decimal('iqr', 15, 4)->nullable()->comment('Interquartile range')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('descriptive_statistics', function (Blueprint $table) {
            $table->decimal('mean', 12, 7)->nullable()->change();
            $table->decimal('median', 12, 7)->nullable()->change();
            $table->decimal('sd', 12, 7)->nullable()->comment('Standard deviation')->change();
            $table->decimal('variance', 12, 7)->nullable()->change();
            $table->decimal('sem', 12, 7)->nullable()->comment('Standard error of mean')->change();
            $table->decimal('cv', 12, 7)->nullable()->comment('Coefficient of variation (%)')->change();
            $table->decimal('min', 12, 7)->nullable()->change();
            $table->decimal('max', 12, 7)->nullable()->change();
            $table->decimal('range', 12, 7)->nullable()->change();
            $table->decimal('q1', 12, 7)->nullable()->comment('First quartile')->change();
            $table->decimal('q3', 12, 7)->nullable()->comment('Third quartile')->change();
            $table->decimal('iqr', 12, 7)->nullable()->comment('Interquartile range')->change();
        });
    }
};
