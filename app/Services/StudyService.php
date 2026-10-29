<?php

namespace App\Services;

use App\Enums\PermissionType;
use App\Enums\StudyStatus;
use App\Mail\CollectionSubmissionAdminNotificationEmail;
use App\Mail\StudyApprovalEmail;
use App\Mail\StudyRejectionEmail;
use App\Mail\StudySubmissionAdminNotificationEmail;
use App\Mail\StudySubmissionEmail;
use App\Models\Collection;
use App\Models\Study;
use App\Models\User;
use Exception;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class StudyService
{
    public const ABANDONED_DRAFT_DAYS = 90;

    public function submit(Study $study, User $actor, bool $sendNotifications = true): array
    {
        $errors = [];

        // authorization: corresponding investigators and ROSSA staff (see User::canSubmitStudy)
        if (! $actor->canSubmitStudy($study)) {
            $errors[] = [
                'message' => 'You are not authorized to submit this study.',
                'route' => null,
            ];

            return $errors;
        }

        // check if study can be submitted by status
        if (! in_array(StudyStatus::PENDING, $study->status->nextStatuses(), true)) {
            $errors[] = [
                'message' => 'Study cannot be submitted in its current status.',
                'route' => null,
            ];

            return $errors;
        }

        // validate by requirements
        $incompleteRequirements = $study->getIncompleteRequirements();
        if (! empty($incompleteRequirements)) {
            return $incompleteRequirements;
        }

        $study->status = StudyStatus::PENDING;
        $study->submitted_at = now();
        $study->submitted_by_user_id = $actor->id;
        $study->withdrawn_at = null;
        $study->reviewed_at = null;
        $study->reviewer_id = null;
        $study->rejection_reason = null;
        $study->approved_at = null;
        Study::withoutLocking(fn () => $study->save());

        if ($sendNotifications && $study->collection_id === null) {
            $uniqueEmailList = collect([$study->user->email])
                ->merge($study->investigators->pluck('email'))
                ->unique();

            $uniqueEmailList->each(function ($email) use ($study) {
                Mail::to($email)->send(new StudySubmissionEmail($study->id));
            });

            Mail::to(config('mail.rossa_admin_mass_email'))
                ->send(new StudySubmissionAdminNotificationEmail($study->id));
        }

        return [];
    }

    // No longer in use
    public function withdraw(Study $study, User $actor): void
    {
        if (! $study->isOwner($actor) && ! $actor->hasPermission(PermissionType::MANAGE_STUDIES->value)) {
            throw new Exception('Not authorized to withdraw this study submission.');
        }

        if ($study->status !== StudyStatus::PENDING) {
            throw new Exception('Only pending studies can be withdrawn.');
        }

        $study->status = StudyStatus::IN_PROGRESS;
        $study->withdrawn_at = now();
        Study::withoutLocking(fn () => $study->save());
    }

    public function approve(Study $study, User $reviewer): void
    {
        if (! $reviewer->hasPermission(PermissionType::MANAGE_STUDIES->value)) {
            throw new Exception('Not authorized to approve studies.');
        }

        if ($study->status !== StudyStatus::PENDING) {
            throw new Exception('Study must be pending to be approved.');
        }

        $this->markApproved($study, $reviewer);
    }

    private function markApproved(Study $study, User $reviewer): void
    {

        $study->status = StudyStatus::APPROVED;
        $study->reviewed_at = now();
        $study->reviewer_id = $reviewer->id;
        $study->approved_at = now();
        $study->rejection_reason = null;
        Study::withoutLocking(fn () => $study->save());

        if ($study->collection_id !== null) {
            return;
        }

        $uniqueEmailList = collect([$study->user->email])
            ->merge($study->investigators->pluck('email'))
            ->unique();

        $uniqueEmailList->each(function ($email) use ($study) {
            Mail::to($email)->send(new StudyApprovalEmail($study->id));
        });
    }

    public function reject(Study $study, User $reviewer, ?string $reason = null): void
    {
        if (! $reviewer->hasPermission(PermissionType::MANAGE_STUDIES->value)) {
            throw new Exception('Not authorized to reject studies.');
        }

        if ($study->status !== StudyStatus::PENDING) {
            throw new Exception('Study must be pending to be rejected.');
        }

        $study->status = StudyStatus::REJECTED;
        $study->reviewed_at = now();
        $study->reviewer_id = $reviewer->id;
        $study->approved_at = null;
        $study->rejection_reason = $reason;
        Study::withoutLocking(fn () => $study->save());

        if ($study->collection_id !== null) {
            return;
        }

        $study->teamEmails()->each(function ($email) use ($study) {
            Mail::to($email)->send(new StudyRejectionEmail($study->id));
        });
    }

    public function deleteOwnedStudy(Study $study, User $actor): void
    {
        if (! $study->isOwner($actor)) {
            throw new AuthorizationException('Not authorized to delete this study.');
        }

        // Owners may remove even submitted or approved studies. Preserve the
        // status and related records so the study can be restored later.
        Study::withoutLocking(fn () => $study->delete());
    }

    public function deleteAbandonedDraft(Study $study, User $actor, int $days = self::ABANDONED_DRAFT_DAYS): void
    {
        if (! $actor->hasPermission(PermissionType::MANAGE_STUDIES->value)) {
            throw new Exception('Not authorized to delete abandoned studies.');
        }

        if (! $study->isAbandonedDraft($days)) {
            throw new Exception('Study is not eligible for abandoned-study cleanup.');
        }

        $study->delete();
    }

    public function getCollectionIncompleteRequirements(Collection $collection): array
    {
        $report = [];
        foreach ($collection->studies as $study) {
            $incomplete = $study->getIncompleteRequirements();
            if (! empty($incomplete)) {
                $report[$study->id] = $incomplete;
            }
        }

        return $report;
    }

    public function firstErrorMessage(array $errors, string $fallback = 'This collection could not be submitted.'): string
    {
        foreach ($errors as $error) {
            if (is_array($error) && isset($error['message']) && is_string($error['message'])) {
                return $error['message'];
            }

            if (is_array($error)) {
                $nested = $this->firstErrorMessage($error, '');
                if ($nested !== '') {
                    return $nested;
                }
            }
        }

        return $fallback;
    }

    public function submitCollection(Collection $collection, User $actor): array
    {
        if (! $collection->canUserSubmit($actor)) {
            return [['message' => 'Not authorized to submit this collection.', 'route' => null]];
        }

        if ($collection->status !== 'draft') {
            return [['message' => 'Only draft collections can be submitted.', 'route' => null]];
        }

        $collection->load('studies');

        if ($collection->studies->isEmpty()) {
            return [['message' => 'A collection must contain at least one study before it can be submitted.', 'route' => null]];
        }

        $submittable = $collection->studies->filter(
            fn (Study $study) => in_array($study->status, [StudyStatus::IN_PROGRESS, StudyStatus::REJECTED], true)
        );

        $incomplete = [];
        foreach ($submittable as $study) {
            $studyIncomplete = $study->getIncompleteRequirements();
            if (! empty($studyIncomplete)) {
                $incomplete[$study->id] = $studyIncomplete;
            }
        }
        if (! empty($incomplete)) {
            return $incomplete;
        }

        try {
            DB::transaction(function () use ($collection, $submittable) {
                foreach ($submittable as $study) {
                    // Submit as the study's actual owner, so submit()'s internal
                    // isOwner() check passes without needing to touch that method.
                    // Suppress per-study emails; one collection-level admin notice follows.
                    $errors = $this->submit($study, $study->user, sendNotifications: false);
                    if (! empty($errors)) {
                        throw new Exception($this->firstErrorMessage($errors));
                    }
                }

                $collection->update(['status' => 'submitted']);
            });
        } catch (Exception $exception) {
            return [['message' => $exception->getMessage(), 'route' => null]];
        }

        try {
            Mail::to(config('mail.rossa_admin_mass_email'))
                ->send(new CollectionSubmissionAdminNotificationEmail($collection->id));
        } catch (\Throwable $exception) {
            report($exception);
        }

        return [];
    }

    public function getCollectionApprovalBlockers(Collection $collection): int
    {
        return $collection->studies()
            ->whereIn('status', [StudyStatus::IN_PROGRESS, StudyStatus::REJECTED])
            ->count();
    }

    public function canApproveCollection(Collection $collection): bool
    {
        if ($collection->status !== 'submitted') {
            return false;
        }

        return $this->getCollectionApprovalBlockers($collection) === 0;
    }

    public function approveCollectionStudies(
        Collection $collection,
        array $studyIds,
        User $reviewer
    ): int {
        if (! $reviewer->hasPermission(PermissionType::MANAGE_COLLECTIONS->value)) {
            throw new Exception('Not authorized to approve collection studies.');
        }

        if ($collection->status !== 'submitted') {
            throw new Exception('Studies can only be approved while the collection is awaiting review.');
        }

        $approved = 0;

        foreach ($collection->studies()->whereIn('id', $studyIds)->get() as $study) {
            if ($study->status !== StudyStatus::PENDING) {
                continue;
            }

            $this->markApproved($study, $reviewer);
            $approved++;
        }

        return $approved;
    }

    public function approveCollection(Collection $collection, User $reviewer): void
    {
        if (! $reviewer->hasPermission(PermissionType::MANAGE_COLLECTIONS->value)) {
            throw new Exception('Not authorized to approve collections.');
        }

        if ($collection->status !== 'submitted') {
            throw new Exception('Only submitted collections can be approved.');
        }

        $blockers = $this->getCollectionApprovalBlockers($collection);
        if ($blockers > 0) {
            throw new Exception("{$blockers} studies are not yet submitted and must be resolved before the collection can be approved.");
        }

        foreach ($collection->studies as $study) {
            if ($study->status === StudyStatus::PENDING) {
                $this->markApproved($study, $reviewer);

                continue;
            }

            if ($study->status !== StudyStatus::APPROVED) {
                throw new Exception('All studies in the collection must be submitted before approval.');
            }
        }

        $collection->update(['status' => 'approved']);
    }
}
