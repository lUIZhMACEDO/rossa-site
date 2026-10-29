<?php

namespace App\Services;

use App\Models\AnalysisUpload;
use App\Models\Measurement;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AnalysisUploadService
{
    protected const SAMPLE_ROW_COUNT = 10;

    protected const MAX_ROWS_TO_READ = 1000;

    protected AnalysisUpload $analysisUpload;

    public array $rows;

    public $headerRowIndex;

    public Collection $measurements;

    public $columnSummary = [];

    public function __construct(AnalysisUpload $analysisUpload)
    {
        $this->analysisUpload = $analysisUpload;
        $this->rows = [];

        // get measurements for this analysis type
        // important to filter here for mapping
        if ($analysisUpload->analysisType) {
            $measurements = Measurement::where('analysis_type_id', $analysisUpload->analysis_type_id);

            // filter by specimen type if set
            if ($analysisUpload->specimen_type_id) {
                $measurements->where(function ($query) use ($analysisUpload) {
                    $query->where('specimen_type_id', $analysisUpload->specimen_type_id)
                        ->orWhereNull('specimen_type_id');
                });
            }

            // filter by region if set
            if ($analysisUpload->region_id) {
                $measurements->where(function ($query) use ($analysisUpload) {
                    $query->whereHas('regions', function ($q) use ($analysisUpload) {
                        $q->where('regions.id', $analysisUpload->region_id);
                    })
                        ->orWhereDoesntHave('regions');
                });
            }

            // filter by grouping for mechanical testing
            if ($analysisUpload->analysisType->code === 'mechanical_testing' && $analysisUpload->test_method) {
                // map test method to grouping
                $grouping = match ($analysisUpload->test_method) {
                    'bending_3pt', 'bending_4pt' => 'Bending',
                    'torsion' => 'Torsion',
                    'compression' => 'Compression',
                    default => null,
                };

                if ($grouping) {
                    // general measurements merged below
                    $measurements->where('measurements.grouping', $grouping);
                }
            }

            $measurements = $measurements->get();
        } else {
            $measurements = collect();
        }
        $measurements = $measurements->merge(Measurement::whereNull('analysis_type_id')->get());

        $this->measurements = $measurements;
    }

    /**
     * Simple way to determine if the file in question will pose any issue with read.
     */
    public function existsAndReadable(): bool
    {
        $path = $this->analysisUpload->path;

        return $path && Storage::exists($path) && is_readable(Storage::path($path));
    }

    /**
     * Sample up to the first SAMPLE_ROW_COUNT rows from the file.
     */
    public function rowSampling()
    {
        $path = $this->analysisUpload->path;
        if (! $this->existsAndReadable()) {
            return [];
        }

        $fh = fopen(Storage::path($path), 'r');
        if (! $fh) {
            return [];
        }

        $rows = [];
        while (($row = fgetcsv($fh)) !== false) {

            // Trim whitespace from rows
            $rows[] = array_map(function ($v) {
                return is_string($v) ? trim(preg_replace('/^\xEF\xBB\xBF/', '', $v)) : $v;
            }, $row);
            if (count($rows) >= self::SAMPLE_ROW_COUNT) {
                break;
            }
        }
        fclose($fh);

        return $rows;
    }

    /**
     * Detect the header row index in the uploaded file.
     */
    public function detectHeaderRowIndex()
    {
        // $availableMeasurementSynonyms = $this->measurements->flatMap(function($measurement) {
        //     return $measurement->synonyms;
        // })->map(function($synonym) {
        //     return strtolower($synonym);
        // })->toArray();

        // if ( $this->rows === [] ) {
        //     $this->rows = $this->rowSampling();
        // }

        // foreach($this->rows as $index => $row) {
        //     foreach($row as $cell) {
        //         if ( in_array( strtolower($cell), $availableMeasurementSynonyms) ) {
        //             $this->headerRowIndex = $index;
        //             return $index;
        //         }
        //     }
        // }

        // $this->headerRowIndex = null;
        // return null;

        $sampleIdentifierMeasurement = Measurement::sampleIdentifier();

        if ($this->rows === []) {
            $this->rows = $this->rowSampling();
        }

        foreach ($this->rows as $index => $row) {
            foreach ($row as $cell) {
                if ($sampleIdentifierMeasurement->matchesSynonym($cell)) {
                    $this->headerRowIndex = $index;

                    return $index;
                }
            }
        }

        $this->headerRowIndex = null;

        return null;
    }

    public function setHeaderIndex($index)
    {
        $this->headerRowIndex = $index;

        return $this->headerRowIndex;
    }

    public function verifySampleNameAnchor()
    {
        if ($this->rows === []) {
            $this->rows = $this->rowSampling();
        }

        foreach ($this->rows as $index => $row) {
            foreach ($row as $cell) {
                if (stripos($cell, 'sample name') !== false) {
                    return true;
                }
            }
        }

        return false;
    }

    public function determineColumnCount()
    {
        if ($this->rows === []) {
            $this->rows = $this->rowSampling();
        }

        $maxColumns = 0;
        foreach ($this->rows as $row) {
            $maxColumns = max($maxColumns, count($row));
        }

        return $maxColumns;
    }

    public function generateColumnSummary()
    {
        if ($this->columnSummary !== []) {
            return $this->columnSummary;
        }

        $this->detectHeaderRowIndex();

        $columnCount = $this->determineColumnCount();
        $summary = [];

        for ($colIndex = 0; $colIndex < $columnCount; $colIndex++) {

            $sampleValues = collect();
            foreach ($this->rows as $row) {
                if (isset($row[$colIndex])) {
                    $sampleValues[] = $row[$colIndex];
                }
            }

            // Infer type (simple inference based on sample values)
            $inferredType = 'string';
            if (count(array_filter($sampleValues->values()->toArray(), 'is_numeric')) > (count($sampleValues) / 2)) {
                $inferredType = 'numeric';
            }

            // Extract the header value from the expected header row, if available
            $headerValue = $this->headerRowIndex !== null && isset($this->rows[$this->headerRowIndex][$colIndex]) ?
                $this->rows[$this->headerRowIndex][$colIndex] :
                null;

            $headerValueLower = $headerValue ? Str::lower($headerValue) : '';

            // Determine a measurement to autofill based on header value
            $autofillMeasurement = $this->findMatchingMeasurement($headerValueLower, $sampleValues);

            $summary[] = [
                'column_index' => $colIndex,
                'headerContent' => $headerValue,
                'inferred_type' => $inferredType,
                'sample_values' => $sampleValues->slice(0, self::SAMPLE_ROW_COUNT)->filterBom()->filter()->unique(),
                'autofill_measurement' => $autofillMeasurement,
            ];
        }
        $this->columnSummary = $summary;

        return $this->columnSummary;
    }

    protected function findMatchingMeasurement($headerValueLower, $sampleValues)
    {
        // first check - case insensitive
        $exactMatch = $this->measurements->filter(function ($measurement) use ($headerValueLower) {
            $synonyms = array_map('strtolower', $measurement->synonyms ?? []);

            return in_array($headerValueLower, $synonyms);
        })->first();

        if ($exactMatch) {
            return $exactMatch;
        }

        // second check - replace punctuation with spaces
        // keeps greek letters
        $spaceNormalized = preg_replace('/[\s_\-\/\(\)\[\]]+/', ' ', $headerValueLower);
        $secondMatch = $this->measurements->filter(function ($measurement) use ($spaceNormalized) {
            $synonyms = $measurement->synonyms ?? [];
            foreach ($synonyms as $synonym) {
                $normalizedSynonym = preg_replace('/[\s_\-\/\(\)\[\]]+/', ' ', strtolower($synonym));
                if ($normalizedSynonym === $spaceNormalized) {
                    return true;
                }
            }

            return false;
        })->first();

        if ($secondMatch) {
            return $secondMatch;
        }

        // third check - strip anything non-alphanumeric
        $fullyNormalized = preg_replace('/[^a-z0-9]+/', '', strtolower($headerValueLower));
        $thirdMatch = $this->measurements->filter(function ($measurement) use ($fullyNormalized) {
            $synonyms = $measurement->synonyms ?? [];
            foreach ($synonyms as $synonym) {
                if (preg_replace('/[^a-z0-9]+/', '', strtolower($synonym)) === $fullyNormalized) {
                    return true;
                }
            }

            return false;
        })->first();

        if ($thirdMatch) {
            return $thirdMatch;
        }

        // fourth check - value match
        if ($sampleValues) {
            $valueMatch = $this->measurements->filter(function ($measurement) use ($sampleValues) {
                $synonyms = array_map('strtolower', $measurement->synonyms ?? []);
                $sampleValuesLower = $sampleValues->map(fn ($v) => Str::lower($v))->toArray();

                return array_intersect($sampleValuesLower, $synonyms) !== [];
            })->first();

            return $valueMatch;
        }

        return null;
    }

    public function columnMappingArray()
    {
        return collect($this->generateColumnSummary())->mapWithKeys(function ($col) {
            return [$col['column_index'] => $col['autofill_measurement'] ? $col['autofill_measurement']->id : null];
        })->toArray();
    }

    public function iterateRows(callable $callback, ?int $maxRows = null, bool $skipHeader = false): int
    {

        $path = $this->analysisUpload->path;
        if (! $this->existsAndReadable()) {
            return 0;
        }

        $max = $maxRows ?? self::MAX_ROWS_TO_READ;
        $fh = fopen(Storage::path($path), 'r');
        if (! $fh) {
            return 0;
        }

        // Read up to $max rows into memory
        $buffer = [];
        while (count($buffer) < $max && ($row = fgetcsv($fh)) !== false) {
            // Trim whitespace from rows
            $buffer[] = array_map(function ($v) {
                return is_string($v) ? trim(preg_replace('/^\xEF\xBB\xBF/', '', $v)) : $v;
            }, $row);
        }
        $columnCount = $this->determineColumnCount();

        // Ensure each yielded row has exactly $columnCount entries (pad with null or trim)
        $processed = 0;
        foreach ($buffer as $index => $row) {
            if ($skipHeader && $this->headerRowIndex !== null && $index === $this->headerRowIndex) {
                continue;
            }

            $len = count($row);
            if ($len < $columnCount) {
                $row = array_merge($row, array_fill(0, $columnCount - $len, null));
            } elseif ($len > $columnCount) {
                $row = array_slice($row, 0, $columnCount);
            }

            $callback($index, $row);
            $processed++;
        }

        return $processed;
    }
}
