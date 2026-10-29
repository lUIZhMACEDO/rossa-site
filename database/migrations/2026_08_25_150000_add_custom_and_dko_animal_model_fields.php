<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('animal_models', function (Blueprint $table) {
            $table->text('functional_outcome_description')->nullable()->after('animal_model_description');
            $table->string('locus_identity_type')->nullable()->after('functional_outcome_description');
            $table->string('genomic_chromosome')->nullable()->after('locus_identity_type');
            $table->unsignedBigInteger('genomic_start')->nullable()->after('genomic_chromosome');
            $table->unsignedBigInteger('genomic_end')->nullable()->after('genomic_start');
            $table->string('genomic_assembly')->nullable()->after('genomic_end');
            $table->text('genomic_region_description')->nullable()->after('genomic_assembly');
            $table->json('genotype_options')->nullable()->after('genomic_region_description');
        });

        Schema::create('animal_model_genes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('animal_model_id')->constrained()->onDelete('cascade');
            $table->foreignId('gene_id')->constrained()->onDelete('cascade');
            $table->unsignedTinyInteger('position')->default(1);
            $table->timestamps();

            $table->unique(['animal_model_id', 'gene_id']);
            $table->unique(['animal_model_id', 'position']);
        });

        Schema::create('animal_model_parents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('animal_model_id')->constrained()->onDelete('cascade');
            $table->string('slot', 8);
            $table->foreignId('animal_line_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('gene_id')->nullable()->constrained()->onDelete('set null');
            $table->string('modification_type')->nullable();
            $table->json('included_zygosities')->nullable();
            $table->timestamps();

            $table->unique(['animal_model_id', 'slot']);
        });

        $now = now();
        $existingCategory = DB::table('genetic_categories')
            ->where('template', 'double_knockout')
            ->first();

        if ($existingCategory) {
            $categoryId = $existingCategory->id;
        } else {
            $categoryId = DB::table('genetic_categories')->insertGetId([
                'name' => 'Global Knockout - Double Knockout',
                'template' => 'double_knockout',
                'description' => 'Cross of two independently modified parent lines to generate a constitutive double mutant and related genotype combinations.',
                'can_cre' => false,
                'can_drug_induce' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }

        $existingMethod = DB::table('genetic_methods')
            ->where('genetic_category_id', $categoryId)
            ->where('name', 'Double Knockout')
            ->first();

        if (! $existingMethod) {
            DB::table('genetic_methods')->insert([
                'name' => 'Double Knockout',
                'allele_abbreviation' => 'DKO',
                'description' => 'Double Knockout method under Global Knockout - Double Knockout',
                'genetic_category_id' => $categoryId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        $categoryIds = DB::table('genetic_categories')
            ->where('template', 'double_knockout')
            ->pluck('id');

        DB::table('genetic_methods')->whereIn('genetic_category_id', $categoryIds)->delete();
        DB::table('genetic_categories')->whereIn('id', $categoryIds)->delete();

        Schema::dropIfExists('animal_model_parents');
        Schema::dropIfExists('animal_model_genes');

        Schema::table('animal_models', function (Blueprint $table) {
            $table->dropColumn([
                'functional_outcome_description',
                'locus_identity_type',
                'genomic_chromosome',
                'genomic_start',
                'genomic_end',
                'genomic_assembly',
                'genomic_region_description',
                'genotype_options',
            ]);
        });
    }
};
