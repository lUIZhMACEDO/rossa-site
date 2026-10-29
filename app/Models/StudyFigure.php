<?php

namespace App\Models;

use App\Traits\LocksWithStudy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class StudyFigure extends Model
{
    use LocksWithStudy;

    public function isStudyLocked(): bool
    {
        $study = $this->getStudyForLock();

        return $study?->isPhenotypeAnalysisLocked(auth()->user()) ?? false;
    }

    protected $fillable = [
        'study_id',
        'path',
        'original_filename',
        'mime_type',
        'size',
        'caption',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function study()
    {
        return $this->belongsTo(Study::class);
    }

    public function deleteStoredFile(): void
    {
        if ($this->path && Storage::exists($this->path)) {
            Storage::delete($this->path);
        }

        $dir = dirname((string) $this->path);
        if ($dir && $dir !== '.' && Storage::directoryExists($dir) && Storage::files($dir) === []) {
            Storage::deleteDirectory($dir);
        }
    }
}
