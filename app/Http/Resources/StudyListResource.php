<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Study representation for paginated list responses, including key relationship
 * summaries for browsing. For full relationship objects use StudyDetailResource
 * (GET /api/studies/{identifier}).
 */
class StudyListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'identifier' => $this->identifier,
            'title' => $this->title,
            'summary' => $this->summary,
            'completion_date' => $this->completion_date?->toDateString(),
            'doi' => $this->doi,
            'doi_link' => $this->doiLink(),
            'pubmed_id' => $this->pubmed_id,
            'pubmed_link' => $this->pubmedLink(),

            /** @var string[] */
            'investigators' => $this->whenLoaded('investigators', fn () => $this->investigators
                ->map(fn ($i) => trim("$i->first_name $i->last_name"))
                ->filter()->values()
            ),

            /** @var string[] Distinct species from animal models */
            'species' => $this->whenLoaded('animalModels', fn () => $this->animalModels
                ->pluck('species')->filter()->unique()->values()
            ),

            /** @var string[] Gene symbols linked via animal models */
            'genes' => $this->whenLoaded('animalModels', fn () => $this->animalModels
                ->map(fn ($m) => $m->gene?->symbol)
                ->filter()->unique()->sort()->values()
            ),

            /** @var string[] Disease ontology labels */
            'diseases' => $this->whenLoaded('diseaseLines', fn () => $this->diseaseLines->pluck('label')->filter()->values()
            ),

            /** @var string[] */
            'tags' => $this->whenLoaded('tags', fn () => $this->tags->pluck('name')->values()
            ),

            /** @var string[] Drug molecule names from administration plans */
            'molecules' => $this->whenLoaded('drugAdministrationPlans', fn () => $this->drugAdministrationPlans
                ->flatMap(fn ($p) => $p->events)
                ->map(fn ($e) => $e->molecule?->pref_name)
                ->filter()->unique()->values()
            ),

            /** @var string[] Distinct phenotype analysis modality names (e.g. micro-CT, DEXA) */
            'modalities' => $this->whenLoaded('analysisUploads', fn () => $this->analysisUploads
                ->map(fn ($u) => $u->analysisType?->name)
                ->filter()->unique()->sort()->values()
            ),
        ];
    }
}
