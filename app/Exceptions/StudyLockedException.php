<?php

namespace App\Exceptions;

use RuntimeException;

class StudyLockedException extends RuntimeException
{
    public function __construct(string $message = 'This study is locked and cannot be modified.')
    {
        parent::__construct($message);
    }
}
