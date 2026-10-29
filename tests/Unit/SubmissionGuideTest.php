<?php

use App\Support\SubmissionGuide;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Tests\TestCase;

uses(TestCase::class);

test('study pages link to the matching guide section', function () {
    $cases = [
        'study.study-information' => 'section-1',
        'study.subject-areas' => 'section-2',
        'study.investigators' => 'section-3',
        'study.animal-experimentation.setup' => 'animal-experimentation-setup',
        'study.animal-experimentation.animal-models' => 'animal-models',
        'study.animal-experimentation.animal-models.edit' => 'animal-models',
        'study.animal-experimentation.drug-treatment' => 'drug-treatments',
        'study.animal-experimentation.drug-treatment.plan.create' => 'drug-administration-plan',
        'study.animal-experimentation.drug-treatment.plan.edit' => 'drug-administration-plan',
        'study.animal-experimentation.gonadectomy-procedure' => 'gonadectomy-procedure',
        'study.sample-groups' => 'sample-groups',
        'study.sample-groups.edit' => 'sample-groups',
        'study.comparison-groups.index' => 'comparison-groups',
        'study.comparison-groups.create' => 'comparison-groups',
        'study.comparison-groups.edit' => 'comparison-groups',
        'study.phenotype-analysis' => 'upload-data',
        'study.summary.information' => 'section-6',
        'study.summary.exploration' => 'report-data-exploration',
        'study.summary.download' => 'report-download',
        'study.submission' => 'study-submission',
    ];

    foreach ($cases as $routeName => $fragment) {
        $this->assertSame($fragment, SubmissionGuide::fragment(submissionGuideRequest($routeName)), $routeName);
        $this->assertSame(
            route('guides.data-ingestion').'#'.$fragment,
            SubmissionGuide::url(submissionGuideRequest($routeName)),
            $routeName
        );
    }
});

test('upload steps link to the matching upload subsection', function () {
    $cases = [
        '' => 'upload-data',
        '?step=0' => 'upload-data',
        '?step=1' => 'upload-map-measurements',
        '?step=2' => 'upload-group-samples',
        '?step=3' => 'upload-review',
        '?step=4' => 'upload-data',
        '?step=5' => 'upload-review',
    ];

    foreach ($cases as $query => $fragment) {
        $request = submissionGuideRequest('study.phenotype-analysis.upload', '/study/1/phenotype-analysis/upload/5'.$query);

        $this->assertSame($fragment, SubmissionGuide::fragment($request), $query === '' ? 'no step' : $query);
    }
});

test('pages outside study submission keep the generic guide link', function () {
    $this->assertNull(SubmissionGuide::fragment(submissionGuideRequest('login', '/login')));
    $this->assertNull(SubmissionGuide::url(submissionGuideRequest('login', '/login')));
});

test('guide table of contents targets existing sections', function () {
    $toc = file_get_contents(resource_path('views/guides/data-ingestion/index.blade.php'));
    preg_match_all('/href="#([^"]+)"/', $toc, $matches);
    $html = view('guides.data-ingestion._content')->render();

    $this->assertNotEmpty($matches[1]);

    foreach (array_unique($matches[1]) as $id) {
        $this->assertStringContainsString('id="'.$id.'"', $html, $id);
    }
});

test('guide sections exist for every submission link', function () {
    $html = view('guides.data-ingestion._content')->render();

    foreach ([
        'section-1',
        'section-2',
        'section-3',
        'section-4',
        'animal-experimentation-setup',
        'animal-models',
        'drug-treatments',
        'drug-administration-plan',
        'gonadectomy-procedure',
        'section-5',
        'sample-groups',
        'comparison-groups',
        'upload-data',
        'upload-map-measurements',
        'upload-group-samples',
        'upload-review',
        'section-6',
        'report-information',
        'report-data-exploration',
        'report-download',
        'study-submission',
        'form-sections',
    ] as $id) {
        $this->assertStringContainsString('id="'.$id.'"', $html, $id);
    }
});

function submissionGuideRequest(string $routeName, string $uri = '/study/1/section'): Request
{
    $request = Request::create($uri, 'GET');
    $route = new Route(['GET'], $uri, ['uses' => fn () => null]);
    $route->name($routeName);
    $request->setRouteResolver(fn () => $route);

    return $request;
}
