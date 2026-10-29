<?php

namespace App\Enums;

enum StudyStatus: string
{
    // study status workflow
    // 1. onCreate -> In Progress
    // 2. onSubmission -> Pending
    // 3. onWithdraw -> In Progress
    // 4. onApprove -> Approved
    // 5. onReject -> Rejected
    // 6. onDelete -> Deleted

    case IN_PROGRESS = 'in_progress';
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case DELETED = 'deleted';

    public function label(): string
    {
        return match ($this) {
            self::IN_PROGRESS => 'In Progress',
            self::PENDING => 'Pending',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Requires Revisions',
            self::DELETED => 'Deleted',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::IN_PROGRESS => 'secondary',
            self::PENDING => 'warning',
            self::APPROVED => 'success',
            self::REJECTED => 'danger',
            self::DELETED => 'dark',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::IN_PROGRESS => 'This study is currently being worked on and can be edited.',
            self::PENDING => 'This study has been submitted for review and is awaiting approval.',
            self::APPROVED => 'This study has been approved and is publicly available.',
            self::REJECTED => 'This study requires revisions before resubmission.',
            self::DELETED => 'This study has been marked as deleted.',
        };
    }

    public function nextStatuses(): array
    {
        return match ($this) {
            // can be submitted for approval
            self::IN_PROGRESS => [self::PENDING],
            // can be withdrawn back to in-progress, or reviewed
            self::PENDING => [self::IN_PROGRESS, self::APPROVED, self::REJECTED],
            // can be deleted
            self::APPROVED => [self::DELETED],
            // can be edited and resubmitted
            self::REJECTED => [self::PENDING],
            self::DELETED => [],
        };
    }
}
