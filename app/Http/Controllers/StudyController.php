<?php

namespace App\Http\Controllers;

use App\Enums\StudyStatus;
use App\Jobs\SendEmbargoReminderEmail;
use App\Mail\StudyContributorInvitationEmail;
use App\Models\Study;
use App\Models\StudyContributorInvitation;
use App\Models\User;
use App\Services\StudyService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class StudyController extends Controller
{
    public function __construct(private StudyService $studyService) {}

    public function store(Request $request)
    {
        $user = $request->user();
        $study = Study::create([
            'created_at' => now(),
            'status' => StudyStatus::IN_PROGRESS,
            'user_id' => $user?->id,
        ]);

        return redirect()->route('study.study-information', ['study' => $study->id]);
    }

    public function showInformation(Study $study)
    {
        return view('study.study-information', compact('study'));
    }

    public function createContributorInvitation(Request $request, Study $study)
    {
        $this->authorizeContributorManagement($request, $study);
        $validated = $request->validate(['email' => ['required', 'string', 'email', 'max:255']]);
        $email = strtolower(trim($validated['email']));

        // May be null: invitations can now go to people without a ROSSA account.
        $user = User::query()
            ->whereRaw('lower(email) = ?', [$email])
            ->first();

        if ($user?->isDisabled()) {
            return back()->withErrors(['email' => 'That ROSSA account is disabled.'])->withInput();
        }

        if ($user && $user->is($study->user)) {
            return back()->withErrors(['email' => 'The study owner already has access.'])->withInput();
        }

        if ($user && $study->contributors()->whereKey($user->id)->exists()) {
            return back()->withErrors(['email' => 'This user is already a contributor.'])->withInput();
        }

        // Mark stale pending invitations for this email as expired.
        $study->contributorInvitations()
            ->where('email', $email)
            ->where('status', StudyContributorInvitation::PENDING)
            ->where('expires_at', '<=', now())
            ->update(['status' => StudyContributorInvitation::EXPIRED]);

        if ($study->contributorInvitations()
            ->where('email', $email)
            ->where('status', StudyContributorInvitation::PENDING)
            ->exists()) {
            return back()->withErrors(['email' => 'This email already has a pending invitation.'])->withInput();
        }

        $token = Str::random(64);
        $invitation = $study->contributorInvitations()->create([
            'user_id' => $user?->id,
            'invited_by_user_id' => $request->user()->id,
            'email' => $email,
            'token_hash' => hash('sha256', $token),
            'status' => StudyContributorInvitation::PENDING,
            'expires_at' => now()->addDays(14),
        ]);

        Mail::to($email)->send(new StudyContributorInvitationEmail($invitation, $token));

        $message = $user
            ? "An invitation has been sent to {$user->display_name}."
            : "An invitation has been sent to {$email}. They will need to create a ROSSA account with this address to accept.";

        return back()->with('success', $message);
    }

    public function resendContributorInvitation(Request $request, Study $study, StudyContributorInvitation $invitation)
    {
        $this->authorizeContributorManagement($request, $study);
        abort_unless($invitation->study_id === $study->id, 404);

        if (! $invitation->isPending() || $invitation->isExpired() || $invitation->user?->isDisabled()) {
            return back()->withErrors(['invitation' => 'This invitation can no longer be resent.']);
        }

        $token = Str::random(64);
        $invitation->update([
            // Follow the account's current email if linked; otherwise keep the invited address.
            'email' => strtolower($invitation->user->email ?? $invitation->email),
            'token_hash' => hash('sha256', $token),
            'expires_at' => now()->addDays(14),
        ]);

        Mail::to($invitation->email)->send(new StudyContributorInvitationEmail($invitation, $token));

        $name = $invitation->user?->display_name ?: $invitation->email;

        return back()->with('success', "A fresh invitation has been sent to {$name}.");
    }

    public function respondToContributorInvitation(Request $request, StudyContributorInvitation $invitation, string $token)
    {
        $this->validateInvitationForRecipient($request, $invitation, $token);
        $validated = $request->validate(['response' => ['required', 'in:accept,decline']]);

        if ($validated['response'] === 'accept') {
            $invitation->study->contributors()->syncWithoutDetaching([$request->user()->id]);
            $invitation->update([
                // Links email-only invitations to the account that accepted them.
                'user_id' => $request->user()->id,
                'status' => StudyContributorInvitation::ACCEPTED,
                'responded_at' => now(),
            ]);

            return redirect()->route('study.summary', $invitation->study)
                ->with('success', 'You are now a contributor to this study.');
        }

        $invitation->update([
            'user_id' => $request->user()->id,
            'status' => StudyContributorInvitation::DECLINED,
            'responded_at' => now(),
        ]);

        return redirect()->route('my-studies')->with('success', 'You declined the collaboration invitation.');
    }

    private function validateInvitationForRecipient(Request $request, StudyContributorInvitation $invitation, string $token): void
    {
        abort_unless(hash_equals($invitation->token_hash, hash('sha256', $token)), 404);

        $user = $request->user();

        // Linked invitations must match the account. Email-only invitations
        // must match the signed-in user's verified email (these routes
        // require the 'verified' middleware).
        $matches = $invitation->user_id !== null
            ? $invitation->user_id === $user->id
            : strtolower($user->email) === $invitation->email;

        abort_unless(
            $matches,
            403,
            'This invitation was sent to a different email address. Sign in with that address to accept it.'
        );

        if ($invitation->isPending() && $invitation->isExpired()) {
            $invitation->update(['status' => StudyContributorInvitation::EXPIRED]);
        }

        abort_unless($invitation->isPending(), 410, 'This collaboration invitation is no longer available.');
    }

    public function revokeContributorInvitation(Request $request, Study $study, StudyContributorInvitation $invitation)
    {
        $this->authorizeContributorManagement($request, $study);
        abort_unless($invitation->study_id === $study->id, 404);

        if ($invitation->isPending()) {
            $invitation->update(['status' => StudyContributorInvitation::REVOKED, 'revoked_at' => now()]);
        }

        return back()->with('success', 'The collaboration invitation has been revoked.');
    }

    public function showContributorInvitation(Request $request, StudyContributorInvitation $invitation, string $token)
    {
        $this->validateInvitationForRecipient($request, $invitation, $token);
        $invitation->loadMissing(['study', 'inviter']);

        return view('study.contributor-invitation', compact('invitation', 'token'));
    }

    public function removeContributor(Request $request, Study $study, User $user)
    {
        $this->authorizeContributorManagement($request, $study);
        $study->contributors()->detach($user->id);

        return back()->with('success', "{$user->display_name}'s access has been removed.");
    }

    private function authorizeContributorManagement(Request $request, Study $study): void
    {
        abort_unless(
            $request->user()->canManageSharing($study),
            403,
            'Only the study owner, corresponding investigator, or ROSSA staff can manage who has access.'
        );
    }

    public function storeInformation(Request $request, Study $study)
    {
        // rules and messages for validation
        $rules = [
            'title' => 'required|string|max:255',
            'summary' => 'required|string',
            'funding_sources' => 'nullable|string',
            'conflicts' => 'nullable|string',
            'completion_date' => 'required|date',
            'is_published' => 'required|boolean',
            'doi' => 'nullable|regex:/^10\.\d{4,9}\/[-._;()\/:A-Z0-9]+$/i|required_if:is_published,1',
            'pubmed_id' => 'nullable|numeric|digits_between:1,10|required_if:is_published,1',
            'publication_plan' => 'nullable|string|required_if:is_published,0',
            'embargo_months' => 'nullable|integer|min:0|exclude_if:is_published,1|required_if:publication_plan,different_journal',
            'embargo_start_date' => 'nullable|date|exclude_if:is_published,1|required_if:publication_plan,different_journal',
        ];
        $messages = [
            'title.required' => 'Please enter a study title.',
            'summary.required' => 'Please enter a study summary.',
            'completion_date.required' => 'Please enter a study completion date.',
            'is_published.required' => 'Please indicate whether the study has been published.',
            'doi.required_if' => 'Please enter a Digital Object Identifier (DOI).',
            'doi.regex' => 'Please enter a valid DOI (format: 10. xxxxx/xxxxx).',
            'pubmed_id.required_if' => 'Please enter a PubMed ID.',
            'pubmed_id.numeric' => 'PubMed ID must be numeric.',
            'pubmed_id.digits_between' => 'PubMed ID must be between 1 and 10 digits.',
            'publication_plan.required_if' => 'Please select a publication plan.',
            'embargo_months.required_if' => 'Please enter an estimated embargo period.',
            'embargo_start_date.required_if' => 'Please enter the embargo start date.',
            'embargo_months.future_date' => 'The embargo reminder date must be in the future.',
        ];

        // validate
        $validator = Validator::make($request->all(), $rules, $messages);

        // validate embargo reminder is in the future
        $validator->after(function ($validator) use ($request, $study) {
            if (! $request->input('is_published') && $request->input('publication_plan') === 'different_journal') {
                $embargoStartDate = $study->submitted_at
                    ? $study->embargo_start_date?->format('Y-m-d')
                    : $request->input('embargo_start_date');
                $embargoMonths = $request->input('embargo_months');

                if ($embargoStartDate && $embargoMonths) {
                    $reminderDate = Carbon::parse($embargoStartDate)->addMonths((int) $embargoMonths);
                    if (! $reminderDate->isFuture()) {
                        $validator->errors()->add('embargo_months', 'The embargo reminder date must be in the future.');
                    }
                }
            }
        });

        $validated = $validator->validated();

        if ($study->submitted_at && $study->embargo_start_date) {
            $validated['embargo_start_date'] = $study->embargo_start_date->format('Y-m-d');
        }

        // Calculate embargo reminder date if embargo fields are present
        $reminderDate = null;
        $reminderSentAt = null;

        if (! $validated['is_published'] &&
            isset($validated['embargo_start_date']) &&
            isset($validated['embargo_months'])) {

            // temp set to calc reminder date
            $study->embargo_start_date = $validated['embargo_start_date'];
            $study->embargo_months = $validated['embargo_months'];
            $reminderDate = $study->calculateEmbargoReminderDate();

            // If reminder date changed, reset reminder sent date
            if ($reminderDate && $study->embargo_reminder_date && ! $reminderDate->eq($study->embargo_reminder_date)) {
                $reminderSentAt = null;
            } else {
                $reminderSentAt = $study->embargo_reminder_sent_at;
            }
        }

        // when we select something other than different journal, reset embargo data
        if (! $validated['is_published'] && $validated['publication_plan'] !== 'different_journal') {
            $validated['embargo_months'] = null;
            $validated['embargo_start_date'] = null;
            $validated['embargo_reminder_date'] = null;
            $validated['embargo_reminder_sent_at'] = null;
        }

        // update study
        $study->update([
            'title' => $validated['title'],
            'summary' => $validated['summary'],
            'funding_sources' => $validated['funding_sources'],
            'conflicts' => $validated['conflicts'],
            'completion_date' => $validated['completion_date'],
            'is_published' => $validated['is_published'],
            'doi' => $validated['is_published'] ? ($validated['doi'] ?? null) : null,
            'pubmed_id' => $validated['is_published'] ? ($validated['pubmed_id'] ?? null) : null,
            'publication_plan' => $validated['is_published'] ? null : ($validated['publication_plan'] ?? null),
            'embargo_months' => $validated['is_published'] ? null : ($validated['embargo_months'] ?? null),
            'embargo_start_date' => $validated['is_published'] ? null : ($validated['embargo_start_date'] ?? null),
            'embargo_reminder_date' => $validated['is_published'] ? null : $reminderDate,
            'embargo_reminder_sent_at' => $validated['is_published'] ? null : $reminderSentAt,
        ]);

        return redirect()->route('study.subject-areas', ['study' => $study->id])
            ->with('success', 'Study information updated successfully.');
    }

    // HTMX validation endpoints
    public function validateDoi(Request $request, Study $study)
    {
        $validator = Validator::make($request->all(), [
            'doi' => 'required|regex:/^10\.\d{4,9}\/[-._;()\/:A-Z0-9]+$/i',
        ], [
            'doi.required' => 'Please enter a Digital Object Identifier (DOI).',
            'doi.regex' => 'Please enter a valid DOI (format: 10. xxxxx/xxxxx).',
        ]);

        if ($validator->fails()) {
            return view('study.partials.url-validation', [
                'valid' => false,
                'message' => $validator->errors()->first('doi'),
            ]);
        }

        $doiUrl = $study->doiLink($request->input('doi'));

        try {
            $response = Http::withoutRedirecting()->timeout(10)->get($doiUrl);
            $status = $response->status();
            if ($status < 200 || $status >= 400) {
                return view('study.partials.url-validation', [
                    'valid' => false,
                    'message' => 'The provided DOI does not resolve to a valid URL.',
                ]);
            }
        } catch (Exception $e) {
            return view('study.partials.url-validation', [
                'valid' => false,
                'message' => 'The provided DOI is not valid.',
            ]);
        }

        return view('study.partials.url-validation', [
            'valid' => true,
            'message' => 'DOI URL looks valid.',
        ]);
    }

    public function validatePubmedId(Request $request, Study $study)
    {
        $validator = Validator::make($request->all(), [
            'pubmed_id' => 'required|numeric|digits_between:1,10',
        ], [
            'pubmed_id.required' => 'Please enter a PubMed ID.',
            'pubmed_id.numeric' => 'PubMed ID must be numeric.',
            'pubmed_id.digits_between' => 'PubMed ID must be between 1 and 10 digits.',
        ]);

        if ($validator->fails()) {
            return view('study.partials.url-validation', [
                'valid' => false,
                'message' => $validator->errors()->first('pubmed_id'),
            ]);
        }

        $pubmedUrl = $study->pubmedLink($request->input('pubmed_id'));

        try {
            $response = Http::withoutRedirecting()->timeout(10)->get($pubmedUrl);
            $status = $response->status();
            if ($status < 200 || $status >= 400) {
                return view('study.partials.url-validation', ['valid' => false, 'message' => 'The provided PubMed ID does not resolve to a valid URL.']);
            }
        } catch (Exception $e) {
            return view('study.partials.url-validation', ['valid' => false, 'message' => 'The provided PubMed ID is not valid.']);
        }

        return view('study.partials.url-validation', ['valid' => true, 'message' => 'PubMed ID URL looks valid.']);
    }

    public function showStudies(Request $request)
    {
        $query = Study::with(['user', 'investigators', 'animalModels', 'drugAdministrationPlans', 'diseaseLines', 'collection']);
        $abandonedFilterValue = 'abandoned';

        // Handle search
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('identifier', 'LIKE', "%{$search}%")
                    ->orWhere('summary', 'LIKE', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('email', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('investigators', function ($invQuery) use ($search) {
                        $invQuery->where('first_name', 'LIKE', "%{$search}%")
                            ->orWhere('last_name', 'LIKE', "%{$search}%");
                    })
                  // Collection-inherited metadata for imported studies
                    ->orWhereHas('collection', function ($colQuery) use ($search) {
                        $colQuery->where('identifier', 'LIKE', "%{$search}%")
                            ->orWhere('title', 'LIKE', "%{$search}%")
                            ->orWhere('summary', 'LIKE', "%{$search}%");
                    })
                    ->orWhereHas('collection.investigators', function ($invQuery) use ($search) {
                        $invQuery->where('first_name', 'LIKE', "%{$search}%")
                            ->orWhere('last_name', 'LIKE', "%{$search}%");
                    });

            });
        }

        // Handle status filter
        if ($request->filled('status') && $request->get('status') !== 'all') {
            $status = $request->get('status');

            // query based off status
            if ($status === $abandonedFilterValue) {
                $query->abandonedDrafts(now()->subDays(StudyService::ABANDONED_DRAFT_DAYS));
            } elseif (in_array($status, array_column(StudyStatus::cases(), 'value'))) {
                $query->where('status', $status);
            }
        }

        // Handle embargo filter
        $embargo = $request->get('embargo', 'all');
        if ($embargo === 'embargoed') {
            $query->embargoed();
        } elseif ($embargo === 'not_embargoed') {
            $query->where(function ($q) {
                $q->whereNull('embargo_start_date')
                    ->orWhere('embargo_start_date', '>', now());
            });
        }

        $studies = $query->latest()->paginate(20)->appends($request->query());

        if ($request->ajax()) {
            return view('admin.partials.studies', compact('studies'));
        }

        return view('admin.studies', compact('studies'))
            ->with('statuses', StudyStatus::cases())
            ->with('abandonedFilterValue', $abandonedFilterValue)
            ->with('abandonedDays', StudyService::ABANDONED_DRAFT_DAYS);
    }

    public function editStudy(Request $request, $id)
    {
        $study = Study::findOrFail($id);

        return view('study.summary.edit', compact('study'));
    }

    public function submitForApproval(Request $request, $id)
    {
        $study = Study::findOrFail($id);

        // safety check
        if ($study->status === StudyStatus::APPROVED->value) {
            return redirect()->back()->with('error', 'Study cannot be completed after approval.');
        }

        $study->update(['status' => StudyStatus::PENDING->value]);

        return redirect()->back()->with('success', 'Successfully submitted study for approval.');
    }

    public function unsubmitForApproval(Request $request, $id)
    {
        $study = Study::findOrFail($id);
        Log::info('unsubmit for approval! 1');

        if ($study->status === StudyStatus::APPROVED->value) {
            // too late, admin already approved
            return redirect()->back()->with('error', 'Study cannot be unsubmitted after approval.');
        }

        $study->update(['status' => StudyStatus::IN_PROGRESS->value]);

        return redirect()->back()->with('success', 'Successfully unsubmitted study for approval.');
    }

    public function approveStudy(Request $request, $id)
    {
        $study = Study::findOrFail($id);
        $this->studyService->approve($study, $request->user());

        return redirect()->back()->with('success', 'Study approved successfully.');
    }

    public function rejectStudy(Request $request, $id)
    {
        $study = Study::findOrFail($id);
        $reason = $request->input('rejection_reason');
        $this->studyService->reject($study, $request->user(), $reason ?? null);

        return redirect()->back()->with('success', 'Requested revision successfully.');
    }

    public function destroyOwnedStudy(Request $request, Study $study)
    {
        $this->studyService->deleteOwnedStudy($study, $request->user());

        return redirect()->route('my-studies')->with('success', 'Study deleted successfully.');
    }

    public function destroyAbandoned(Request $request, $id)
    {
        $study = Study::findOrFail($id);

        try {
            $this->studyService->deleteAbandonedDraft($study, $request->user());
        } catch (Exception $exception) {
            return redirect()->back()->with('error', $exception->getMessage());
        }

        return redirect()->route('admin.studies', ['status' => 'abandoned'])
            ->with('success', 'Abandoned study deleted successfully.');
    }

    public function showEmbargoedStudies(Request $request)
    {
        $past = Study::query()->whereNotNull('embargo_reminder_sent_at', 'and')
            ->with('user')
            ->latest('embargo_reminder_sent_at')
            ->paginate(20);

        $future = Study::query()->whereNotNull('embargo_reminder_date', 'and')
            ->whereNull('embargo_reminder_sent_at', 'and')
            ->with('user')
            ->orderBy('embargo_reminder_date')
            ->paginate(20);

        return view('admin.embargoed-studies', compact('past', 'future'));
    }

    public function resendEmbargoReminder(Request $request, Study $study)
    {
        if (! $study->user || ! $study->user->email) {
            return redirect()->back()->with('error', 'Study does not have a valid user email to send reminder.');
        }

        SendEmbargoReminderEmail::dispatch($study->id, true);

        return redirect()->back()->with('success', 'Embargo reminder email queued successfully.');
    }
}
