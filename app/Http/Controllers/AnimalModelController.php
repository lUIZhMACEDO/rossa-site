<?php

namespace App\Http\Controllers;

use App\Enums\DkoParentModification;
use App\Enums\LocusIdentityType;
use App\Enums\Species;
use App\Models\AnimalLine;
use App\Models\AnimalModel;
use App\Models\Gene;
use App\Models\GeneticCategory;
use App\Models\SampleGroup;
use App\Models\Study;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AnimalModelController extends Controller
{
    public function index(Study $study)
    {
        return redirect()->route('study.animal-experimentation.animal-models.create', $study);
    }

    // Shows the create model page.
    public function create(Request $request, Study $study)
    {
        $animalModel = $study->animalModels()->make();

        $selectedGeneticCategory = GeneticCategory::query()
            ->whereKey($request->input('genetic_category_id'))
            ->first();
        $showGeneticCategorySelection = $request->has('genetic_category_id') === false;

        $study->load(['animalModels' => function ($query) {
            $query->orderBy('created_at', 'asc')
                ->with(['animalLine', 'gene', 'geneticCategory', 'geneticMethod', 'targetGenes', 'parents.animalLine', 'parents.gene']);
        }]);

        $animalModels = $study->animalModels;

        return view('study.animal-experimentation.animal-models.create', compact(
            'study',
            'animalModel',
            'selectedGeneticCategory',
            'showGeneticCategorySelection',
            'animalModels'
        ));
    }

    // Handles submission of the create/edit form.
    public function submitAnimalModel(Request $request, Study $study, ?AnimalModel $animalModel = null)
    {
        $geneticCategory = GeneticCategory::query()
            ->whereKey($request->input('genetic_category_id'))
            ->first();
        $template = $geneticCategory?->template;
        $isDko = $template === 'double_knockout';
        $isCustom = $template === 'custom_animal_model';

        $rules = [
            'species' => ['required', 'string', Rule::in(array_column(Species::cases(), 'value'))],
            'animal_model_name' => ($isCustom || $isDko) ? 'required|string|max:255' : 'nullable|string|max:255',
            'animal_model_description' => 'nullable|string|max:5000',
            'functional_outcome_description' => 'nullable|string|max:5000',
            'investigator' => 'nullable|string|max:255',
            'genetic_category_id' => 'nullable|exists:genetic_categories,id',
            'genetic_method_id' => 'nullable|exists:genetic_methods,id',
            'gene_id' => 'nullable|exists:genes,id',
            'promoter_gene_id' => 'nullable|exists:genes,id',
            'promoter_other' => 'nullable|string|max:255',
            'functional_change' => 'nullable|string|max:255',
            'tissue_lineage_specificity' => 'nullable|string|max:255',
            'gene_product' => 'nullable|string|max:255',
            'locus_name_safe_harbor' => 'nullable|string|max:255',
            'transgene_abbreviation' => 'nullable|string|max:255',
            'transgene_biotype' => 'nullable|string|max:255',
            'cre_system_used' => 'nullable|string|max:255',
            'cre_animal_model_name' => 'nullable|string|max:255',
            'cre_animal_line_id' => 'nullable|exists:animal_lines,id',
            'cre_investigator' => 'nullable|string|max:255',
            'cre_regulation_type' => 'nullable|string|max:255',
            'intercrossed_from_id' => 'nullable|exists:animal_models,id',
        ];

        if (! $isDko) {
            $rules['animal_line'] = 'nullable|string|max:255';
            $rules['animal_line_id'] = 'required_without:animal_line|nullable|exists:animal_lines,id';
        } else {
            $rules['animal_line'] = 'nullable|string|max:255';
            $rules['animal_line_id'] = 'nullable|exists:animal_lines,id';
        }

        $request->validate($rules, [
            'animal_line_id.required_without' => 'Select a strain.',
            'animal_line_id.exists' => 'Select a valid strain.',
        ]);

        if ($isCustom) {
            $this->validateCustomAnimalModel($request);
        }

        if ($isDko) {
            $this->validateDoubleKnockout($request);
        }

        $data = $request->only([
            'species',
            'animal_line_id',
            'animal_model_name',
            'animal_model_description',
            'functional_outcome_description',
            'investigator',
            'genetic_category_id',
            'genetic_method_id',
            'gene_id',
            'promoter_gene_id',
            'promoter_other',
            'functional_change',
            'tissue_lineage_specificity',
            'gene_product',
            'locus_name_safe_harbor',
            'transgene_abbreviation',
            'transgene_biotype',
            'cre_system_used',
            'cre_animal_model_name',
            'cre_animal_line_id',
            'cre_investigator',
            'cre_regulation_type',
            'intercrossed_from_id',
        ]);

        if ($isCustom) {
            $data['locus_identity_type'] = $request->input('locus_identity_type');
            if ($data['locus_identity_type'] === LocusIdentityType::GENOMIC_REGION->value) {
                $data['genomic_chromosome'] = $request->input('genomic_chromosome');
                $data['genomic_start'] = $request->input('genomic_start') ?: null;
                $data['genomic_end'] = $request->input('genomic_end') ?: null;
                $data['genomic_assembly'] = $request->input('genomic_assembly') ?: null;
                $data['genomic_region_description'] = $request->input('genomic_region_description');
            }
        }

        // If this is an intercross line, don't allow changing of species or genetic category
        if ($animalModel && $animalModel->intercrossed_from_id) {
            unset($data['species']);
            unset($data['genetic_category_id']);
        }

        if ($geneticCategory && $geneticCategory->geneticMethods()->count() === 1) {
            $data['genetic_method_id'] = $geneticCategory->geneticMethods()->first()->id;
        }

        if (! $isDko && ! $request->input('animal_line_id') && $request->input('animal_line')) {
            $data['animal_line_id'] = $this->resolveAnimalLine(
                $study,
                $data['species'] ?? $animalModel?->species,
                $request->input('animal_line')
            )->id;
        }

        if ($isDko) {
            $data['animal_line_id'] = null;
        }

        $renamedCount = 0;

        if ($animalModel) {
            if ($animalModel->study_id !== $study->id) {
                abort(404, 'Animal model not found for this study.');
            }

            if ($isCustom || $isDko) {
                $renamedCount = $this->applyGenotypeOptionChanges($request, $animalModel);
            }

            $animalModel->update($data);
        } else {
            $animalModel = $study->animalModels()->create($data);
        }

        if ($isCustom) {
            $this->saveCustomIdentity($request, $animalModel);
        }

        if ($isDko) {
            $this->saveDoubleKnockoutParents($request, $study, $animalModel);
        }

        if ($request->has('inducible_drug_treatment') && $animalModel->canBeDrugInduced()) {
            $this->inducibleDrugTreatmentSubmit($request, $animalModel);
        }

        $message = $animalModel->wasRecentlyCreated
            ? 'Animal model created successfully.'
            : 'Animal model updated successfully.';

        if ($renamedCount > 0) {
            $message .= ' '.$renamedCount.' sample group'.($renamedCount === 1 ? '' : 's').' will be updated.';
        }

        return redirect()
            ->route('study.animal-experimentation.animal-models', $study)
            ->with('success', $message);
    }

    public function inducibleDrugTreatmentSubmit(Request $request, AnimalModel $animalModel)
    {

        $request->validate([
            'molecule_id' => 'required|exists:molecules,id',
            'drug_name' => 'nullable|string|max:255',
            'dose' => 'nullable|numeric|min:0',
            'dose_unit' => 'nullable|string|max:50',
            'duration' => 'nullable|numeric|min:0',
            'duration_unit' => 'nullable|string|max:50',
            'frequency' => 'nullable|string|max:50',
            'frequency_other' => 'nullable|string|max:255',
            'drug_route' => 'nullable|string|max:50',
            'drug_route_other' => 'nullable|string|max:255',
            'impact' => 'nullable|string|max:255',
        ]);

        $data = $request->only([
            'molecule_id',
            'drug_name',
            'dose',
            'dose_unit',
            'duration',
            'duration_unit',
            'frequency',
            'frequency_other',
            'drug_route',
            'drug_route_other',
            'impact',
        ]);

        if (! $animalModel) {
            abort(404, 'Animal model not found.');
        }

        // this should have already resolved through middleware if called by submitAnimalModel
        if ($request->user()->canEditStudy($animalModel->study) === false) {
            abort(403);
        }

        $treatment = $animalModel->inducibleDrugTreatment;
        if ($treatment) {
            // Update existing treatment
            $treatment->update($data);
        } else {
            // Create new treatment
            $animalModel->inducibleDrugTreatment()->create($data);
        }

        return $animalModel;

        // return redirect()
        //     ->route('study.animal-experimentation.animal-models.edit', [$animalModel->study, $animalModel])
        //     ->with('success', 'Inducible drug treatment saved successfully.');

    }

    public function createSimilar(Study $study, AnimalModel $animalModel)
    {
        if ($animalModel->study_id !== $study->id) {
            abort(404);
        }

        $animalModel->load(['targetGenes', 'parents']);

        $newAnimalModel = $study->animalModels()->make($animalModel->toArray());
        $newAnimalModel->animal_model_name = null;
        // $newAnimalModel->cre = false;
        $newAnimalModel->cre_system_used = null;
        $newAnimalModel->cre_animal_model_name = null;
        $newAnimalModel->cre_animal_line_id = null;
        $newAnimalModel->cre_investigator = null;
        $newAnimalModel->cre_regulation_type = null;
        // $newAnimalModel->intercrossed_from_id = null;

        $newAnimalModel->save();

        $targetGeneIds = $animalModel->targetGenes()->pluck('genes.id')->all();
        if (count($targetGeneIds) > 0) {
            $newAnimalModel->syncTargetGenes($targetGeneIds);
        }

        foreach ($animalModel->parents as $parent) {
            $newAnimalModel->parents()->create([
                'slot' => $parent->slot,
                'animal_line_id' => $parent->animal_line_id,
                'investigator' => $parent->investigator,
                'gene_id' => $parent->gene_id,
                'modification_type' => $parent->modification_type,
                'included_zygosities' => $parent->included_zygosities,
            ]);
        }

        return redirect()->route('study.animal-experimentation.animal-models.edit', [$study, $newAnimalModel])->with('success', 'Similar animal model created. Please update details as needed.');
    }

    public function edit(Study $study, AnimalModel $animalModel)
    {
        if ($animalModel->study_id !== $study->id) {
            abort(404);
        }

        if ($animalModel->intercrossed_from_id) {
            // Intercross animal models should be edited via the intercross route
            return redirect()->route('study.animal-experimentation.animal-models.intercross', [$study, $animalModel]);
        }

        $animalModel->load(['targetGenes', 'parents.gene', 'parents.animalLine', 'geneticCategory']);

        return view('study.animal-experimentation.animal-models.edit', compact('study', 'animalModel'));
    }

    /**
     * Show form for new intercross animal model, or edit existing intercross animal model.
     */
    public function intercross(Study $study, AnimalModel $animalModel)
    {
        if ($animalModel->study_id !== $study->id) {
            abort(404);
        }

        $intercrossedAnimalModel = $animalModel->intercross;

        if (! $intercrossedAnimalModel) {
            $intercrossedAnimalModel = new AnimalModel([
                'species' => $animalModel->species,
                'intercrossed_from_id' => $animalModel->id,
                'genetic_category_id' => $animalModel->genetic_category_id,
            ]);
            $selectedGeneticCategory = $animalModel->geneticCategory;
            $showGeneticCategorySelection = false;

            return view('study.animal-experimentation.animal-models.create', [
                'study' => $study,
                'animalModel' => $intercrossedAnimalModel,
                'selectedGeneticCategory' => $selectedGeneticCategory,
                'showGeneticCategorySelection' => $showGeneticCategorySelection,
            ]);
        }

        return view('study.animal-experimentation.animal-models.edit', [
            'study' => $study,
            'animalModel' => $intercrossedAnimalModel,
        ]);
    }

    public function destroy(Study $study, AnimalModel $animalModel)
    {
        if ($animalModel->study_id !== $study->id) {
            abort(404);
        }
        AnimalModel::destroy($animalModel->id);

        return redirect()->route('study.animal-experimentation.animal-models', $study)->with('success', ('Animal model deleted successfully.'));
    }

    private function validateCustomAnimalModel(Request $request): void
    {
        $species = Species::tryFrom($request->input('species'));
        $chromosomes = $species?->chromosomes() ?? [];
        $assemblies = $species?->genomeAssemblies() ?? [];

        $request->validate([
            'animal_model_description' => 'required|string|max:5000',
            'locus_identity_type' => ['required', Rule::in([LocusIdentityType::GENES->value, LocusIdentityType::GENOMIC_REGION->value])],
            'genotype_options' => [
                'required',
                'array',
                'min:1',
                'max:6',
                function (string $attribute, mixed $value, \Closure $fail) use ($request) {
                    if (count($this->cleanedGenotypeOptions($request)) === 0) {
                        $fail('Add at least one genotype label.');
                    }
                },
            ],
            'genotype_options.*' => 'nullable|string|max:255',
        ], [
            'animal_model_description.required' => 'A description is required for custom animal models.',
            'locus_identity_type.required' => 'Select whether this model modifies specific gene(s) or a genomic region.',
            'genotype_options.required' => 'Add at least one genotype label.',
            'genotype_options.min' => 'Add at least one genotype label.',
        ]);

        $labels = $this->cleanedGenotypeOptions($request);
        if (count($labels) !== count(array_unique($labels))) {
            throw ValidationException::withMessages([
                'genotype_options' => 'Genotype labels must be unique.',
            ]);
        }

        if ($request->input('locus_identity_type') === LocusIdentityType::GENES->value) {
            $geneIds = $this->customGeneIds($request);
            if (count($geneIds) === 0) {
                throw ValidationException::withMessages([
                    'gene_ids' => 'Select at least one gene.',
                ]);
            }
            if (count($geneIds) !== count(array_unique($geneIds))) {
                throw ValidationException::withMessages([
                    'gene_ids' => 'Each gene can only be selected once.',
                ]);
            }
            $request->merge(['gene_ids' => $geneIds]);
            $request->validate([
                'gene_ids' => 'required|array|min:1|max:3',
                'gene_ids.*' => 'required|exists:genes,id',
            ]);
        } else {
            $hasStart = filled($request->input('genomic_start'));
            $hasEnd = filled($request->input('genomic_end'));

            $request->validate([
                'genomic_chromosome' => ['required', Rule::in($chromosomes)],
                'genomic_region_description' => 'required|string|max:5000',
                'genomic_start' => [
                    'nullable',
                    'integer',
                    'min:1',
                    Rule::requiredIf($hasEnd),
                    function (string $attribute, mixed $value, \Closure $fail) use ($request) {
                        $end = $request->input('genomic_end');
                        if ($value && $end && (int) $value > (int) $end) {
                            $fail('Start must be less than or equal to end.');
                        }
                    },
                ],
                'genomic_end' => ['nullable', 'integer', 'min:1', Rule::requiredIf($hasStart)],
                'genomic_assembly' => [
                    Rule::requiredIf($hasStart || $hasEnd),
                    'nullable',
                    Rule::in($assemblies),
                ],
            ]);
        }
    }

    private function validateDoubleKnockout(Request $request): void
    {
        $modificationValues = array_column(DkoParentModification::cases(), 'value');

        $request->validate([
            'parent_a_animal_line_id' => 'required_without:parent_a_animal_line|nullable|exists:animal_lines,id',
            'parent_a_animal_line' => 'nullable|string|max:255',
            'parent_a_investigator' => 'nullable|string|max:255',
            'parent_a_gene_id' => 'required|exists:genes,id',
            'parent_a_modification_type' => ['required', Rule::in($modificationValues)],
            'parent_a_zygosities' => 'required|array|min:1',
            'parent_a_zygosities.*' => ['required', Rule::in(AnimalModel::DKO_ZYGOSITIES)],
            'parent_b_animal_line_id' => 'required_without:parent_b_animal_line|nullable|exists:animal_lines,id',
            'parent_b_animal_line' => 'nullable|string|max:255',
            'parent_b_investigator' => 'nullable|string|max:255',
            'parent_b_gene_id' => ['required', 'exists:genes,id', Rule::notIn([$request->input('parent_a_gene_id')])],
            'parent_b_modification_type' => ['required', Rule::in($modificationValues)],
            'parent_b_zygosities' => 'required|array|min:1',
            'parent_b_zygosities.*' => ['required', Rule::in(AnimalModel::DKO_ZYGOSITIES)],
            'genotype_options' => 'required|array|min:1',
            'genotype_options.*' => 'required|string|max:255',
        ], [
            'parent_a_animal_line_id.required_without' => 'Select a strain for parent A.',
            'parent_a_gene_id.required' => 'Select a gene for parent A.',
            'parent_a_modification_type.required' => 'Select a modification type for parent A.',
            'parent_a_zygosities.required' => 'Select at least one genotype for parent A.',
            'parent_a_zygosities.min' => 'Select at least one genotype for parent A.',
            'parent_b_animal_line_id.required_without' => 'Select a strain for parent B.',
            'parent_b_gene_id.required' => 'Select a gene for parent B.',
            'parent_b_gene_id.not_in' => 'Parent B must use a different gene than Parent A.',
            'parent_b_modification_type.required' => 'Select a modification type for parent B.',
            'parent_b_zygosities.required' => 'Select at least one genotype for parent B.',
            'parent_b_zygosities.min' => 'Select at least one genotype for parent B.',
            'genotype_options.required' => 'Select at least one genotype combination.',
            'genotype_options.min' => 'Select at least one genotype combination.',
        ]);

        $geneA = Gene::find($request->input('parent_a_gene_id'));
        $geneB = Gene::find($request->input('parent_b_gene_id'));
        $allowed = array_column(AnimalModel::dkoGeneratedCombinations(
            $request->input('parent_a_zygosities', []),
            $request->input('parent_b_zygosities', []),
            $geneA?->symbol,
            $geneB?->symbol
        ), 'label');

        foreach ($this->cleanedGenotypeOptions($request) as $label) {
            if (! in_array($label, $allowed, true)) {
                throw ValidationException::withMessages([
                    'genotype_options' => 'Selected combinations must match the generated list from the checked genotypes.',
                ]);
            }
        }
    }

    private function saveCustomIdentity(Request $request, AnimalModel $animalModel): void
    {
        $animalModel->genotype_options = $this->cleanedGenotypeOptions($request);

        if ($request->input('locus_identity_type') === LocusIdentityType::GENES->value) {
            $animalModel->clearGenomicRegion();
            $animalModel->locus_identity_type = LocusIdentityType::GENES->value;
            $animalModel->save();
            $animalModel->syncTargetGenes($this->customGeneIds($request));

            return;
        }

        $animalModel->clearTargetGenes();
        $animalModel->locus_identity_type = LocusIdentityType::GENOMIC_REGION->value;
        $animalModel->save();
    }

    private function saveDoubleKnockoutParents(Request $request, Study $study, AnimalModel $animalModel): void
    {
        $species = $request->input('species') ?: $animalModel->species;
        $animalModel->genotype_options = $this->cleanedGenotypeOptions($request);
        $animalModel->save();

        foreach (['a', 'b'] as $slot) {
            $prefix = 'parent_'.$slot;
            $lineId = $request->input($prefix.'_animal_line_id');
            if (! $lineId && $request->input($prefix.'_animal_line')) {
                $lineId = $this->resolveAnimalLine($study, $species, $request->input($prefix.'_animal_line'))->id;
            }

            $animalModel->parents()->updateOrCreate(
                ['slot' => $slot],
                [
                    'animal_line_id' => $lineId,
                    'investigator' => $request->input($prefix.'_investigator') ?: null,
                    'gene_id' => $request->input($prefix.'_gene_id'),
                    'modification_type' => $request->input($prefix.'_modification_type'),
                    'included_zygosities' => array_values($request->input($prefix.'_zygosities', [])),
                ]
            );
        }

        $animalModel->gene_id = $request->input('parent_a_gene_id');
        $animalModel->save();
    }

    private function applyGenotypeOptionChanges(Request $request, AnimalModel $animalModel): int
    {
        $newOptions = $this->cleanedGenotypeOptions($request);
        $renameMap = $animalModel->isCustomAnimalModel() ? $this->genotypeRenameMap($request) : [];
        $inUse = $animalModel->sampleGroups()
            ->whereNotNull('genotype')
            ->where('genotype', '!=', '')
            ->get(['id', 'genotype', 'label']);

        $blocked = [];
        foreach ($inUse as $group) {
            $current = $group->genotype;
            if (isset($renameMap[$current])) {
                continue;
            }
            if (! in_array($current, $newOptions, true)) {
                $blocked[] = $group->label ?: ('Sample group #'.$group->id);
            }
        }

        if (count($blocked) > 0) {
            throw ValidationException::withMessages([
                'genotype_options' => 'Cannot remove genotype labels still used by sample groups: '.implode(', ', $blocked).'.',
            ]);
        }

        $renamedCount = 0;
        foreach ($renameMap as $from => $to) {
            $renamedCount += SampleGroup::where('animal_model_id', $animalModel->id)
                ->where('genotype', $from)
                ->update(['genotype' => $to]);
        }

        $animalModel->genotype_options = $newOptions;
        $animalModel->save();

        return $renamedCount;
    }

    private function cleanedGenotypeOptions(Request $request): array
    {
        $values = $request->input('genotype_options', []);
        if (! is_array($values)) {
            return [];
        }

        $labels = [];
        foreach ($values as $value) {
            $label = trim((string) $value);
            if ($label !== '') {
                $labels[] = $label;
            }
        }

        return array_values($labels);
    }

    private function genotypeRenameMap(Request $request): array
    {
        $originals = $request->input('genotype_options_original', []);
        $values = $request->input('genotype_options', []);
        if (! is_array($originals) || ! is_array($values)) {
            return [];
        }

        $map = [];
        foreach ($originals as $index => $original) {
            $from = trim((string) $original);
            $to = trim((string) ($values[$index] ?? ''));
            if ($from !== '' && $to !== '' && $from !== $to) {
                $map[$from] = $to;
            }
        }

        return $map;
    }

    private function customGeneIds(Request $request): array
    {
        $ids = $request->input('gene_ids', []);
        if (! is_array($ids)) {
            $ids = [];
        }

        $count = (int) $request->input('gene_row_count', count($ids));
        $ids = array_slice($ids, 0, max(1, min(3, $count)));

        return array_values(array_filter($ids, fn ($id) => filled($id)));
    }

    private function resolveAnimalLine(Study $study, ?string $species, string $label): AnimalLine
    {
        return AnimalLine::firstOrCreate([
            'label' => $label,
            'species' => $species,
            'study_id' => $study->id,
        ]);
    }
}
