<?php

namespace App\Services\Ai;

/**
 * The contract between Claude and ROSSA.
 *
 * The field names intentionally match what JsonIngester already accepts, so an
 * AI draft can be fed straight into the existing ingest path (or used to
 * prefill the study-information / investigators forms).
 *
 * Scope for v1: study information, subject areas, investigators.
 * Animal experimentation / sample groups come later.
 */
class StudyExtractionSchema
{
    public const VERSION = '2026-11-04.v1';

    public const TOOL_NAME = 'record_study_fields';

    /** Fields we ask Claude to cite evidence for. */
    public const EVIDENCE_FIELDS = [
        'title', 'summary', 'funding_sources', 'conflicts', 'completion_date',
        'is_published', 'doi', 'pubmed_id', 'species', 'investigators',
    ];

    /**
     * Tool definition sent to the Messages API. Forcing Claude to call this
     * tool means we always get structured JSON back, never free text.
     */
    public static function tool(): array
    {
        $nullableString = fn (string $description) => [
            'type' => ['string', 'null'],
            'description' => $description,
        ];

        return [
            'name' => self::TOOL_NAME,
            'description' => 'Record the ROSSA submission fields extracted from the paper. Use null for anything the paper does not state. Never guess emails, DOIs or IDs.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'title' => $nullableString('Full title of the paper/study.'),
                    'summary' => $nullableString('2-5 sentence plain summary of the study, based on the abstract.'),
                    'funding_sources' => $nullableString('Funding/grant statement, e.g. "NIH NIAMS R01AR012345".'),
                    'conflicts' => $nullableString('Conflict of interest / disclosure statement.'),
                    'completion_date' => $nullableString('Best available date as YYYY-MM-DD (publication or acceptance date). Null if none.'),
                    'species' => [
                        'type' => ['string', 'null'],
                        'enum' => ['mouse', 'rat', null],
                        'description' => 'Rodent species used.',
                    ],
                    'is_published' => [
                        'type' => ['boolean', 'null'],
                        'description' => 'True if this PDF is a published article (has a journal, DOI, etc.). False for a preprint/manuscript.',
                    ],
                    'doi' => $nullableString('DOI exactly as printed, without the https://doi.org/ prefix.'),
                    'pubmed_id' => $nullableString('PMID if printed in the PDF. Otherwise null.'),
                    'subject_areas' => [
                        'type' => 'array',
                        'items' => ['type' => 'string'],
                        'description' => 'Short topic keywords (e.g. "osteoporosis", "fracture healing").',
                    ],
                    'investigators' => [
                        'type' => 'array',
                        'description' => 'Authors, in author-list order.',
                        'items' => [
                            'type' => 'object',
                            'properties' => [
                                'first_name' => ['type' => 'string'],
                                'last_name' => ['type' => 'string'],
                                'email' => $nullableString('Only if printed in the PDF.'),
                                'department' => $nullableString('Department from the affiliation.'),
                                'organization' => $nullableString('University / institute from the affiliation.'),
                                'country' => $nullableString('Country from the affiliation.'),
                                'state' => $nullableString('US state, if the affiliation is in the US.'),
                                'corresponding' => ['type' => 'boolean', 'description' => 'Marked as corresponding author.'],
                            ],
                            'required' => ['first_name', 'last_name', 'corresponding'],
                        ],
                    ],
                    'evidence' => [
                        'type' => 'object',
                        'description' => 'For each field you filled in, where it came from.',
                        'additionalProperties' => [
                            'type' => 'object',
                            'properties' => [
                                'page' => ['type' => ['integer', 'null']],
                                'quote' => ['type' => 'string', 'description' => 'Short exact snippet (under 20 words) supporting the value.'],
                                'confidence' => ['type' => 'string', 'enum' => ['high', 'medium', 'low']],
                            ],
                            'required' => ['quote', 'confidence'],
                        ],
                    ],
                ],
                'required' => ['title', 'summary', 'investigators', 'evidence'],
            ],
        ];
    }

    public static function instructions(): string
    {
        return <<<'TXT'
You are helping a researcher fill out a submission form for ROSSA, the Rodent Open Science Skeletal Archive.
Read the attached paper and call the record_study_fields tool exactly once.

Rules:
- Only use information that is actually in the PDF. If something is not stated, use null (or an empty list).
- Do not invent emails, DOIs, PubMed IDs or affiliations.
- Keep names as printed; split into first_name / last_name.
- For every field you fill, add an entry in "evidence" with the page number, a short exact quote and your confidence.
TXT;
    }

    /**
     * Light validation for a *draft*. Much looser than JsonIngester because a
     * draft is allowed to be incomplete — the user fills in the gaps.
     */
    public static function draftRules(): array
    {
        return [
            'title' => 'nullable|string',
            'summary' => 'nullable|string',
            'funding_sources' => 'nullable|string',
            'conflicts' => 'nullable|string',
            'completion_date' => 'nullable|date',
            'species' => 'nullable|in:mouse,rat',
            'is_published' => 'nullable|boolean',
            'doi' => 'nullable|string',
            'pubmed_id' => 'nullable|string',
            'subject_areas' => 'nullable|array',
            'subject_areas.*' => 'string',
            'investigators' => 'array',
            'investigators.*.first_name' => 'required|string',
            'investigators.*.last_name' => 'required|string',
            'investigators.*.email' => 'nullable|email',
            'investigators.*.department' => 'nullable|string',
            'investigators.*.organization' => 'nullable|string',
            'investigators.*.country' => 'nullable|string',
            'investigators.*.state' => 'nullable|string',
            'investigators.*.corresponding' => 'boolean',
        ];
    }
}
