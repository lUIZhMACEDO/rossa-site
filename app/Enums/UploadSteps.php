<?php

namespace App\Enums;

enum UploadSteps: int
{
    case PREPARE_UPLOAD = 0;
    case MAP_MEASUREMENTS = 1;
    case GROUP_SAMPLES = 2;
    case REVIEW = 3;
    case SCAN_DATASETS = 4;
    case COMPLETE = 5;

    public function label(): string
    {
        return match ($this) {
            UploadSteps::PREPARE_UPLOAD => 'Prepare Upload',
            UploadSteps::MAP_MEASUREMENTS => 'Map Measurements',
            UploadSteps::GROUP_SAMPLES => 'Group Samples',
            UploadSteps::REVIEW => 'Review',
            UploadSteps::SCAN_DATASETS => 'Scan Datasets',
            UploadSteps::COMPLETE => 'Complete',
        };
    }
}
