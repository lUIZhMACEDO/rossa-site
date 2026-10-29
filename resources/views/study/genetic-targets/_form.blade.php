<form action="{{ route('study.animal-targets.save', $study) }}" method="POST" x-data="{
    method: '{{ $geneticTarget->method }}',
    intercrossed: {{ $geneticTarget->creAnimals->count() > 0 && $geneticTarget->method === 'global_knockout' ? 'true' : 'false' }},
    inducible_system: '{{ $geneticTarget->inducible_system }}',
    geneType: '{{ $geneticTarget->gene_type }}',
    alleleSchema: '{{ $geneticTarget->allele_schema }}'
}">
    @csrf
    <div class="mb-3">
        <label for="method" class="form-label">Method</label>
        <select name="method" id="method" class="form-select" x-model="method">
            <option value="">Select method...</option>
            @if ( $study->gm_global_knockout || $geneticTarget->method === 'global_knockout' )
                <option value="global_knockout" @selected(old('method', $geneticTarget->method) === 'global_knockout')>Global Gene Targeted Animal Model</option>
            @endif

            @if ( $study->gm_induced_mutation || $geneticTarget->method === 'induced_mutation' )
                <option value="induced_mutation" @selected(old('method', $geneticTarget->method) === 'induced_mutation')>Global Chemically or X-Ray Induced Mutant Animal Model</option>
            @endif
            @if ( $study->gm_insertional_mutagenesis || $geneticTarget->method === 'insertional_mutagenesis' )
                <option value="insertional_mutagenesis" @selected(old('method', $geneticTarget->method) === 'insertional_mutagenesis')>Global Insertional Mutant Animal Model</option>
            @endif
            @if ( $study->gm_conditional_knockout || $geneticTarget->method === 'conditional_knockout' )
                <option value="conditional_knockout" @selected(old('method', $geneticTarget->method) === 'conditional_knockout')>Conditionally Engineered Animal Model</option>
            @endif
            @if ( $study->gm_conditional_knockin_safe_harbor || $geneticTarget->method === 'conditional_knockin_safe_harbor' )
                <option value="conditional_knockin_safe_harbor" @selected(old('method', $geneticTarget->method) === 'conditional_knockin_safe_harbor')>Conditional Knock-in into a "Safe Harbor" Site</option>
            @endif
            @if ( $study->gm_transgene || $geneticTarget->method === 'random_genome_transgenic' )
                <option value="random_genome_transgenic" @selected(old('method', $geneticTarget->method) === 'random_genome_transgenic')>Randomly Integrated Transgenic Animal Model</option>
            @endif
        </select>
    </div>


    <div class="form-check mb-3" x-show="method==='global_knockout' || method==='random_genome_transgenic'">
        <input class="form-check-input" type="checkbox" value="1" id="intercrossed" name="intercrossed" @checked(old('intercrossed', $geneticTarget->creAnimals->count() > 0 && $geneticTarget->canIntercross())) x-model="intercrossed">
        <label for="intercrossed" class="form-check-label">Check this box, if this study involves the intercross of a 2nd target animal line.</label>
        <div class="alert alert-warning mt-2 small py-2" x-show="intercrossed">An intercrossed line will be created.</div>
    </div>

    <div class="mb-3">
		<label for="animal_model_name" class="form-label">Name of Animal Model</label>
		<input type="text" name="animal_model_name" id="animal_model_name" class="form-control" value="{{ old('animal_model_name', $geneticTarget->animal_model_name) }}">
	</div>

    <div class="mb-3">
        <label for="investigator_name" class="form-label">Full Name of Investigator from which Animal Model Originated</label>
        <input type="text" name="investigator_name" id="investigator_name" class="form-control" value="{{ old('investigator_name', $geneticTarget->investigator_name) }}">
    </div>

    <div class="mb-3">
        <label for="gene_name" class="form-label">Name of Genetically Modified Gene</label>
        <input type="text" name="gene_name" id="gene_name" class="form-control" value="{{ old('gene_name', $geneticTarget->gene_name) }}">
    </div>

    <div class="mb-3">
        <label for="gene_symbol" class="form-label">Gene Symbol of Genetically Modified Gene</label>
        <input type="text" name="gene_symbol" id="gene_symbol" class="form-control" value="{{ old('gene_symbol', $geneticTarget->gene_symbol) }}">
    </div>

    <div class="mb-3" x-show="method !== 'conditional_knockin_safe_harbor' && method !== 'random_genome_transgenic'">
        <label for="modification_type" class="form-label">Type of Genetic Modification</label>
        <select name="modification_type" id="modification_type" class="form-select">
            <option value="">Select type...</option>
            <optgroup label="Global KO/KI Types" x-bind:hidden="method !== 'global_knockout'">
                @foreach(App\Models\GeneticAnimalTarget::$modificationTypes as $value => $label)
                    <option value="{{ $value }}" @selected(old('modification_type', $geneticTarget->modification_type) === $value)>{{ $label }}</option>
                @endforeach
            </optgroup>
            <optgroup label="Induced Mutation Types" x-bind:hidden="method !== 'induced_mutation'">
                @foreach(App\Models\GeneticAnimalTarget::$inducedModificationTypes as $value => $label)
                    <option value="{{ $value }}" @selected(old('modification_type', $geneticTarget->modification_type) === $value)>{{ $label }}</option>
                @endforeach
            </optgroup>
            <optgroup label="Insertional Mutagenesis Types" x-bind:hidden="method !== 'insertional_mutagenesis'">
                @foreach(App\Models\GeneticAnimalTarget::$insertionalModificationTypes as $value => $label)
                    <option value="{{ $value }}" @selected(old('modification_type', $geneticTarget->modification_type) === $value)>{{ $label }}</option>
                @endforeach
            </optgroup>
        </select>
    </div>


    <div class="mb-3">
        <label for="allele_schema" class="form-label">Mutant Allele Schema to Follow</label>
        <select name="allele_schema" id="allele_schema" class="form-select" x-model="alleleSchema">
            <option value="">Select schema...</option>
            <optgroup x-bind:hidden="method !== 'global_knockout'" label="Global Knockout Allele Schemas">
                @foreach(App\Models\GeneticAnimalTarget::$alleleSchemas as $value => $label)
                    <option value="{{ $value }}" @selected(old('allele_schema', $geneticTarget->allele_schema) === $value)>{{ $label }}</option>
                @endforeach
            </optgroup>
            <optgroup x-bind:hidden="method !== 'induced_mutation'" label="Induced Mutation Allele Schemas">
                @foreach(App\Models\GeneticAnimalTarget::$inducedAlleleSchemas as $value => $label)
                    <option value="{{ $value }}" @selected(old('allele_schema', $geneticTarget->allele_schema) === $value)>{{ $label }}</option>
                @endforeach
            </optgroup>
            <optgroup x-bind:hidden="method !== 'insertional_mutagenesis'" label="Insertional Mutagenesis Allele Schemas">
                @foreach(App\Models\GeneticAnimalTarget::$insertionalAlleleSchemas as $value => $label)
                    <option value="{{ $value }}" @selected(old('allele_schema', $geneticTarget->allele_schema) === $value)>{{ $label }}</option>
                @endforeach
            </optgroup>
            <optgroup x-bind:hidden="method !== 'conditional_knockout'" label="Conditional Knockout Allele Schemas">
                @foreach(App\Models\GeneticAnimalTarget::$conditionalAlleleSchemas as $value => $label)
                    <option value="{{ $value }}" @selected(old('allele_schema', $geneticTarget->allele_schema) === $value)>{{ $label }}</option>
                @endforeach
            </optgroup>
            <optgroup x-bind:hidden="method !== 'conditional_knockin_safe_harbor'" label="Conditional Knock-in Safe Harbor Allele Schemas">
                @foreach(App\Models\GeneticAnimalTarget::$safeHarborAlleleSchemas as $value => $label)
                    <option value="{{ $value }}" @selected(old('allele_schema', $geneticTarget->allele_schema) === $value)>{{ $label }}</option>
                @endforeach
            </optgroup>
            <optgroup x-bind:hidden="method !== 'random_genome_transgenic'" label="Random Genome Transgenic Allele Schemas">
                @foreach(App\Models\GeneticAnimalTarget::$transgenicAlleleSchemas as $value => $label)
                    <option value="{{ $value }}" @selected(old('allele_schema', $geneticTarget->allele_schema) === $value)>{{ $label }}</option>
                @endforeach
            </optgroup>
        </select>
    </div>

    <div class="mb-3" x-show="alleleSchema === 'other'">
        <label for="allele_abbreviation" class="form-label">Mutant Allele Abbreviation/Symbol <span class="text-muted">(max 4 characters)</span></label>
        <input type="text" name="allele_abbreviation" id="allele_abbreviation" class="form-control" value="{{ old('allele_abbreviation', $geneticTarget->allele_abbreviation) }}">
    </div>


    <div class="mb-3">
        <label for="gene_type" class="form-label">
            <span x-show="method !== 'conditional_knockin_safe_harbor' && method !== 'random_genome_transgenic'">Gene Type</span>
            <span x-show="method === 'conditional_knockin_safe_harbor' || method === 'random_genome_transgenic'">Type of Coding Sequence</span>
        </label>
        <select name="gene_type" id="gene_type" class="form-select" x-model="geneType">
            <option value="">Select type...</option>
            @foreach(App\Models\GeneticAnimalTarget::$geneTypes as $value => $label)
                <option value="{{ $value }}" @selected(old('gene_type', $geneticTarget->gene_type) === $value)>{{ $label }}</option>
            @endforeach
        </select>

        <div class="mt-2" id="gene_type_other" x-show="geneType === 'other'">
            <label for="gene_type_other" class="form-label">Other Gene Type</label>
            <input type="text" name="gene_type_other" id="gene_type_other" class="form-control" value="{{ old('gene_type_other', $geneticTarget->gene_type_other) }}">
        </div>
    </div>


    <div class="mb-3" x-show="method === 'conditional_knockin_safe_harbor' || method === 'random_genome_transgenic'">
        <label for="gene_product" class="form-label">Name of Gene Product Being Expressed</label>
        <input type="text" name="gene_product" id="gene_product" class="form-control" value="{{ old('gene_product', $geneticTarget->gene_product) }}">
    </div>
    

    <div class="mb-3">
        <label for="functional_change" class="form-label">
            <span x-show="method !== 'conditional_knockin_safe_harbor' && method !== 'random_genome_transgenic'">Functional Change</span>
            <span x-show="method === 'conditional_knockin_safe_harbor' || method === 'random_genome_transgenic'">Function of the Transgene</span>
        </label>
        <select name="functional_change" id="functional_change" class="form-select">
            <option value="">Select change...</option>
            <optgroup label="Functional Change Options" x-bind:hidden="method === 'conditional_knockin_safe_harbor' || method === 'random_genome_transgenic'">
                @foreach(App\Models\GeneticAnimalTarget::$functionalChangeOptions as $value => $label)
                    <option value="{{ $value }}" @selected(old('functional_change', $geneticTarget->functional_change) === $value)>{{ $label }}</option>
                @endforeach
            </optgroup>
            <optgroup label="Transgene Function Options" x-bind:hidden="method !== 'conditional_knockin_safe_harbor' && method !== 'random_genome_transgenic'">
                @foreach(App\Models\GeneticAnimalTarget::$transgeneFunctionalChangeOptions as $value => $label)
                    <option value="{{ $value }}" @selected(old('functional_change', $geneticTarget->functional_change) === $value)>{{ $label }}</option>
                @endforeach
            </optgroup>
        </select>
    </div>

    <livewire:animal-strain-lookup :$study :geneticAnimalTarget="$geneticTarget" name="animal_strain" />

    <div class="mb-3" x-show="method === 'conditional_knockin_safe_harbor' || method === 'random_genome_transgenic'">
        <label for="tissue_lineage_specificity" class="form-label">Tissue/Cell Lineage Specificity of Transgenic Line</label>
        <input type="text" name="tissue_lineage_specificity" id="tissue_lineage_specificity" class="form-control" value="{{ old('tissue_lineage_specificity', $geneticTarget->tissue_lineage_specificity) }}">
    </div>


    <div class="mb-3" x-show="method === 'conditional_knockout' || method === 'conditional_knockin_safe_harbor' || method === 'random_genome_transgenic'">
        <label for="inducible_system" class="form-label">Inducible System</label>
        <select name="inducible_system" id="inducible_system" class="form-select" x-model="inducible_system">
            <option value="">Select system...</option>
            <optgroup label="Typical Systems" x-bind:hidden="method === 'random_genome_transgenic'">
                @foreach(App\Models\GeneticAnimalTarget::$inducibleSystems as $value => $label)
                    <option value="{{ $value }}" @selected(old('inducible_system', $geneticTarget->inducible_system) === $value)>{{ $label }}</option>
                @endforeach
            </optgroup>
            <option value="drug" @selected(old('inducible_system', $geneticTarget->inducible_system) === 'drug') x-show="method === 'random_genome_transgenic'">Drug Inducible</option>
        </select>

        <div class="alert alert-warning mt-2 small py-2" x-show="inducible_system && method !== 'random_genome_transgenic'">A CRE line will be created.</div>
    </div>
    <div x-show="inducible_system && inducible_system !== 'transgene'">
        @if ( $geneticTarget->id )
            <livewire:inducible-drug-treatment-table :study="$study" :type="$geneticTarget->method" :geneticAnimalTarget="$geneticTarget" :autofillName="$geneticTarget->inducible_system" />
        @else
            <p>Creating drug treatments will become available as soon as this line is saved.</p>
        @endif
    </div>

    

    
    

    <input type="hidden" name="id" value="{{ $geneticTarget->id }}">
    <button class="btn btn-primary" type="submit">Save</button>
</form>