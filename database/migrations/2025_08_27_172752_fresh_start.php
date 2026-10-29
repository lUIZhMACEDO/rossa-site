<?php

use App\Enums\CoreFacilityStatus;
use App\Enums\StudyStatus;
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
        Schema::create('studies', function (Blueprint $table) {
            $table->id();
            $table->string('identifier')->comment('ROSSA ID');
            $table->text('title')->nullable();
            $table->text('summary')->nullable();
            $table->text('funding_sources')->nullable();
            $table->text('conflicts')->nullable();
            $table->date('completion_date')->nullable();
            $table->boolean('is_published')->nullable();
            $table->string('doi')->nullable();
            $table->string('pubmed_id')->nullable();
            $table->text('publication_plan')->nullable();
            $table->integer('embargo_months')->nullable();
            $table->json('subject_areas')->nullable();
            $table->timestamps();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('status', array_column(StudyStatus::cases(), 'value'))->default(StudyStatus::IN_PROGRESS->value);
            $table->timestamp('approved_at')->nullable();

            // experimental categories
            $table->boolean('performs_genetic_modification')->default(false);
            $table->boolean('performs_drug_treatment')->default(false);
            $table->boolean('performs_gonadectomy')->default(false);
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_custom')->default(false);
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null'); // custom roles are tied to a user
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->constrained('roles')->onDelete('set null');
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('permission_role', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained()->onDelete('cascade');
            $table->foreignId('permission_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            // permission <-> role should not have duplicate pivots
            $table->unique(['permission_id', 'role_id']);
        });

        Schema::create('core_facilities', function (Blueprint $table) {
            $table->id();
            $table->string('cf_id')->nullable();
            $table->string('facility_name');
            $table->text('web_address');
            $table->string('institution');
            $table->string('contact_name');
            $table->string('contact_email');
            $table->json('analysis_types')->nullable();
            $table->enum('status', array_column(CoreFacilityStatus::cases(), 'value'))->default(CoreFacilityStatus::PENDING->value);
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('last_refreshed_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_email_sent_at')->nullable();
            $table->timestamp('web_checked_at')->nullable();
            $table->integer('web_status')->nullable();
            $table->string('web_status_desc')->nullable();
            $table->text('web_status_error')->nullable();
            $table->timestamps();
        });

        Schema::create('feedback', function (Blueprint $table) {
            $table->id();
            $table->text('feedback');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->text('previous_url')->nullable();
            $table->boolean('resolved')->nullable();
            $table->timestamps();
        });

        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->timestamps();
        });

        Schema::create('analysis_types', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('name');
            $table->string('abbreviation')->nullable();
            $table->timestamps();
        });

        Schema::create('specimen_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('specimen_category');
            $table->timestamps();
        });

        Schema::create('analyte_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('specimen_type_id')->constrained()->onDelete('cascade');
            $table->string('name')->nullable();
            $table->string('slug')->nullable();
            $table->timestamps();
        });

        Schema::create('investigators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_id')->constrained()->onDelete('cascade');
            $table->text('first_name')->nullable();
            $table->text('last_name')->nullable();
            $table->text('email')->nullable();
            $table->text('department')->nullable();
            $table->text('organization')->nullable();
            $table->foreignId('country_id')->nullable()->constrained()->onDelete('set null');
            $table->string('state')->nullable();
            $table->boolean('corresponding')->nullable();
            $table->timestamps();
        });

        Schema::create('genetic_categories', function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->string('template')->nullable();
            $table->text('description')->nullable();
            $table->boolean('can_cre')->default(false);
            $table->boolean('can_drug_induce')->default(false);
            $table->timestamps();
        });

        Schema::create('genetic_methods', function (Blueprint $table) {
            $table->id();
            $table->text('name');
            $table->text('description')->nullable();
            $table->string('allele_abbreviation')->nullable();
            $table->foreignId('genetic_category_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('animal_lines', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->text('iri')->nullable();
            $table->text('description')->nullable();
            $table->string('ontology')->nullable();
            $table->json('synonyms')->nullable();
            $table->string('obo_id')->nullable();
            $table->json('definition_citation')->nullable();
            $table->string('species')->nullable();
            $table->foreignId('study_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('genes', function (Blueprint $table) {
            $table->id();
            $table->string('alliance_id');
            $table->string('symbol');
            $table->text('name');
            $table->string('species_taxonId')->nullable();
            $table->string('species_name')->nullable();
            $table->json('data')->nullable();
            $table->text('geneSynopsis')->nullable();
            $table->text('biotype')->nullable();
            $table->timestamps();
        });

        Schema::create('molecules', function (Blueprint $table) {
            $table->id();
            $table->string('chembl_id')->unique();
            $table->string('pref_name')->nullable();
            $table->string('molecule_type')->nullable();
            $table->string('max_phase')->nullable();
            $table->string('formula')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
        });

        Schema::create('animal_models', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_id')->constrained()->onDelete('cascade');
            $table->string('species')->nullable();
            $table->foreignId('animal_line_id')->nullable()->constrained()->onDelete('set null');
            $table->string('animal_model_name')->nullable();
            $table->string('investigator')->nullable();

            $table->foreignId('genetic_category_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('genetic_method_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('gene_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('promoter_gene_id')->nullable()->constrained('genes')->onDelete('set null');
            $table->text('promoter_other')->nullable();
            $table->string('functional_change')->nullable();
            $table->string('tissue_lineage_specificity')->nullable();
            $table->string('gene_product')->nullable();
            $table->string('locus_name_safe_harbor')->nullable();

            $table->string('transgene_abbreviation')->nullable();
            $table->string('transgene_biotype')->nullable();

            $table->string('cre_system_used')->nullable();
            $table->string('cre_animal_model_name')->nullable();
            $table->foreignId('cre_animal_line_id')->nullable()->constrained('animal_lines')->onDelete('set null');
            $table->string('cre_investigator')->nullable();
            $table->string('cre_regulation_type')->nullable();

            $table->foreignId('intercrossed_from_id')->nullable()->constrained('animal_models')->onDelete('set null');

            $table->timestamps();
        });

        Schema::create('drug_treatments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_id')->constrained()->onDelete('cascade');
            $table->string('treatment_category')->nullable();
            $table->string('vehicle')->nullable();
            $table->boolean('control_vehicle')->default(false);
            $table->boolean('control_no_treatment')->default(false);
            $table->timestamps();
        });

        Schema::create('gonadectomy_procedures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_id')->constrained()->onDelete('cascade');
            $table->string('type')->nullable();
            $table->text('description')->nullable();
            $table->integer('age')->nullable();
            $table->timestamps();
        });

        Schema::create('inducible_drug_treatments', function (Blueprint $table) {
            $table->id();
            $table->decimal('dose', 10, 4)->nullable();
            $table->string('dose_unit')->nullable();
            $table->integer('duration')->nullable();
            $table->string('duration_unit')->nullable();
            $table->string('frequency')->nullable();
            $table->string('frequency_other')->nullable();
            $table->string('drug_route')->nullable();
            $table->string('drug_route_other')->nullable();
            $table->string('impact')->nullable();
            $table->foreignId('animal_model_id')->constrained()->onDelete('cascade');
            $table->foreignId('molecule_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('sample_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_id')->constrained()->onDelete('cascade');
            $table->integer('age')->nullable();
            $table->string('sex')->nullable();
            $table->text('label')->nullable();
            $table->foreignId('animal_model_id')->constrained()->onDelete('cascade');
            $table->boolean('control')->nullable();
            $table->string('genotype')->nullable();
            $table->foreignId('drug_treatment_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('drug_treatment_type', ['normal', 'no_treatment', 'vehicle'])->nullable()->default('normal');
            $table->foreignId('gonadectomy_procedure_id')->nullable()->constrained()->onDelete('set null');
            $table->string('cre_status')->nullable();
            $table->timestamps();
        });

        Schema::create('experimental_comparisons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_id')->constrained('studies')->onDelete('cascade');
            $table->foreignId('experimental_sample_group_id')->constrained('sample_groups')->onDelete('cascade');
            $table->foreignId('control_sample_group_id')->constrained('sample_groups')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('drug_treatment_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('drug_treatment_id')->constrained()->onDelete('cascade');
            $table->foreignId('molecule_id')->nullable()->constrained()->onDelete('set null');
            $table->string('abbreviation')->nullable();
            $table->string('mode')->nullable();
            $table->integer('duration')->nullable();
            $table->string('duration_unit')->nullable();
            $table->integer('frequency')->nullable();
            $table->string('frequency_unit')->nullable();
            $table->decimal('dose', 8, 2)->nullable();
            $table->string('dose_unit')->nullable();
            $table->integer('start_age')->nullable();
            $table->string('target')->nullable();
            $table->string('impact')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });

        Schema::create('analysis_uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_id')->constrained()->onDelete('cascade');
            $table->string('path');
            $table->string('original_filename');
            $table->string('mime_type')->nullable();
            $table->integer('size')->nullable();
            $table->foreignId('analysis_type_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('specimen_type_id')->nullable()->constrained()->onDelete('set null');
            $table->json('headers')->nullable();
            $table->string('compartment')->nullable();
            $table->timestamps();
        });

        Schema::create('mechanical_testing_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analysis_upload_id')->constrained()->onDelete('cascade');
            $table->string('test_method')->nullable();
            $table->string('orientation')->nullable();
            $table->decimal('loading_rate', 8, 2)->nullable();
            $table->string('loading_rate_unit')->nullable();
            $table->decimal('support_point_1', 8, 2)->nullable();
            $table->decimal('support_point_2', 8, 2)->nullable();
            $table->decimal('loading_point_1', 8, 2)->nullable();
            $table->decimal('loading_point_2', 8, 2)->nullable();
            $table->decimal('torsion_span', 8, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('samples', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analysis_upload_id')->constrained()->onDelete('cascade');
            $table->string('label')->nullable();
            $table->foreignId('sample_group_id')->nullable()->constrained()->onDelete('set null');
            $table->json('raw_upload_data')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('media_uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_id')->constrained()->onDelete('cascade');
            $table->foreignId('analysis_upload_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('sample_id')->nullable()->constrained()->onDelete('set null');
            $table->string('path');
            $table->string('original_filename');
            $table->string('mime_type')->nullable();
            $table->integer('size')->nullable();
        });

        Schema::create('measurements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('display_name');
            $table->string('unit')->nullable();
            $table->foreignId('analysis_type_id')->nullable()->constrained()->onDelete('set null');
            $table->json('synonyms')->nullable();
            $table->foreignId('study_id')->nullable()->constrained()->onDelete('cascade')->comment('Implies custom');
            $table->foreignId('analyte_category_id')->nullable()->constrained()->onDelete('set null');
            $table->timestamps();
        });

        Schema::create('measurement_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sample_id')->constrained()->onDelete('cascade');
            $table->foreignId('measurement_id')->constrained()->onDelete('cascade');
            $table->string('value')->nullable();
            $table->timestamps();
        });

        Schema::create('disease_lines', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('iri');
            $table->text('description')->nullable();
            $table->string('ontology');
            $table->string('short_form')->nullable();
            $table->string('obo_id')->nullable();
            $table->string('type')->nullable();
            $table->json('xrefs')->nullable();
            $table->timestamps();
        });

        Schema::create('disease_line_study', function (Blueprint $table) {
            $table->id();
            $table->foreignId('study_id')->constrained()->onDelete('cascade');
            $table->foreignId('disease_line_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('study_disease_line');
        Schema::dropIfExists('disease_lines');
        Schema::dropIfExists('measurement_values');
        Schema::dropIfExists('measurements');
        Schema::dropIfExists('media_uploads');
        Schema::dropIfExists('samples');
        Schema::dropIfExists('mechanical_testing_details');
        Schema::dropIfExists('analysis_uploads');
        Schema::dropIfExists('drug_treatment_lines');
        Schema::dropIfExists('experimental_comparisons');
        Schema::dropIfExists('sample_groups');
        Schema::dropIfExists('inducible_drug_treatment');
        Schema::dropIfExists('gonadectomy_procedures');
        Schema::dropIfExists('drug_treatments');
        Schema::dropIfExists('animal_models');
        Schema::dropIfExists('genes');
        Schema::dropIfExists('animal_lines');
        Schema::dropIfExists('genetic_methods');
        Schema::dropIfExists('investigators');
        Schema::dropIfExists('analyte_categories');
        Schema::dropIfExists('specimen_types');
        Schema::dropIfExists('analysis_types');
        Schema::dropIfExists('countries');
        Schema::dropIfExists('feedback');
        Schema::dropIfExists('core_facilities');
        Schema::dropIfExists('studies');
        Schema::dropIfExists('permission_role');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });
    }
};
