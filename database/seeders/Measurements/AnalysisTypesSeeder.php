<?php

namespace Database\Seeders\Measurements;

use App\Enums\AnalysisTypeEnum;
use App\Models\AnalysisType;
use Illuminate\Database\Seeder;

class AnalysisTypesSeeder extends Seeder
{
    public function run()
    {
        foreach (AnalysisTypeEnum::cases() as $analysisTypeEnum) {
            AnalysisType::updateOrCreate(
                ['code' => $analysisTypeEnum->value],
                [
                    'name' => $analysisTypeEnum->name(),
                    'abbreviation' => $analysisTypeEnum->shorthand(),
                    'description' => $analysisTypeEnum->descriptions(),
                ]
            );
        }
    }
}
