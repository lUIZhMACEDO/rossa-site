<?php

use App\Services\Ai\PaperExtractionService;
use App\Services\Ai\StudyExtractionSchema;
use Illuminate\Support\Facades\Http;

function fakeClaudeToolResponse(array $input): array
{
    return [
        'model' => 'claude-test',
        'content' => [[
            'type' => 'tool_use',
            'id' => 'toolu_test',
            'name' => StudyExtractionSchema::TOOL_NAME,
            'input' => $input,
        ]],
        'usage' => ['input_tokens' => 1000, 'output_tokens' => 200],
    ];
}

beforeEach(function () {
    config([
        'services.anthropic.key' => 'test-key',
        'services.anthropic.model' => 'claude-test',
    ]);
});

it('sends the pdf to claude and returns a validated draft', function () {
    Http::fake([
        'api.anthropic.com/*' => Http::response(fakeClaudeToolResponse([
            'title' => 'Sclerostin loss increases bone mass in mice',
            'summary' => 'We studied bone mass in Sost knockout mice.',
            'species' => 'mouse',
            'investigators' => [
                ['first_name' => 'Ada', 'last_name' => 'Lovelace', 'corresponding' => true, 'email' => 'ada@uconn.edu'],
            ],
            'evidence' => [
                'title' => ['page' => 1, 'quote' => 'Sclerostin loss increases bone mass', 'confidence' => 'high'],
            ],
        ])),
    ]);

    $result = app(PaperExtractionService::class)->extract('%PDF-1.4 fake');

    expect($result['extracted']['title'])->toBe('Sclerostin loss increases bone mass in mice')
        ->and($result['extracted']['investigators'])->toHaveCount(1)
        ->and($result['extracted'])->not->toHaveKey('evidence')
        ->and($result['evidence']['title']['page'])->toBe(1)
        ->and($result['usage']['input_tokens'])->toBe(1000);

    Http::assertSent(function ($request) {
        $doc = $request['messages'][0]['content'][0];

        return $request->hasHeader('x-api-key', 'test-key')
            && $doc['type'] === 'document'
            && $doc['source']['media_type'] === 'application/pdf'
            && $request['tool_choice']['name'] === StudyExtractionSchema::TOOL_NAME;
    });
});

it('rejects files that are not pdfs', function () {
    Http::fake();

    expect(fn () => app(PaperExtractionService::class)->extract('hello'))
        ->toThrow(RuntimeException::class, 'does not look like a PDF');

    Http::assertNothingSent();
});

it('fails when the api key is missing', function () {
    config(['services.anthropic.key' => null]);

    expect(fn () => app(PaperExtractionService::class)->extract('%PDF-1.4'))
        ->toThrow(RuntimeException::class, 'ANTHROPIC_API_KEY');
});

it('rejects a draft with an invalid investigator email', function () {
    $body = fakeClaudeToolResponse([
        'title' => 'X',
        'summary' => 'Y',
        'investigators' => [
            ['first_name' => 'A', 'last_name' => 'B', 'corresponding' => false, 'email' => 'not-an-email'],
        ],
        'evidence' => [],
    ]);

    expect(fn () => app(PaperExtractionService::class)->parseResponse($body, 'claude-test'))
        ->toThrow(RuntimeException::class, 'failed validation');
});

it('errors when claude does not call the tool', function () {
    $body = ['content' => [['type' => 'text', 'text' => 'Sorry']]];

    expect(fn () => app(PaperExtractionService::class)->parseResponse($body, 'claude-test'))
        ->toThrow(RuntimeException::class, 'expected tool call');
});
