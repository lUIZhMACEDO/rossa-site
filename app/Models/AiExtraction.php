<?php

namespace App\Models;

use App\Enums\AiExtractionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiExtraction extends Model
{
    protected $fillable = [
        'user_id',
        'study_id',
        'disk',
        'pdf_path',
        'original_filename',
        'status',
        'schema_version',
        'model',
        'extracted',
        'evidence',
        'usage',
        'error',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => AiExtractionStatus::class,
            'extracted' => 'array',
            'evidence' => 'array',
            'usage' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function study(): BelongsTo
    {
        return $this->belongsTo(Study::class);
    }
}
