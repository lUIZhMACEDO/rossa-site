<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * full study detail with relationship summaries - brief
 * paginated sub-resources - use dedicated routes
 *
 * accepts ?expand=full query param - get fully expanded relationships
 */
class StudyDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $expandFull = $request->boolean('expand') && $request->input('expand') === 'full';

        return [
            'identifier' => $this->identifier,
            'title' => $this->title,
            'summary' => $this->summary,
            'completion_date' => $this->completion_date?->toDateString(),
            'is_published' => $this->is_published ? true : false,
            'doi' => $this->doi,
            'doi_link' => $this->doiLink(),
            'pubmed_id' => $this->pubmed_id,
            'pubmed_link' => $this->pubmedLink(),

            'funding_sources' => $this->funding_sources,
            'conflicts' => $this->conflicts,
            'publication_plan' => $this->publication_plan,

            /** @var array{first_name: string, last_name: string, corresponding: bool, is_submitting: bool, is_contributing: bool}[] */
            'investigators' => $this->whenLoaded('investigators', fn () => $this->investigators->map(fn ($i) => [
                'first_name' => $i->first_name,
                'last_name' => $i->last_name,
                'corresponding' => $i->corresponding,
                'is_submitting' => $i->is_submitting,
                'is_contributing' => $i->isContributing(),
            ])
            ),

            /** @var array{label: string, obo_id: string}[] */
            'disease_lines' => $this->whenLoaded('diseaseLines', fn () => $this->diseaseLines->map(fn ($d) => [
                'label' => $d->label,
                'obo_id' => $d->obo_id,
            ])
            ),

            /** @var array{name: string}[] */
            'tags' => $this->whenLoaded('tags', fn () => $this->tags->map(fn ($t) => ['name' => $t->name])
            ),

            'animal_models_count' => $this->whenLoaded('animalModels', fn () => $this->animalModels->count()),
            'genes_count' => $this->whenLoaded('animalModels', fn () => $this->animalModels->whereNotNull('gene_id')->pluck('gene_id')->unique()->count()
            ),
            'sample_groups_count' => $this->whenLoaded('sampleGroups', fn () => $this->sampleGroups->count()),
            'analysis_uploads_count' => $this->whenLoaded('analysisUploads', fn () => $this->analysisUploads->count()),
            'drug_plans_count' => $this->whenLoaded('drugAdministrationPlans', fn () => $this->drugAdministrationPlans->count()),

            $this->mergeWhen($expandFull, [
                '_expand_full' => 'Full relationship expansion is not yet implemented. Use the dedicated sub-resource routes instead.',
            ]),
        ];
    }
}
