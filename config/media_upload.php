<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Chunk size (bytes)
    |--------------------------------------------------------------------------
    |
    | Each chunk request must stay under PHP upload/post limits (100M).
    |
    */
    'chunk_size' => (int) env('MEDIA_UPLOAD_CHUNK_SIZE', 5 * 1024 * 1024),

    /*
    |--------------------------------------------------------------------------
    | Maximum file size (bytes)
    |--------------------------------------------------------------------------
    */
    'max_file_size' => (int) env('MEDIA_UPLOAD_MAX_FILE_SIZE', 20 * 1024 * 1024 * 1024),

    /*
    |--------------------------------------------------------------------------
    | Maximum total storage per study (bytes)
    |--------------------------------------------------------------------------
    */
    'max_study_bytes' => (int) env('MEDIA_UPLOAD_MAX_STUDY_BYTES', 75 * 1024 * 1024 * 1024),

    /*
    |--------------------------------------------------------------------------
    | Allowed file extensions (Micro CT scan datasets)
    |--------------------------------------------------------------------------
    */
    'allowed_extensions' => ['isq', 'dcm', 'aim', 'zip'],

    /*
    |--------------------------------------------------------------------------
    | ClamAV malware scan
    |--------------------------------------------------------------------------
    */
    'malware_scan' => [
        'skip' => (bool) env('MEDIA_UPLOAD_SKIP_MALWARE_SCAN', false),
        'binary' => env('MEDIA_UPLOAD_CLAMD_SCAN_BINARY', 'clamdscan'),
        'window_size' => (int) env('MEDIA_UPLOAD_SCAN_WINDOW_SIZE', 2 * 1024 * 1024 * 1024),
        'overlap' => (int) env('MEDIA_UPLOAD_SCAN_OVERLAP', 512 * 1024 * 1024),
        'process_timeout' => (int) env('MEDIA_UPLOAD_SCAN_PROCESS_TIMEOUT', 1440),
    ],

];
