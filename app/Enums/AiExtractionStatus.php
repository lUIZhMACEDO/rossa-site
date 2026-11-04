<?php

namespace App\Enums;

enum AiExtractionStatus: string
{
    case Queued = 'queued';
    case Processing = 'processing';
    case Ready = 'ready';      // draft is available for the user to review
    case Failed = 'failed';
    case Applied = 'applied';  // user reviewed it and saved it into a study
}
