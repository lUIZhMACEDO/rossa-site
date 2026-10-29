<?php

namespace App\Docs;

use App\Enums\InstitutionType;

class ScrambleApiDescription
{
    public static function get(): string
    {
        $appUrl = rtrim((string) env('APP_URL', ''), '/');
        $contactUrl = $appUrl ? ($appUrl.'/contact') : '/contact';
        $termsPath = '/terms-of-use';
        $termsUrl = config('api.terms_url') ?: ($appUrl ? $appUrl.$termsPath : $termsPath);
        $apiDocsUrl = $appUrl ? ($appUrl.'/api-docs') : '/api-docs';

        $tierRows = collect(InstitutionType::cases())
            ->map(fn (InstitutionType $type) => sprintf(
                '| **%s** | %s | ✅ Yes | %s |',
                $type->label(),
                $type->whoItsFor(),
                $type->usageLabel()
            ))
            ->implode("\n");

        return <<<MD
The ROSSA API provides programmatic access to the Rodent Open Science Skeletal Archive —
a curated database of rodent skeletal phenotyping studies, including gene associations,
disease linkages, drug treatments, and quantitative analysis data.

## Authentication

All endpoints are publicly accessible without a token. Providing a valid **Sanctum Bearer token**
unlocks higher rate limits and pagination volume appropriate for your use case.

```
Authorization: Bearer YOUR_API_TOKEN
```

To get a token, sign in on the [API access page]($apiDocsUrl) and submit a request describing
who you are and how you plan to use the API. An administrator reviews requests before issuing a token.

## Access Tiers

Usage without a token is free but rate-limited. Token-based access is available after a brief token request form.

| Tier | Who it's for | Token required? | Usage |
|---|---|---|---|
| **Public** | Anyone — no sign-up required | ❌ No | Limited |
{$tierRows}

All token-based tiers are granted through the token request process and are free of charge.
For questions about tokens or usage, [contact us]($contactUrl).

## Terms and licensing

Use of the ROSSA API is subject to the repository [terms and licensing agreement]($termsUrl),
including attribution requirements and restrictions on commercial use. Review the agreement before
requesting a token or integrating the API into your work.

## Pagination

All list endpoints return a standard Laravel paginated envelope:

```json
{
  "data": [...],
  "links": { "first": "...", "last": "...", "prev": null, "next": "..." },
  "meta": { "current_page": 1, "last_page": 4, "per_page": 25, "total": 82 }
}
```

Use `?per_page=N` to control page size. The maximum allowed value depends on your tier.

## Study Identifiers

Studies are referenced by their **ROSSA identifier** (e.g. `ROSSA-20250101-AB12`).
MD;
    }
}
