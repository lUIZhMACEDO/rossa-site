<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\GeneResource;
use App\Http\Resources\StudyDetailResource;
use App\Http\Resources\StudyListResource;
use App\Models\AnimalModel;
use App\Models\Gene;
use App\Models\Study;
use Dedoc\Scramble\Attributes\QueryParameter;
use Dedoc\Scramble\Attributes\Response as ApiResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class StudyController extends ApiController
{
    /**
     * List Studies
     *
     * Returns a paginated list of all publicly approved studies, ordered by approval
     * date (most recent first).
     *
     * *[WIP] Additional filters (disease, gene symbol, analysis type, date range) are planned.*
     *
     * @tags Studies
     */
    #[QueryParameter(
        name: 'q',
        description: 'Keyword search across title, summary, investigator names, gene symbols, disease labels, drug names, and tags.',
        example: 'femur'
    )]
    #[QueryParameter(
        name: 'tag',
        description: 'Filter by tag name (e.g. a thematic area, cell type, or tissue interaction).',
        example: 'Bone Metabolism'
    )]
    #[QueryParameter(
        name: 'species',
        description: 'Filter by species via animal models — partial, case-insensitive match.',
        example: 'mouse'
    )]
    #[QueryParameter(
        name: 'per_page',
        description: 'Results per page. Maximum depends on your access tier.',
        example: 10
    )]
    #[ApiResponse(
        status: 200,
        description: 'Paginated list of approved studies. Use `links.next` to fetch subsequent pages.'
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'tag' => ['nullable', 'string', 'max:255'],
            'species' => ['nullable', 'string', 'max:255'],
            'per_page' => ['nullable', 'integer', 'min:1'],
        ]);

        $query = Study::query()
            ->publiclyVisible()
            ->with([
                'investigators',
                'animalModels.gene',
                'diseaseLines',
                'tags',
                'drugAdministrationPlans.events.molecule',
                'analysisUploads.analysisType',
            ])
            ->orderByDesc('approved_at');

        $query = $this->applyFilters($request, $query);

        $studies = $query->paginate($this->resolvePerPage($request));

        return StudyListResource::collection($studies);
    }

    /**
     * [WIP] Get Study
     *
     * Returns full detail for a single approved study. The response includes all scalar
     * fields plus **relationship summaries**: brief arrays for investigators, disease lines,
     * and tags; counts for animal models, genes, sample groups, analysis uploads, and drug plans.
     *
     * For paginated sub-resources use the dedicated routes (e.g. `/genes`, `/animal-models`).
     *
     * **Query Parameters**
     *
     * | Parameter | Type | Description |
     * |---|---|---|
     * | `expand` | string | Pass `full` to request fully expanded relationships. Currently stubbed — returns a note in the response meta. |
     *
     * @tags Studies
     */
    public function show(Request $request, string $identifier): StudyDetailResource
    {
        $study = Study::query()
            ->publiclyVisible()
            ->where('identifier', $identifier)
            ->with([
                'investigators',
                'diseaseLines',
                'tags',
                'animalModels',
                'sampleGroups',
                'comparisonGroups',
                'analysisUploads',
                'drugAdministrationPlans',
            ])
            ->firstOrFail();

        return new StudyDetailResource($study);
    }

    /**
     * [WIP] List Study Genes
     *
     * Returns a paginated list of genes linked to the study through its animal models.
     * Results are ordered alphabetically by gene symbol.
     *
     * Each result includes: `alliance_id`, `symbol`, `name`, `species`.
     *
     * **Query Parameters**
     *
     * | Parameter | Type | Description |
     * |---|---|---|
     * | `per_page` | int | Results per page. Tier caps: public `25`, academic_nonprofit `unlimited`, commercial_for_profit `50`. |
     *
     * @tags Studies
     */
    public function genes(Request $request, string $identifier): AnonymousResourceCollection
    {
        $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1'],
        ]);

        $study = Study::query()
            ->publiclyVisible()
            ->where('identifier', $identifier)
            ->firstOrFail();

        $geneIds = AnimalModel::where('study_id', $study->id)
            ->whereNotNull('gene_id')
            ->pluck('gene_id')
            ->unique();

        $genes = Gene::whereIn('id', $geneIds)
            ->orderBy('symbol')
            ->paginate($this->resolvePerPage($request));

        return GeneResource::collection($genes);
    }

    /**
     * [WIP] List Study Animal Models
     *
     * > **This endpoint is planned and not yet implemented.**
     * > A summary count and brief list of animal models is available via
     * > `GET /api/studies/{identifier}` (`animal_models_count`).
     *
     * Will return a paginated list of animal models associated with the study,
     * including species, genetic category, and gene associations.
     *
     * @tags Studies
     */
    public function animalModels(Request $request, string $identifier): JsonResponse
    {
        return $this->notImplemented('A summary is available via GET /api/studies/{identifier} (animal_models_count).');
    }

    /**
     * [WIP] List Study Disease Associations
     *
     * > **This endpoint is planned and not yet implemented.**
     * > A brief list of disease lines is available via
     * > `GET /api/studies/{identifier}` (`disease_lines`).
     *
     * Will return a paginated list of disease ontology entries (OBO IDs and labels)
     * linked to the study.
     *
     * @tags Studies
     */
    public function diseases(Request $request, string $identifier): JsonResponse
    {
        return $this->notImplemented('A summary is available via GET /api/studies/{identifier} (disease_lines).');
    }

    /**
     * [WIP] List Study Investigators
     *
     * > **This endpoint is planned and not yet implemented.**
     * > A brief list of investigators is available via
     * > `GET /api/studies/{identifier}` (`investigators`).
     *
     * Will return a paginated list of investigators associated with the study,
     * including names, affiliations, and correspondence status.
     *
     * @tags Studies
     */
    public function investigators(Request $request, string $identifier): JsonResponse
    {
        return $this->notImplemented('A summary is available via GET /api/studies/{identifier} (investigators).');
    }

    /**
     * [WIP] List Study Sample Groups
     *
     * > **This endpoint is planned and not yet implemented.**
     * > A count is available via `GET /api/studies/{identifier}` (`sample_groups_count`).
     *
     * Will return a paginated list of sample groups (cohorts) defined in the study,
     * including age, sex, genotype, and animal model references.
     *
     * @tags Studies
     */
    public function sampleGroups(Request $request, string $identifier): JsonResponse
    {
        return $this->notImplemented('A count is available via GET /api/studies/{identifier} (sample_groups_count).');
    }

    /**
     * [WIP] List Study Drug Treatments
     *
     * > **This endpoint is planned and not yet implemented.**
     * > A count is available via `GET /api/studies/{identifier}` (`drug_plans_count`).
     *
     * Will return a paginated list of drug administration plans associated with
     * the study, including molecule details and dosing schedules.
     *
     * @tags Studies
     */
    public function drugTreatments(Request $request, string $identifier): JsonResponse
    {
        return $this->notImplemented('A count is available via GET /api/studies/{identifier} (drug_plans_count).');
    }

    /**
     * [WIP] List Study Analysis Uploads
     *
     * > **This endpoint is planned and not yet implemented.**
     * > A count is available via `GET /api/studies/{identifier}` (`analysis_uploads_count`).
     *
     * Will return a paginated list of phenotype analysis uploads (modalities such as
     * micro-CT, DEXA, bone histomorphometry, and mechanical testing) associated with
     * the study.
     *
     * @tags Studies
     */
    public function analysisUploads(Request $request, string $identifier): JsonResponse
    {
        return $this->notImplemented('A count is available via GET /api/studies/{identifier} (analysis_uploads_count).');
    }

    /**
     * Apply optional query parameter filters to a study query builder.
     * Add new filter blocks here as the API grows.
     */
    private function applyFilters(Request $request, Builder $query): Builder
    {
        if ($q = $request->input('q')) {
            $query->deepSearch($q);
        }

        if ($tag = $request->input('tag')) {
            $query->whereHas('tags', fn ($q) => $q->where('name', $tag));
        }

        if ($species = $request->input('species')) {
            $query->whereHas('animalModels', function ($q) use ($species) {
                $q->where('species', 'ilike', '%'.$species.'%');
            });
        }

        return $query;
    }
}
