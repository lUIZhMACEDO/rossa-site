@if ($study->animalModels->count() > 0)
    <div class="border rounded bg-light p-3" style="max-height: 400px; overflow-y: scroll;">
        <div class="row">
            @foreach ($study->animalModels as $animalModel)
                <div class="col-12 mb-3">
                    <div class="card">
                        <div class="card-body">
                            {{-- animal details --}}
                            <h5 class="mb-1">
                                <strong class="text-primary">{{ ucfirst($animalModel->species) }}</strong>
                                @if ($animalModel->animal_model_name)
                                    <span class="text-muted fs-6">{{ $animalModel->animal_model_name }}</span>
                                @endif
                            </h5>
                            <hr class="mt-1 mb-2">

                            {{-- genetic modification details --}}
                            @if (!$animalModel->geneticCategory)
                                <span class="badge text-bg-secondary">No Genetic Modification</span>
                                @if ($animalModel->experimentalStrainLabel())
                                    <div class="row small mt-2">
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Strain:</strong> {{ $animalModel->experimentalStrainLabel() }}</p>
                                        </div>
                                    </div>
                                @endif
                            @else
                                @php
                                    $gcTemplate = $animalModel->geneticCategory->template;
                                    $hasCre = $gcTemplate == 'conditional_knockout_knockin' || $gcTemplate == 'safe_harbor_knockin';
                                @endphp
                                <div class="mb-2">
                                    <span @class([
                                        'badge me-1',
                                        'text-bg-light' => !$animalModel->geneticCategory,
                                        'text-bg-secondary' => $gcTemplate == 'gene_targeting',
                                        'text-bg-danger' => $gcTemplate == 'spontaneous_mutation',
                                        'text-bg-primary' => $gcTemplate == 'insertional_mutagenesis',
                                        'text-bg-warning' => $gcTemplate == 'conditional_knockout_knockin',
                                        'text-bg-primarydark' => $gcTemplate == 'safe_harbor_knockin',
                                        'text-bg-success' => $gcTemplate == 'standard_transgenic',
                                        'text-bg-info text-dark' => $gcTemplate == 'custom_animal_model',
                                        'text-bg-dark' => $gcTemplate == 'double_knockout',
                                    ])>
                                        {{ $animalModel->geneticCategory?->name }}
                                    </span>
                                    <span class="badge text-bg-dark">{{ $animalModel->geneticMethod?->name }} ({{ $animalModel->geneticMethod?->allele_abbreviation ?? '' }})</span>
                                </div>

                                {{-- details --}}
                                <div class="row small">
                                    @if ($animalModel->isCustomAnimalModel() || $animalModel->isDoubleKnockout())
                                        @include('study.animal-experimentation.animal-models.partials.extended-details', ['animalModel' => $animalModel])
                                    @else
                                    @if ($gcTemplate == 'safe_harbor_knockin')
                                        <div class="col-md-12">
                                            <p class="mb-1"><strong>Safe Harbor Locus:</strong> {{ $animalModel->locus_name_safe_harbor }}</p>
                                        </div>
                                    @endif
                                    @if ($animalModel->experimentalStrainLabel())
                                        <div class="col-md-6">
                                            <p class="mb-1"><strong>Strain:</strong> {{ $animalModel->experimentalStrainLabel() }}</p>
                                        </div>
                                    @endif
                                    <div class="col-md-6">
                                        <p class="mb-1"><strong>Source of Animal Model:</strong> {{ $animalModel->investigator }}</p>
                                    </div>
                                    @if ($animalModel->gene)
                                        <div class="col-md-6">
                                            <p class="mb-1">
                                                <strong>
                                                    @if ($gcTemplate == 'safe_harbor_knockin' || $gcTemplate == 'standard_transgenic')
                                                        Regulatory Gene:
                                                    @elseif ($gcTemplate == 'conditional_knockout_knockin')
                                                        Target Gene:
                                                    @else
                                                        Gene Targeted:
                                                    @endif
                                                </strong>
                                                @if ($animalModel->gene->getAllianceUrl() )
                                                    <a href="{{ $animalModel->gene->getAllianceUrl() }}"
                                                        target="_blank"
                                                        title="View Gene Details"
                                                        data-bs-toggle="tooltip">
                                                        {{ $animalModel->gene?->name }}
                                                    </a>
                                                @else
                                                    {{ $animalModel->gene?->name }}
                                                @endif
                                            </p>
                                        </div>

                                        @if ($gcTemplate == 'safe_harbor_knockin' || $gcTemplate == 'standard_transgenic')
                                            {{-- handling safe harbor and standard transgenic --}}
                                            <div class="col-md-6">
                                                <p class="mb-1"><strong>Transgene Abbreviation:</strong> {{ $animalModel->transgene_abbreviation }}</p>
                                            </div>
                                            @if ($gcTemplate == 'standard_transgenic')
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>Tissue/Lineage Specificity:</strong> {{ $animalModel->tissue_lineage_specificity }}</p>
                                                </div>
                                            @endif
                                            <div class="col-md-6">
                                                <p class="mb-1"><strong>Coding Sequence Type:</strong> {{ \App\Enums\BioType::from($animalModel->transgene_biotype)->print() }}</p>
                                            </div>
                                            @if ($gcTemplate == 'standard_transgenic')
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>Gene Product:</strong> {{ $animalModel->gene_product }}</p>
                                                </div>
                                            @endif
                                        @endif

                                        @if ($animalModel->functional_change)
                                            <div class="col-md-6">
                                                <p class="mb-1"><strong>Functional Change:</strong> {{\App\Enums\FunctionalChange::from($animalModel->functional_change)->print() }}</p>
                                            </div>
                                        @else
                                            {{-- should not reach this - TODO remove --}}
                                            <p class="mb-1"><strong>Functional Change:</strong> <span class="text-danger">No Functional Change</span></p>
                                        @endif

                                        @if ($gcTemplate == 'safe_harbor_knockin')
                                            <div class="col-md-6">
                                                <p class="mb-1"><strong>Promoter:</strong>
                                                    @if ($animalModel->promoterGene)
                                                        @if ($animalModel->promoterGene->data['modCrossRefCompleteUrl'])
                                                            <a href="{{ $animalModel->promoterGene->data['modCrossRefCompleteUrl'] }}"
                                                                target="_blank"
                                                                title="View Gene Details"
                                                                data-bs-toggle="tooltip">
                                                                {{ $animalModel->promoterGene?->name }}
                                                            </a>
                                                        @else
                                                            {{ $animalModel->promoterGene?->name }}
                                                        @endif
                                                    @elseif ($animalModel->promoter_other)
                                                        {{ $animalModel->promoter_other }}
                                                    @else
                                                        <span class="text-muted"><em>None</em></span>
                                                    @endif
                                                </p>
                                            </div>
                                        @endif
                                    @else
                                        {{-- should not reach this - TODO remove --}}
                                        <p class="mb-1"><strong>Gene Modified:</strong> <span class="text-danger">No Gene Modified</span></p>
                                    @endif
                                    @endif
                                </div>


                                @if ($hasCre)
                                    {{-- cre system --}}
                                    <div class="bg-light rounded p-2 mb-2">
                                        @if ($animalModel->cre_system_used)
                                            <h6 class="mb-2 small"><i class="bi bi-gear"></i> CRE System - {{ \App\Enums\CreSystem::from($animalModel->cre_system_used)->print() }}</h6>
                                            <div class="row small">
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>CRE Model:</strong> {{ $animalModel->cre_animal_model_name }}</p>
                                                    <p class="mb-0"><strong>CRE Line:</strong> {{ $animalModel->creAnimalLine?->label ?? '' }} {{ $animalModel->creAnimalLine?->species ? '('. ucfirst($animalModel->creAnimalLine?->species) .')' : '' }}</p>
                                                </div>
                                                <div class="col-md-6">
                                                    <p class="mb-1"><strong>Tissue/Lineage Specificity:</strong> {{ $animalModel->tissue_lineage_specificity }}</p>
                                                    @if ($animalModel->cre_regulation_type)
                                                        <p class="mb-0"><strong>CRE Regulation Type:</strong> {{ \App\Enums\CreRegulationType::from($animalModel->cre_regulation_type)->print() }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                        @else
                                            <p class="mb-0 text-muted"><em>No CRE System</em></p>
                                        @endif
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
