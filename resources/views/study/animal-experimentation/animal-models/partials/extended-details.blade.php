@if ($animalModel->isCustomAnimalModel())
    @if($animalModel->experimentalStrainLabel())
        <div class="col-md-6">
            <p class="mb-1"><strong>Strain:</strong> {{ $animalModel->experimentalStrainLabel() }}</p>
        </div>
    @endif
    @if($animalModel->investigator)
        <div class="col-md-6">
            <p class="mb-1"><strong>Source of Animal Model:</strong> {{ $animalModel->investigator }}</p>
        </div>
    @endif
    @if($animalModel->animal_model_description)
        <div class="col-md-12">
            <p class="mb-1"><strong>Genetic Modification:</strong> {{ $animalModel->animal_model_description }}</p>
        </div>
    @endif
    @if($animalModel->functional_outcome_description)
        <div class="col-md-12">
            <p class="mb-1"><strong>Functional Outcome:</strong> {{ $animalModel->functional_outcome_description }}</p>
        </div>
    @endif
    @if($animalModel->locus_identity_type === 'genes' && $animalModel->targetGenes->isNotEmpty())
        <div class="col-md-12">
            <p class="mb-1"><strong>Gene(s):</strong>
                @foreach($animalModel->targetGenes as $gene)
                    @if(!$loop->first), @endif
                    @if($gene->getAllianceUrl())
                        <a href="{{ $gene->getAllianceUrl() }}" target="_blank">{{ $gene->symbol }}</a>
                    @else
                        {{ $gene->symbol }}
                    @endif
                @endforeach
            </p>
        </div>
    @elseif($animalModel->genomicRegionSummary())
        <div class="col-md-12">
            <p class="mb-1"><strong>Genomic Region:</strong> {{ $animalModel->genomicRegionSummary() }}</p>
        </div>
    @endif
    @if(count($animalModel->genotypeOptionList()) > 0)
        <div class="col-md-12">
            <p class="mb-1"><strong>Genotype Options:</strong> {{ implode(', ', $animalModel->genotypeOptionList()) }}</p>
        </div>
    @endif
@elseif($animalModel->isDoubleKnockout())
    @if($animalModel->experimentalStrainLabel())
        <div class="col-md-6">
            <p class="mb-1"><strong>Strain:</strong> {{ $animalModel->experimentalStrainLabel() }}</p>
        </div>
    @endif
    @if($animalModel->investigator)
        <div class="col-md-6">
            <p class="mb-1"><strong>Source of Animal Model:</strong> {{ $animalModel->investigator }}</p>
        </div>
    @endif
    @foreach(['a' => 'Parent A', 'b' => 'Parent B'] as $slot => $heading)
        @php $parent = $animalModel->parents->firstWhere('slot', $slot); @endphp
        @if($parent)
            <div class="col-md-6">
                <p class="mb-1"><strong>{{ $heading }}:</strong>
                    {{ $parent->animalLine?->label ?? '—' }}
                    @if($parent->gene)
                        · {{ $parent->gene->symbol }}
                    @endif
                    @if($parent->modificationLabel())
                        · {{ $parent->modificationLabel() }}
                    @endif
                </p>
                @if($parent->investigator)
                    <p class="mb-1"><strong>Source of {{ $heading }}:</strong> {{ $parent->investigator }}</p>
                @endif
            </div>
        @endif
    @endforeach
    @if(count($animalModel->genotypeOptionList()) > 0)
        <div class="col-md-12">
            <p class="mb-1"><strong>Genotype Combinations:</strong> {{ implode(', ', $animalModel->genotypeOptionList()) }}</p>
        </div>
    @endif
@endif
