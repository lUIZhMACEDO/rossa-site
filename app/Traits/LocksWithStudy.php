<?php

namespace App\Traits;

use App\Exceptions\StudyLockedException;
use App\Models\Study;

trait LocksWithStudy
{
    protected static bool $bypassStudyLock = false;

    public static function bootLocksWithStudy(): void
    {
        static::saving(function ($model) {
            if (static::$bypassStudyLock) {
                return;
            }

            if ($model->isStudyLocked() && ! $model->isDirty('publication_plan')) {
                throw new StudyLockedException;
            }
        });

        static::deleting(function ($model) {
            if (static::$bypassStudyLock) {
                return;
            }

            if ($model->isStudyLocked()) {
                throw new StudyLockedException;
            }
        });
    }

    public function isStudyLocked(): bool
    {
        $study = $this->getStudyForLock();

        return $study?->isLockedForEditing(auth()->user()) ?? false;
    }

    protected function getStudyForLock(): ?Study
    {
        if (! method_exists($this, 'study')) {
            return null;
        }

        $this->loadMissing('study');
        $study = $this->getRelation('study');

        return $study instanceof Study ? $study : null;
    }

    public static function withoutLocking(callable $callback): mixed
    {
        $previous = static::$bypassStudyLock;
        static::$bypassStudyLock = true;

        try {
            return $callback();
        } finally {
            static::$bypassStudyLock = $previous;
        }
    }
}
