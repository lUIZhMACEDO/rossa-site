# AI PDF extraction (prototype)

Goal: a user uploads a paper PDF and ROSSA pre-fills the submission forms with
AI suggestions that the user reviews before saving.

```
PDF -> Claude (Messages API, PDF document block, forced tool call)
    -> JSON draft (same shape JsonIngester accepts)
    -> ai_extractions table (jsonb draft + evidence)
    -> [next] prefill study-information / investigators forms
    -> user reviews + saves -> real study tables
```

The `ai_extractions` table only holds **drafts**. Search, the datacenter and
stats keep reading the normal relational tables. Nothing is saved to a study
without a user confirming it.

## Setup

```
# .env
ANTHROPIC_API_KEY=sk-ant-...
ANTHROPIC_MODEL=claude-sonnet-5-5
ANTHROPIC_MAX_PDF_MB=20

php artisan migrate
```

## Try it

```
# print the draft only (no DB writes)
php artisan rossa:extract-pdf storage/app/sample.pdf --out=draft.json

# store the PDF and save the draft in ai_extractions
php artisan rossa:extract-pdf storage/app/sample.pdf --save --user=1
```

Tests (API is faked, no key needed):

```
./vendor/bin/pest tests/Feature/PaperExtractionServiceTest.php
```

## What's here

| File | Purpose |
| --- | --- |
| `app/Services/Ai/StudyExtractionSchema.php` | Fields we ask Claude for (tool JSON schema), prompt, draft validation rules |
| `app/Services/Ai/PaperExtractionService.php` | Sends the PDF to Claude, parses and validates the tool call |
| `app/Jobs/ExtractStudyFromPdf.php` | Queued job: stored PDF -> draft in `ai_extractions` |
| `app/Models/AiExtraction.php`, `app/Enums/AiExtractionStatus.php` | Draft model and statuses |
| `app/Console/Commands/ExtractPdf.php` | `rossa:extract-pdf` demo command |
| `tests/Feature/PaperExtractionServiceTest.php` | Tests with a faked API |

## Scope (v1)

Study information, subject areas and investigators only. Not yet: animal
experimentation, sample groups and drug plans.

## Next steps

1. Upload page and route that creates an `AiExtraction` and dispatches the job.
2. Prefill the study-information and investigators forms from a `ready` draft, with "AI suggested" badges and evidence on hover.
3. Share validation rules between `JsonIngester` and the schema class.
4. Resolve names (genes, molecules, strains, countries) to IDs with the existing services instead of trusting AI output.
5. Accuracy check against 10-20 already-submitted studies.
6. Confirm it's okay to send unpublished manuscripts to an external API before production.
