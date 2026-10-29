<?php

namespace App\Support;

use App\Enums\UploadSteps;
use Illuminate\Http\Request;

class SubmissionGuide
{
    /**
     * Help-guide fragment for the current study submission page.
     * Null when the page has no matching section.
     */
    public static function fragment(?Request $request = null): ?string
    {
        $request ??= request();
        $name = $request->route()?->getName();

        if ($name === null) {
            return null;
        }

        return match (true) {
            $name === 'study.study-information',
            $name === 'study.study-information.store' => 'section-1',
            $name === 'study.subject-areas' => 'section-2',
            $name === 'study.investigators',
            $name === 'study.save-investigators' => 'section-3',
            str_starts_with($name, 'study.animal-experimentation.setup') => 'animal-experimentation-setup',
            str_starts_with($name, 'study.animal-experimentation.animal-models') => 'animal-models',
            str_starts_with($name, 'study.animal-experimentation.drug-treatment.plan') => 'drug-administration-plan',
            $name === 'study.animal-experimentation.drug-treatment' => 'drug-treatments',
            $name === 'study.animal-experimentation.gonadectomy-procedure' => 'gonadectomy-procedure',
            str_starts_with($name, 'study.sample-groups') => 'sample-groups',
            str_starts_with($name, 'study.comparison-groups') => 'comparison-groups',
            $name === 'study.phenotype-analysis',
            $name === 'study.phenotype-analysis.store',
            $name === 'study.phenotype-analysis.destroy' => 'upload-data',
            str_starts_with($name, 'study.phenotype-analysis.upload'),
            str_starts_with($name, 'study.phenotype-analysis.update') => self::uploadFragment($request),
            $name === 'study.summary.exploration' => 'report-data-exploration',
            $name === 'study.summary.download' => 'report-download',
            $name === 'study.summary',
            $name === 'study.summary.information' => 'section-6',
            $name === 'study.submission',
            $name === 'study.submission.submit' => 'study-submission',
            default => null,
        };
    }

    public static function url(?Request $request = null): ?string
    {
        $fragment = self::fragment($request);

        if ($fragment === null) {
            return null;
        }

        return route('guides.data-ingestion').'#'.$fragment;
    }

    private static function uploadFragment(Request $request): string
    {
        $step = (int) $request->query('step', UploadSteps::PREPARE_UPLOAD->value);

        return match ($step) {
            UploadSteps::MAP_MEASUREMENTS->value => 'upload-map-measurements',
            UploadSteps::GROUP_SAMPLES->value => 'upload-group-samples',
            UploadSteps::REVIEW->value, UploadSteps::COMPLETE->value => 'upload-review',
            default => 'upload-data',
        };
    }
}
