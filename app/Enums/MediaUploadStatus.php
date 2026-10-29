<?php

namespace App\Enums;

enum MediaUploadStatus: string
{
    case Pending = 'pending';
    case Uploading = 'uploading';
    case Scanning = 'scanning';
    case Complete = 'complete';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Uploading => 'Uploading',
            self::Scanning => 'Scan running',
            self::Complete => 'Complete',
            self::Failed => 'Failed',
        };
    }
}
