@extends('layout')

@section('title', $study->title)
@section('description', $study->summary
    ? \Illuminate\Support\Str::limit(preg_replace('/\s+/', ' ', strip_tags($study->summary)), 155)
    : 'Public skeletal phenotyping study '.$study->identifier.' in the Rodent Open Science Skeletal Archive.')
@section('canonical', route('search.show', $study))

@section('content')
<div class="container py-5">

    @include('search.partials.report-header')
   
    <!-- Navigation Tabs -->
    @include('search.partials.report-tabs')


    <!-- Study Information -->
    <div class="card report-section mt-4 mb-4">
    <div class="card report-section mb-4">
        <div class="card-header">
            <h4 class="mb-0">Study Information</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    @if ($study->completion_date)
                        <div class="mb-3">
                            <h5>Completion Date:</h5>
                            <p class="ms-3">{{ $study->completion_date->format('F j, Y') }}</p>
                        </div>
                    @endif
                </div>

                <div class="col-md-6">
                    <h5>Publication Information:</h5>
                    <p class="ms-3">
                        @if ($study->collection_id)
                            <span class="text-muted small d-block mb-1">
                                This study belongs to a collection.
                            </span>
                            <a href="{{ route('collections.show', $study->collection) }}">
                                View publications from this collection
                            </a>
                        @else
                            <strong>Published:</strong> {{ $study->is_published ? 'Yes' : 'No' }}
                            @if ($study->is_published)
                                <br>
                                @if($study->doi)
                                    <strong>DOI:</strong> <a href="{{ $study->doiLink() }}" target="_blank">{{ $study->doi }}</a>
                                    <br>
                                @endif
                                @if($study->pubmed_id)
                                    <strong>PubMed ID:</strong> <a href="{{ $study->pubmedLink() }}" target="_blank">{{ $study->pubmed_id }}</a>
                                @endif
                            @else
                                @if ($study->publication_plan === 'different_journal')
                                    <br>
                                    <strong>Publication Plan:</strong> Different Journal
                                @elseif ($study->publication_plan === 'special_issue')
                                    <br>
                                    <strong>Publication Plan:</strong> Special Issue of Bone
                                @endif
                            @endif
                        @endif
                    </p>
                </div>
            </div>
            @if($study->summary)
                <div>
                    <h5>Summary</h5>
                    <p class="ms-3">{{ $study->summary }}</p>
                </div>
            @endif

            @if ( !$study->isEmbargoed() )
                @if($study->funding_sources)
                    <div class="mt-3">
                        <h5>Funding Sources</h5>
                        <p class="ms-3">{{ $study->funding_sources }}</p>
                    </div>
                @endif
                @if($study->conflicts)
                    <div class="mt-3">
                        <h5>Conflicts of Interest</h5>
                        <p class="ms-3">{{ $study->conflicts }}</p>
                    </div>
                @endif
            @endif
        </div>
    </div>

    @if($study->hasSubjectAreaContent())
        <!-- Subject Areas -->
        <div class="card report-section mb-4">
            <div class="card-header">
                <h4 class="mb-0">Subject Areas</h4>
            </div>
            <div class="card-body">
                @php
                    $tagsByTab = $study->tags->groupBy('tab');
                @endphp

                @if($study->tags->isNotEmpty())
                    <div class="row">
                        @foreach(['Thematic Areas', 'Tissue Interactions', 'Cell Types'] as $tabName)
                            <div class="col-md-4">
                                <h5>{{ $tabName }}</h5>
                                @if(isset($tagsByTab[$tabName]) && $tagsByTab[$tabName]->isNotEmpty())
                                    <ul class="list">
                                        @foreach($tagsByTab[$tabName] as $tag)
                                            <li>{{ $tag->name }}</li>
                                        @endforeach
                                    </ul>
                                @else
                                    <p class="text-muted ms-3">None selected.</p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">No subject areas selected for this study.</p>
                @endif

                <h5 class="mt-4">Disease Lines</h5>
                @if($study->diseaseLines->isNotEmpty())
                    <div class="border rounded bg-light p-3" style="max-height: 400px; overflow-y: auto;">
                        <div class="row">
                            @foreach($study->diseaseLines as $disease)
                                <div class="col-12 mb-2">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <h6 class="card-title mb-0">
                                                    <strong>{{ $disease->label }}</strong>
                                                </h6>
                                                @if ($disease->iri)
                                                    <a href="{{ $disease->iri }}"
                                                        target="_blank"
                                                        class="text-nowrap">
                                                        {{ $disease->obo_id }}
                                                    </a>
                                                @else
                                                    <span class="text-muted text-nowrap">{{ $disease->obo_id }}</span>
                                                @endif
                                            </div>
                                            @if(!empty($disease->xrefs))
                                                <div class="d-flex flex-wrap gap-1">
                                                    @foreach($disease->xrefs as $xref)
                                                        @if($xref['url'])
                                                            <a href="{{ $xref['url'] }}"
                                                                target="_blank"
                                                                class="badge text-bg-secondary text-nowrap">
                                                                {{ $xref['database'] }}: {{ $xref['id'] }}
                                                            </a>
                                                        @else
                                                            <span class="badge text-bg-secondary text-nowrap">{{ $xref['database'] }}: {{ $xref['id'] }}</span>
                                                        @endif
                                                    @endforeach
                                                </div>
                                            @else
                                                <p class="text-muted mb-0">No cross-references available.</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <p class="text-muted">No disease lines have been associated with this study.</p>
                @endif
            </div>
        </div>
    @endif

    <!-- Investigators -->
    <div class="card report-section mb-4">
        <div class="card-header">
            <h4 class="mb-0">Investigators</h4>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Investigator</th>
                            <th>Department</th>
                            <th>Location</th>
                            <th>Role</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($study->investigators->isNotEmpty())
                            @foreach($study->investigators as $investigator)
                                <tr>
                                    <td>
                                        {{ $investigator->first_name }} {{ $investigator->last_name }}
                                    </td>
                                    <td>
                                        {{ $investigator->department }}
                                        <span class="d-block text-muted">{{ $investigator->organization }}</span>
                                    </td>
                                    <td>{{ $investigator->state ? $investigator->state . ', ' : '' }}{{ $investigator->country->name }}</td>
                                    <td>
                                        <x-investigator-role-badges :labels="$investigator->roleLabels()" />
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="4" class="text-muted text-center">No investigators have been added to this study.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Animal Models -->
    <div class="card report-section mb-4">
        <div class="card-header">
            <h4 class="mb-0">Animal Models</h4>
        </div>
        <div class="card-body">
            @if ($study->animalModels->count() > 0)
                <div class="row">
                    @foreach ($study->animalModels as $animalModel)
                        <div class="col-12 mb-3">
                            <div class="card">
                                <div class="card-body">
                                    <h5 class="mb-1">
                                        <strong class="text-primary">{{ ucfirst($animalModel->species) }}</strong>
                                        @if ($animalModel->animal_model_name)
                                            <span class="text-muted fs-6">{{ $animalModel->animal_model_name }}</span>
                                        @endif
                                    </h5>
                                    <hr class="mt-1 mb-2">

                                    @if (!$animalModel->geneticCategory)
                                        <span class="badge text-bg-light">No Genetic Modification</span>
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
                                        @endphp

                                        <div class="mb-2">
                                            <strong>Genetic Category:</strong> {{ $animalModel->geneticCategory?->name }}
                                        </div>

                                        @if($animalModel->geneticMethod)
                                            <div class="mb-2">
                                                <span class="badge text-bg-dark">{{ $animalModel->geneticMethod?->name }} ({{ $animalModel->geneticMethod?->allele_abbreviation ?? '' }})</span>
                                            </div>
                                        @endif

                                        <div class="row small">
                                            @if ($animalModel->isCustomAnimalModel() || $animalModel->isDoubleKnockout())
                                                @include('study.animal-experimentation.animal-models.partials.extended-details', ['animalModel' => $animalModel])
                                            @else
                                                @if ($gcTemplate == 'safe_harbor_knockin' && $animalModel->locus_name_safe_harbor)
                                                    <div class="col-md-12">
                                                        <p class="mb-1"><strong>Safe Harbor Locus:</strong> {{ $animalModel->locus_name_safe_harbor }}</p>
                                                    </div>
                                                @endif
                                                @if ($animalModel->experimentalStrainLabel())
                                                    <div class="col-md-6">
                                                        <p class="mb-1"><strong>Strain:</strong> {{ $animalModel->experimentalStrainLabel() }}</p>
                                                    </div>
                                                @endif
                                                @if($animalModel->investigator)
                                                    <div class="col-md-6">
                                                        <p class="mb-1"><strong>Source of Animal Model:</strong> {{ $animalModel->investigator }}</p>
                                                    </div>
                                                @endif
                                                @if ($animalModel->gene)
                                                    <div class="col-md-6">
                                                        <p class="mb-1">
                                                            <strong>Gene:</strong>
                                                            <em>{{ $animalModel->gene->symbol }}</em> - {{ $animalModel->gene->name }}
                                                        </p>
                                                    </div>
                                                @endif
                                                @if($animalModel->functional_change)
                                                    <div class="col-md-6">
                                                        <p class="mb-1"><strong>Functional Change:</strong> {{ $animalModel->functional_change }}</p>
                                                    </div>
                                                @endif
                                                @if($animalModel->tissue_lineage_specificity)
                                                    <div class="col-md-6">
                                                        <p class="mb-1"><strong>Tissue/Lineage:</strong> {{ $animalModel->tissue_lineage_specificity }}</p>
                                                    </div>
                                                @endif
                                            @endif
                                        </div>

                                        @if($animalModel->inducibleDrugTreatment)
                                            <div class="mt-2 p-2 bg-white rounded">
                                                <strong>Inducible Drug Treatment:</strong>
                                                @if($animalModel->inducibleDrugTreatment->molecule)
                                                    <span>{{ $animalModel->inducibleDrugTreatment->molecule->pref_name }}</span>
                                                @endif
                                                @if($animalModel->inducibleDrugTreatment->dose)
                                                    <span class="text-muted">
                                                        ({{ $animalModel->inducibleDrugTreatment->dose }} {{ $animalModel->inducibleDrugTreatment->dose_unit }})
                                                    </span>
                                                @endif
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted">No animal models have been added to this study.</p>
            @endif
        </div>
    </div>

    <!-- Drug Treatments -->
    @if($study->drugAdministrationPlans->count() > 0)
        <div class="card report-section mb-4">
            <div class="card-header">
                <h4 class="mb-0">Drug Treatments</h4>
            </div>
            <div class="card-body">
                @foreach($study->drugAdministrationPlans as $plan)
                    <div class="card mb-3">
                        <div class="card-body">
                            <h6 class="mb-2">Treatment {{ $loop->iteration }}</h6>
                            @if($plan->events->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Molecule</th>
                                                <th>Dose</th>
                                                <th>Duration</th>
                                                <th>Frequency</th>
                                                <th>Route</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($plan->events as $event)
                                                <tr>
                                                    <td>
                                                        @if($event->molecule)
                                                            {{ $event->molecule->pref_name }}
                                                            <span class="text-muted small d-block">{{ $event->molecule->chembl_id }}</span>
                                                        @else
                                                            <span class="text-muted">Not specified</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $event->dose ?? '-' }} {{ $event->dose_unit ?? '' }}</td>
                                                    <td>{{ $event->getDuration() ?? '-' }} days</td>
                                                    <td>{{ $event->frequency ?? '-' }}</td>
                                                    <td>{{ $event->route ?? '-' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted mb-0">No treatment lines specified.</p>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Gonadectomy Procedures -->
    @if ( $study->performs_gonadectomy )
        <div class="card report-section mb-4">
            <div class="card-header">
                <h4 class="mb-0">Gonadectomy Procedures</h4>
            </div>
            <div class="card-body">
                @include('study.summary.partials.gonadectomy-procedures')
            </div>
        </div>
    @endif

    <!-- Other Interventions -->
    @if ( $study->otherInterventionPlans()->count() > 0 )
        <div class="card report-section mb-4">
            <div class="card-header">
                <h4 class="mb-0">Interventions</h4>
            </div>
            <div class="card-body">
                @include('study.summary.partials.other-interventions')
            </div>
        </div>
    @endif

    <!-- Sample Groups -->
    @if ( $study->sampleGroups()->count() > 0 )
        <div class="card report-section mb-4">
            <div class="card-header">
                <h4 class="mb-0">Sample Groups</h4>
            </div>
            <div class="card-body">
                @include('study.sample-groups.partials.tree', ['study' => $study, 'viewonly' => true])
            </div>
        </div>
    @endif

    <!-- Comparisons -->
    @if ( $study->comparisonGroups()->count() > 0 )
        <div class="card report-section mb-4">
            <div class="card-header">
                <h4 class="mb-0">Comparisons</h4>
            </div>
            <div class="card-body">
                @include('study.comparison-groups.partials.comparison-table', ['study' => $study, 'viewonly' => true])
            </div>
        </div>
    @endif

    <!-- Phenotype Analysis -->
    @if ( $study->analysisUploads()->count() > 0 )
        <div class="card report-section mb-4">
            <div class="card-header">
                <h4 class="mb-0">Phenotype Analysis</h4>
            </div>
            <div class="card-body">
                @include('study.summary.partials.analysis-metadata')
            </div>
        </div>
    @endif
</div>
@endsection
