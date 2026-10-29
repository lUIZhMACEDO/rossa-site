<?php

namespace App\Services;

use App\Enums\CoreFacilityStatus;
use App\Mail\CoreFacilityApprovalEmail;
use App\Mail\CoreFacilityRejectionEmail;
use App\Models\CoreFacility;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;

class CoreFacilityService
{
    // STATUS UPDATES
    public function accept(CoreFacility $facility): void
    {
        // must be admin
        if (! Gate::check('manage-facilities')) {
            throw new Exception('Not authorized.');
        }

        // check if pending
        if ($facility->status !== CoreFacilityStatus::PENDING) {
            throw new Exception('Facility must be pending to be accepted.');
        }

        $facility->status = CoreFacilityStatus::ACCEPTED;
        $facility->reviewed_at = now();
        $facility->reviewer_id = Auth::id();
        $facility->expires_at = now()->addYear()->startOfDay();
        $facility->rejection_reason = null;
        $facility->save();

        // notify of acceptance
        $user = $facility->createdBy;
        if ($user) {
            Mail::to($user->email)->send(new CoreFacilityApprovalEmail($facility->id));
        }
    }

    public function reject(CoreFacility $facility, ?string $reason = null): void
    {
        // must be admin
        if (! Gate::check('manage-facilities')) {
            throw new Exception('Not authorized.');
        }

        // check if pending
        if ($facility->status !== CoreFacilityStatus::PENDING) {
            throw new Exception('Facility must be pending to be rejected.');
        }

        $facility->status = CoreFacilityStatus::REJECTED;
        $facility->reviewed_at = now();
        $facility->reviewer_id = Auth::id();
        $facility->expires_at = null;
        $facility->rejection_reason = $reason;
        $facility->save();

        // notify of rejection
        $user = $facility->createdBy;
        if ($user) {
            Mail::to($user->email)->send(new CoreFacilityRejectionEmail($facility->id));
        }
    }

    public function markAsPending(CoreFacility $facility): void
    {
        if (! Gate::check('manage-facilities')) {
            throw new Exception('Not authorized.');
        }

        if (! in_array($facility->status, [CoreFacilityStatus::ACCEPTED, CoreFacilityStatus::REJECTED])) {
            throw new Exception('Facility must be accepted or rejected to be marked as pending.');
        }

        $facility->status = CoreFacilityStatus::PENDING;
        $facility->reviewed_at = now();
        $facility->reviewer_id = Auth::id();
        $facility->expires_at = null;
        $facility->save();
    }

    // refresh route in refresh email
    public function refresh(CoreFacility $facility): void
    {
        // must be accepted or expired
        if (! in_array($facility->status, [CoreFacilityStatus::ACCEPTED, CoreFacilityStatus::EXPIRED])) {
            throw new Exception('Facility must be accepted or expired to be refreshed.');
        }

        // if expired, set to accepted
        if ($facility->status === CoreFacilityStatus::EXPIRED) {
            $facility->status = CoreFacilityStatus::ACCEPTED;
        }

        // set expiration and refresh date
        $facility->expires_at = now()->addYear()->startOfDay();
        $facility->last_refreshed_at = now();
        $facility->save();
    }

    // delete
    public function delete(CoreFacility $facility): void
    {
        // only admin can delete
        if (! Gate::check('manage-facilities')) {
            throw new Exception('Not authorized.');
        }

        $facility->delete();
    }
}
