<?php

namespace App\Http\Controllers;

use App\Enums\StudyStatus;
use App\Mail\StudyApprovalRequestEmail;
use App\Models\Study;
use App\Services\StudyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

class StudySubmissionController extends Controller
{
    public function __construct(private StudyService $studyService) {}

    public function show(Study $study)
    {
        return view('study.submission', compact('study'));
    }

    // store
    public function submitStudy(Request $request, Study $study)
    {
        abort_unless(
            $request->user()->canSubmitStudy($study),
            403,
            'Only the corresponding investigator can submit this study. Ask them to review and submit it.'
        );
        $request->validate([
            'attest_accuracy' => 'required|accepted',
        ]);

        $validationErrors = $this->studyService->submit($study, $request->user());

        if (! empty($validationErrors)) {
            return redirect()
                ->route('study.submission', ['study' => $study->id])
                ->with('validationErrors', $validationErrors);
        }

        return redirect()
            ->route('study.submission', ['study' => $study->id])
            ->with('success', 'Study submitted successfully for approval.');
    }

    public function requestApproval(Request $request, Study $study)
    {
        $user = $request->user();

        // Standalone studies only; collection studies are submitted by the owner.
        abort_if($study->collection_id !== null, 404);
        // Editors (contributors / owner who is not the CI) may nudge the CI to submit.
        abort_unless($user->canEditStudy($study), 403, 'Unauthorized action.');
        abort_if($user->canSubmitStudy($study), 403, 'You can submit this study yourself.');
        abort_unless(
            in_array($study->status, [StudyStatus::IN_PROGRESS, StudyStatus::REJECTED], true),
            403,
            'This study is not open for submission.'
        );

        $validationErrors = $study->getIncompleteRequirements();
        if (! empty($validationErrors)) {
            return redirect()
                ->route('study.submission', ['study' => $study->id])
                ->with('validationErrors', $validationErrors);
        }

        $recipients = $study->investigators()
            ->where('corresponding', true)
            ->pluck('email')
            ->filter()
            ->map(fn ($email) => strtolower(trim($email)))
            ->unique();

        if ($recipients->isEmpty()) {
            return back()->with('error', 'This study does not have a corresponding investigator yet. Add one on the Investigators page.');
        }

        // One request per hour per study, so the investigator is not flooded.
        if (! Cache::add('study-approval-request:'.$study->id, true, now()->addHour())) {
            return back()->with('error', 'A request was already sent in the last hour.');
        }

        foreach ($recipients as $email) {
            Mail::to($email)->send(new StudyApprovalRequestEmail($study->id, $user->id, $email));
        }

        return redirect()
            ->route('study.submission', ['study' => $study->id])
            ->with('success', 'The corresponding investigator has been asked to review and submit this study.');
    }
}
