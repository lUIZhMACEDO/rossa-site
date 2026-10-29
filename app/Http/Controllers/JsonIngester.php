<?php

namespace App\Http\Controllers;

use App\Models\Investigator;
use App\Models\Study;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class JsonIngester extends Controller
{
    public function index()
    {
        return view('json-ingester.index');
    }

    public function ingest(Request $request)
    {
        Log::info('received an ingest request');

        $request->validate([
            'data' => 'required|string',
        ]);

        // read data string as json
        $json = json_decode($request->input('data'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json(['error' => 'Invalid JSON data'], 422);
        }

        try {
            // Create study based off JSON
            $study = $this->createStudy($json);
            Log::info('Study created successfully with identifier: '.$study->identifier);

            return response()->json([
                'success' => true,
                'message' => 'JSON data ingested successfully.',
                'identifier' => $study->identifier,
                'study_id' => $study->id,
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating study: '.$e->getMessage());
            Log::error('Stack trace: '.$e->getTraceAsString());

            return response()->json([
                'success' => false,
                'error' => 'Failed to create study: '.$e->getMessage(),
            ], 500);
        }
    }

    private function createStudy(array $json): Study
    {
        $study = new Study([
            'created_at' => now(),
            'status' => 'in_progress',
            'user_id' => auth()->id(),
        ]);

        // ==== Study Information ====

        $studyInfoRules = [
            'title' => 'required|string|max:255',
            'summary' => 'required|string',
            'funding_sources' => 'nullable|string',
            'conflicts' => 'nullable|string',
            'completion_date' => 'required|date',
            'species' => 'nullable|in:mouse,rat',
            'is_published' => 'required|boolean',
            'doi' => 'nullable|string|required_if:is_published,1',
            'pubmed_id' => 'nullable|string|required_if:is_published,1',
            'publication_plan' => 'nullable|string|required_if:is_published,0',
            'embargo_months' => 'nullable|integer|min:0|required_if:publication_plan,different_journal',
        ];
        $studyInfoMessages = [
            'title.required' => 'Please enter a study title.',
            'summary.required' => 'Please enter a study summary.',
            'completion_date.required' => 'Please enter a study completion date.',
            'is_published.required' => 'Please indicate whether the study has been published.',
            'doi.required_if' => 'Please enter a Digital Object Identifier (DOI).',
            'pubmed_id.required_if' => 'Please enter a PubMed ID.',
            'publication_plan.required_if' => 'Please select a publication plan.',
            'embargo_months.required_if' => 'Please enter an estimated embargo period.',
        ];

        $studyInfoValidator = Validator::make($json, $studyInfoRules, $studyInfoMessages);

        if ($studyInfoValidator->fails()) {
            Log::error('Study information validation failed', ['errors' => $studyInfoValidator->errors()]);
            throw new \Exception('Study information validation failed: '.$studyInfoValidator->errors()->first());
        }

        $studyInfoValidated = $studyInfoValidator->validated();

        $study->fill([
            'title' => $studyInfoValidated['title'],
            'summary' => $studyInfoValidated['summary'],
            'funding_sources' => $studyInfoValidated['funding_sources'],
            'conflicts' => $studyInfoValidated['conflicts'],
            'completion_date' => $studyInfoValidated['completion_date'],
            // 'species' => $studyInfoValidated['species'],
            'is_published' => $studyInfoValidated['is_published'],
            'doi' => $studyInfoValidated['doi'] ?? null,
            'pubmed_id' => $studyInfoValidated['pubmed_id'] ?? null,
            'publication_plan' => $studyInfoValidated['is_published'] ? null : ($studyInfoValidated['publication_plan'] ?? null),
            'embargo_months' => $studyInfoValidated['is_published'] ? null : ($studyInfoValidated['embargo_months'] ?? null),
        ]);

        // ==== Subject Areas ====

        $subjectAreasRules = [
            'subject_areas' => 'nullable|array',
            'subject_areas.*' => 'string',
            // TODO: disease_iri ??
        ];
        $subjectAreasMessages = [
            'subject_areas.array' => 'Subject areas must be an array.',
            'subject_areas.*.string' => 'Each subject area must be a string.',
        ];

        $subjectAreasValidator = Validator::make($json, $subjectAreasRules, $subjectAreasMessages);

        if ($subjectAreasValidator->fails()) {
            Log::error('Subject areas validation failed', ['errors' => $subjectAreasValidator->errors()]);
            throw new \Exception('Subject areas validation failed: '.$subjectAreasValidator->errors()->first());
        }

        $subjectAreasValidated = $subjectAreasValidator->validated();

        $study->fill([
            'subject_areas' => $subjectAreasValidated['subject_areas'] ?? [],
        ]);

        // Save the study to the database first (required before creating related records)
        $study->save();
        Log::info('Study saved successfully with ID: '.$study->id);

        // ==== Investigators ====

        $investigatorsRules = [
            'investigators' => 'required|array',
            'investigators.*.first_name' => 'required|string',
            'investigators.*.last_name' => 'required|string',
            'investigators.*.email' => 'required|email',
            'investigators.*.department' => 'nullable|string',
            'investigators.*.organization' => 'nullable|string',
            'investigators.*.country' => 'nullable|string',
            'investigators.*.state' => 'required_if:investigators.*.country,US|string',
            'investigators.*.corresponding' => 'boolean',
            'investigators.*.is_submitting' => 'boolean',
            'investigators.*.is_contributing' => 'boolean',
        ];

        $investigatorMessages = [
            'investigators.required' => 'Please provide at least one investigator.',
            'investigators.*.first_name.required' => 'First name is required for each investigator.',
            'investigators.*.last_name.required' => 'Last name is required for each investigator.',
            'investigators.*.email.required' => 'Email is required for each investigator.',
            'investigators.*.email.email' => 'Each investigator must have a valid email address.',
            'investigators.*.state.required_if' => 'State is required if the country is US.',
        ];

        $investigatorsValidator = Validator::make($json, $investigatorsRules, $investigatorMessages);

        if ($investigatorsValidator->fails()) {
            Log::error('Investigators validation failed', ['errors' => $investigatorsValidator->errors()]);
            throw new \Exception('Investigators validation failed: '.$investigatorsValidator->errors()->first());
        }

        $investigatorsValidated = $investigatorsValidator->validated();
        Log::info('Investigators validation passed');

        foreach ($investigatorsValidated['investigators'] as $investigatorData) {
            $isSubmitting = (bool) ($investigatorData['is_submitting'] ?? false);
            $corresponding = (bool) ($investigatorData['corresponding'] ?? false);
            $isContributing = (bool) ($investigatorData['is_contributing'] ?? false);
            $roleError = Investigator::roleSelectionError($isSubmitting, $corresponding, $isContributing);

            if ($roleError !== null) {
                throw new \Exception('Investigators validation failed: '.$roleError);
            }

            $investigator = $study->investigators()->create([
                'first_name' => $investigatorData['first_name'],
                'last_name' => $investigatorData['last_name'],
                'email' => $investigatorData['email'],
                'department' => $investigatorData['department'] ?? null,
                'organization' => $investigatorData['organization'] ?? null,
                'country' => $investigatorData['country'] ?? null,
                'state' => $investigatorData['state'] ?? null,
                'corresponding' => $corresponding,
                'is_submitting' => $isSubmitting,
                'is_contributing' => $isContributing,
            ]);
            Log::info('Created investigator: '.$investigator->first_name.' '.$investigator->last_name);
        }

        Log::info('All investigators created successfully');

        return $study;
    }
}

/*
Example JSON data
{
    "title": "Study Title",
    "summary": "Study Summary",
    "funding_sources": "Funding information",
    "conflicts": "Conflicts of interest",
    "completion_date": "2025-04-29",
    "species": "mouse",
    "is_published": true,
    "doi": "DOI if published",
    "pubmed_id": "PubMed ID if published",
    "publication_plan": "Plan if not published",
    "embargo_months": null,
    "subject_areas": [
        "array of subject area codes"
    ],
    "measurement_category_ids": [
        "array of measurement category IDs"
    ],
    "investigators": [
        {
        "first_name": "First",
        "last_name": "Last",
        "email": "email@example.com",
        "department": "Department",
        "organization": "Organization",
        "country": "Country",
        "corresponding": 1
        }
    ]
}

Select at least one role. Submitting and corresponding may both be true.
Set is_contributing to 1 only on its own, with corresponding and is_submitting false or omitted.
*/
