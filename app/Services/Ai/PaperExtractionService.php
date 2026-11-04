<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use RuntimeException;

/**
 * Sends a paper PDF to Claude and returns a draft of ROSSA form fields.
 *
 * The PDF is sent directly as a document block (no local PDF-to-text step):
 * Claude reads text, tables and layout itself.
 */
class PaperExtractionService
{
    private const API_URL = 'https://api.anthropic.com/v1/messages';

    private const API_VERSION = '2023-06-01';

    /**
     * @return array{extracted: array, evidence: array, usage: array, model: string}
     */
    public function extract(string $pdfBytes): array
    {
        $key = config('services.anthropic.key');
        if (blank($key)) {
            throw new RuntimeException('ANTHROPIC_API_KEY is not set.');
        }

        $maxBytes = (int) config('services.anthropic.max_pdf_mb', 20) * 1024 * 1024;
        if (strlen($pdfBytes) > $maxBytes) {
            throw new RuntimeException('PDF is larger than the configured limit.');
        }

        if (! str_starts_with($pdfBytes, '%PDF')) {
            throw new RuntimeException('File does not look like a PDF.');
        }

        $model = config('services.anthropic.model');

        $response = Http::withHeaders([
            'x-api-key' => $key,
            'anthropic-version' => self::API_VERSION,
        ])
            ->timeout(180)
            ->retry(2, 2000, throw: false)
            ->post(self::API_URL, [
                'model' => $model,
                'max_tokens' => 8000,
                'tools' => [StudyExtractionSchema::tool()],
                'tool_choice' => ['type' => 'tool', 'name' => StudyExtractionSchema::TOOL_NAME],
                'messages' => [[
                    'role' => 'user',
                    'content' => [
                        [
                            'type' => 'document',
                            'source' => [
                                'type' => 'base64',
                                'media_type' => 'application/pdf',
                                'data' => base64_encode($pdfBytes),
                            ],
                        ],
                        ['type' => 'text', 'text' => StudyExtractionSchema::instructions()],
                    ],
                ]],
            ]);

        if ($response->failed()) {
            $message = $response->json('error.message') ?? $response->body();
            throw new RuntimeException('Claude API error ('.$response->status().'): '.$message);
        }

        return $this->parseResponse($response->json(), $model);
    }

    /**
     * Pull the tool call out of the Messages API response and validate it.
     * Public so it can be unit-tested without hitting the API.
     */
    public function parseResponse(array $body, string $model): array
    {
        $toolUse = collect($body['content'] ?? [])
            ->first(fn ($block) => ($block['type'] ?? null) === 'tool_use'
                && ($block['name'] ?? null) === StudyExtractionSchema::TOOL_NAME);

        if (! $toolUse) {
            throw new RuntimeException('Claude did not return the expected tool call.');
        }

        $input = $toolUse['input'] ?? [];
        $evidence = $input['evidence'] ?? [];
        unset($input['evidence']);

        $validator = Validator::make($input, StudyExtractionSchema::draftRules());
        if ($validator->fails()) {
            throw new RuntimeException('AI draft failed validation: '.$validator->errors()->first());
        }

        return [
            'extracted' => $input,
            'evidence' => $evidence,
            'usage' => $body['usage'] ?? [],
            'model' => $body['model'] ?? $model,
        ];
    }
}
