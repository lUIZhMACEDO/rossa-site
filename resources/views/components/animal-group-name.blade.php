@props(['animalGroup'])

<span class="animal-group-name">
    @if ( $animalGroup->gm_gko_gene_symbol )
        <span class="gm_gko_gene_symbol">{{ $animalGroup->gm_gko_gene_symbol }}</span>
        <sup class="gm_gko_genotype">{{ $animalGroup->gm_gko_genotype }}</sup>_
    @endif

    @if ( $animalGroup->gm_gko_gene_symbol_2 )
        <span class="gm_gko_gene_symbol_2">{{ $animalGroup->gm_gko_gene_symbol_2 }}</span>
        <sup class="gm_gko_genotype_2">{{ $animalGroup->gm_gko_genotype_2 }}</sup>_
    @endif

    @if ( $animalGroup->gm_ind_mutation_gene_symbol )
        <span class="gm_ind_mutation_gene_symbol">{{ $animalGroup->gm_ind_mutation_gene_symbol }}</span>
        <sup class="gm_ind_mutation_genotype">{{ $animalGroup->gm_ind_mutation_genotype }}</sup>_
    @endif

    @if ( $animalGroup->gm_ins_mutagenesis_gene_symbol )
        <span class="gm_ins_mutagenesis_gene_symbol">{{ $animalGroup->gm_ins_mutagenesis_gene_symbol }}</span>
        <sup class="gm_ins_mutagenesis_genotype">{{ $animalGroup->gm_ins_mutagenesis_genotype }}</sup>_
    @endif

    @if ( $animalGroup->gm_cond_knockout_gene_symbol )
        <span class="gm_cond_knockout_gene_symbol">{{ $animalGroup->gm_cond_knockout_gene_symbol }}</span>
        <sup class="gm_cond_knockout_genotype">{{ $animalGroup->gm_cond_knockout_genotype }}</sup>_
        <span class="gm_cond_knockin_cre_animal_line_abbreviation">{{ $animalGroup->gm_cond_knockin_cre_animal_line_abbreviation }}</span>
        <sup class="gm_cond_knockin_cre_animal_genotype">{{ $animalGroup->gm_cond_knockin_cre_animal_genotype }}</sup>_
        @if ( $animalGroup->gm_cond_knockin_cre_treatment_group )
            <span class="gm_cond_knockin_cre_treatment_group">{{ $animalGroup->gm_cond_knockin_cre_treatment_group }}</span>_
        @endif
    @endif

    @if ( $animalGroup->gm_cond_knockin_gene_symbol )
        <span class="gm_cond_knockin_gene_symbol">{{ $animalGroup->gm_cond_knockin_gene_symbol }}</span>
        <sup class="gm_cond_knockin_genotype">{{ $animalGroup->gm_cond_knockin_genotype }}</sup>_
        <span class="gm_cond_knockin_cre_animal_line_abbreviation">{{ $animalGroup->gm_cond_knockin_cre_animal_line_abbreviation }}</span>
        <sup class="gm_cond_knockin_cre_animal_genotype">{{ $animalGroup->gm_cond_knockin_cre_animal_genotype }}</sup>_
        @if ( $animalGroup->gm_cond_knockin_cre_treatment_group )
            <span class="gm_cond_knockin_cre_treatment_group">{{ $animalGroup->gm_cond_knockin_cre_treatment_group }}</span>_
        @endif
    @endif

    @if ( $animalGroup->gm_random_genome_gene_symbol )
        <span class="gm_random_genome_gene_symbol">{{ $animalGroup->gm_random_genome_gene_symbol }}</span>
        <sup class="gm_random_genome_genotype">{{ $animalGroup->gm_random_genome_genotype }}</sup>_
        @if ( $animalGroup->gm_random_genome_treatment_group )
            <span class="gm_random_genome_treatment_group">{{ $animalGroup->gm_random_genome_treatment_group }}</span>_
        @endif

        @if ( $animalGroup->gm_random_genome_gene_symbol_2 )
            <span class="gm_random_genome_gene_symbol_2">{{ $animalGroup->gm_random_genome_gene_symbol_2 }}</span>
            <sup class="gm_random_genome_genotype_2">{{ $animalGroup->gm_random_genome_genotype_2 }}</sup>_
        @endif

        @if ( $animalGroup->gm_random_genome_treatment_group_2 )
            <span class="gm_random_genome_treatment_group_2">{{ $animalGroup->gm_random_genome_treatment_group_2 }}</span>_
        @endif
    @endif

    @if ( $animalGroup->gonadectomy_type_of_surgery )
        <span class="gonadectomy_type_of_surgery">{{ $animalGroup->gonadectomy_type_of_surgery }}</span>_
        <span class="gonadectomy_age">{{ $animalGroup->gonadectomy_age }}</span>wk_
    @endif

    @if ( $animalGroup->strain )
        <span class="strain">{{ $animalGroup->strain }}</span>_
    @endif

    <span class="sex">{{ match($animalGroup->sex) {
        'male' => 'M',
        'female' => 'F'
    } }}</span>_

    <span class="age">{{ $animalGroup->age }}wk</span>

    {{-- TODO(Aaron): no longer have drug_treatment, use drug_administration_plan instead... --}}
    @if ($animalGroup->drug_treatment)
        @php
            $summary = App\Models\TreatmentSummary::findOrFail($animalGroup->drug_treatment);
            $type = match(App\Enums\StudyType::from($summary->treatment_category)) {
                App\Enums\StudyType::SINGLE => 'SI',
                App\Enums\StudyType::COMBINATION => 'C',
                App\Enums\StudyType::SEQUENTIAL => 'SE',
            };
        @endphp
        _<span class="drug_treatment_type">{{ $type }}</span>
        _<span class="drug_treatment">{{ $animalGroup->drug_treatment }}</span>
    @endif
</span>