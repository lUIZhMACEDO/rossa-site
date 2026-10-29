{{--
    Study sharing: header bar with collaborator avatars, Share button and dialog.
    Usage: <x-study-share :study="$study" />
    Posts to the existing contributor routes (invite, resend, revoke, remove).
--}}
@props(['study'])

@php
    $currentUser = auth()->user();
    $canManage = $currentUser->canManageSharing($study);

    $owner = $study->user;
    $contributors = $study->contributors()->orderBy('first_name')->orderBy('last_name')->get();
    $pendingInvitations = $study->contributorInvitations()
        ->with(['user', 'inviter'])
        ->where('status', \App\Models\StudyContributorInvitation::PENDING)
        ->where('expires_at', '>', now())
        ->latest()
        ->get();

    $initials = function ($user) {
        // Prefer first/last name; fall back to the display name (for accounts with only a single name field).
        $first = trim($user->first_name ?? '');
        $last = trim($user->last_name ?? '');

        if ($first === '' && $last === '') {
            $parts = preg_split('/\s+/', trim($user->display_name ?? ''), -1, PREG_SPLIT_NO_EMPTY);
            $first = $parts[0] ?? '';
            $last = count($parts) > 1 ? end($parts) : '';
        }

        $value = strtoupper(mb_substr($first, 0, 1) . mb_substr($last, 0, 1));

        return $value !== '' ? $value : strtoupper(mb_substr($user->email ?? '?', 0, 1));
    };

    $avatarPeople = collect([$owner])->filter()->concat($contributors);
    $visibleAvatars = $avatarPeople->take(4);
    $hiddenAvatarCount = max(0, $avatarPeople->count() - $visibleAvatars->count());

    // "Back to collection" link, moved here from the old sidebar header.
    $backRoute = null;
    $backLabel = null;
    if ($study->collection_id && $study->collection) {
        $backRoute = $study->collection->hasCorrespondingUser(auth()->user())
            ? route('my-collections.studies', $study->collection)
            : route('admin.collections.studies', $study->collection);
        $backLabel = $study->collection->identifier;
    }

    $reopenDialog = $errors->has('email') || $errors->has('invitation');
    $modalId = 'studyShareModal';
@endphp

<header id="studyShareBar" class="study-share-bar d-flex justify-content-between align-items-center gap-3">
    <div class="study-share-bar-info">
        <div class="study-share-bar-meta">
            @if($backRoute)
                <a href="{{ $backRoute }}" class="study-share-bar-back"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to {{ $backLabel }}</a>
                &middot;
            @endif
            Study ID: {{ $study->identifier }}
            @if($study->status)
                &middot; {{ $study->status->label() }}
            @endif
        </div>
        <div class="study-share-bar-title">{{ $study->title ?: 'Untitled study' }}</div>
    </div>

    <div class="d-flex align-items-center gap-2 flex-shrink-0">
        @if($pendingInvitations->isNotEmpty())
            <span class="study-share-bar-pending">{{ $pendingInvitations->count() }} pending</span>
        @endif

        <div class="d-flex align-items-center" aria-label="People with access">
            @foreach($visibleAvatars as $person)
                <span class="study-share-avatar {{ $loop->first ? 'is-owner' : '' }}"
                      title="{{ $person->display_name ?: $person->email }}{{ $loop->first ? ' (owner)' : '' }}">
                    {{ $initials($person) }}
                </span>
            @endforeach
            @if($hiddenAvatarCount > 0)
                <span class="study-share-avatar is-more" title="{{ $hiddenAvatarCount }} more">+{{ $hiddenAvatarCount }}</span>
            @endif
        </div>

        <button type="button" class="btn btn-primary btn-sm"
                data-bs-toggle="modal" data-bs-target="#{{ $modalId }}">
            <i class="bi bi-share me-1" aria-hidden="true"></i>Share
        </button>

        <button type="button" class="btn btn-outline-light btn-sm fw-semibold text-nowrap"
                data-bs-toggle="modal" data-bs-target="#guideModal"
                aria-label="View instruction guide">
            <i class="bi bi-question-circle me-1" aria-hidden="true"></i>Help
        </button>

        <button type="button" class="btn btn-secondary btn-sm fw-semibold text-nowrap">
            <i class="bi bi-laptop me-1" aria-hidden="true"></i>
            <a href="{{ route('office-hours.index') }}" target="_blank" class="text-white text-decoration-none">Office Hours</a>
        </button>
    </div>
</header>

<div class="modal fade" id="{{ $modalId }}" tabindex="-1" aria-labelledby="{{ $modalId }}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title h5" id="{{ $modalId }}Label">Share this study</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                @if($canManage)
                    <form action="{{ route('study.contributors.store', $study) }}" method="POST" class="mb-2">
                        @csrf
                        <label for="shareEmail" class="form-label visually-hidden">Email address</label>
                        <div class="input-group">
                            <input id="shareEmail" name="email" type="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" placeholder="name@institution.edu" required>
                            <span class="input-group-text">Editor</span>
                            <button class="btn btn-primary" type="submit">Send invite</button>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </form>
                    <p class="small text-muted mb-4">
                        Editors can change the study information and uploaded data. No ROSSA account yet?
                        They will be asked to create one with this address before accepting.
                    </p>
                @else
                    <p class="small text-muted mb-3">
                        Only the study owner, corresponding investigator, or ROSSA staff can change who has access.
                    </p>
                @endif

                @error('invitation')
                    <div class="alert alert-danger small py-2">{{ $message }}</div>
                @enderror

                <div class="fw-semibold small mb-2">People with access</div>
                <div class="list-group mb-4">
                    @if($owner)
                        <div class="list-group-item d-flex align-items-center gap-3">
                            <span class="study-share-avatar is-owner">{{ $initials($owner) }}</span>
                            <div class="flex-grow-1">
                                <div>{{ $owner->display_name ?: $owner->email }}</div>
                                <small class="text-muted">{{ $owner->email }}</small>
                            </div>
                            <small class="text-muted">Owner</small>
                        </div>
                    @endif

                    @foreach($contributors as $contributor)
                        <div class="list-group-item d-flex align-items-center gap-3">
                            <span class="study-share-avatar">{{ $initials($contributor) }}</span>
                            <div class="flex-grow-1">
                                <div>{{ $contributor->display_name ?: $contributor->email }}</div>
                                <small class="text-muted">{{ $contributor->email }}</small>
                            </div>
                            @if($canManage)
                                <form action="{{ route('study.contributors.destroy', [$study, $contributor]) }}" method="POST"
                                      onsubmit="return confirm('Remove this person\'s access to the study?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm" type="submit">Remove</button>
                                </form>
                            @else
                                <small class="text-muted">Editor</small>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if($pendingInvitations->isNotEmpty())
                    <div class="fw-semibold small mb-2">Pending invitations</div>
                    <div class="list-group">
                        @foreach($pendingInvitations as $invitation)
                            <div class="list-group-item d-flex align-items-center gap-3">
                                <span class="study-share-avatar is-pending">
                                    {{ $invitation->user ? $initials($invitation->user) : strtoupper(mb_substr($invitation->email, 0, 1)) }}
                                </span>
                                <div class="flex-grow-1">
                                    <div>
                                        {{ $invitation->user?->display_name ?: $invitation->email }}
                                        @unless($invitation->user_id)
                                            <span class="badge text-bg-light border ms-1">No ROSSA account yet</span>
                                        @endunless
                                    </div>
                                    <small class="text-muted">
                                        Invited by {{ $invitation->inviter->display_name ?: $invitation->inviter->email }}
                                        &middot; expires {{ $invitation->expires_at->toFormattedDateString() }}
                                    </small>
                                </div>
                                @if($canManage)
                                    <div class="d-flex gap-2">
                                        <form action="{{ route('study.invitations.resend', [$study, $invitation]) }}" method="POST">
                                            @csrf
                                            <button class="btn btn-outline-primary btn-sm" type="submit">Resend</button>
                                        </form>
                                        <form action="{{ route('study.invitations.revoke', [$study, $invitation]) }}" method="POST"
                                              onsubmit="return confirm('Revoke this invitation?');">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-outline-danger btn-sm" type="submit">Revoke</button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
/* Full-width header above the sidebar and the form. */
.study-share-bar {
    background: #111;
    border: 1px solid #1A7DB5;
    border-radius: 0.375rem;
    margin: 0.5rem 0.5rem 0.5rem 0;
    padding: 10px 1rem;
    min-height: 56px;
}
.study-share-bar-info { min-width: 0; }
.study-share-bar-title {
    color: #fff;
    font-size: 15px;
    font-weight: 600;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.study-share-bar-meta { color: #7FBCD4; font-size: 12px; }
.study-share-bar-back { color: #7FBCD4; text-decoration: none; }
.study-share-bar-back:hover { color: #fff; text-decoration: underline; }
.study-share-bar-pending { color: #7FBCD4; font-size: 12px; }
.study-share-bar .study-share-avatar { border-color: #111; }
.study-share-avatar { border-color: #0D3B52; }
.study-share-avatar {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    font-size: 10px;
    font-weight: 600;
    background: #e2e8f0;
    color: #2d3748;
    border: 2px solid #fff;
}
.study-share-bar .study-share-avatar + .study-share-avatar { margin-left: -5px; }
.study-share-avatar.is-owner { background: #cfe2ff; color: #0a4a9e; }
.study-share-avatar.is-pending { background: #fff3cd; color: #7a5c00; }
.study-share-avatar.is-more { background: #f1f3f5; color: #495057; }
</style>

<script>
    // Stop the header at the same right edge as the content pane (which is capped in width).
    document.addEventListener('DOMContentLoaded', function () {
        var bar = document.getElementById('studyShareBar');
        var main = document.querySelector('.sidebar-layout .main-content');
        if (!bar || !main) return;
        function align() {
            bar.style.maxWidth = '';
            // Prefer the content wrapper the form cards sit in, so the bar's right edge lines up with them.
            var wrap = main.querySelector('.px-3.position-relative');
            var right;
            if (wrap) {
                right = wrap.getBoundingClientRect().right - (parseFloat(getComputedStyle(wrap).paddingRight) || 0);
            } else {
                right = main.getBoundingClientRect().right - (parseFloat(getComputedStyle(bar).marginRight) || 0);
            }
            var width = right - bar.getBoundingClientRect().left;
            if (width > 0) bar.style.maxWidth = width + 'px';
        }
        align();
        window.addEventListener('resize', align);
        window.addEventListener('load', align);
    });

    (function () {
        var modalEl = document.getElementById(@json($modalId));
        if (!modalEl) return;
        // Move the modal to <body> so positioned parents cannot trap the backdrop.
        document.body.appendChild(modalEl);
        @if($reopenDialog)
            document.addEventListener('DOMContentLoaded', function () {
                bootstrap.Modal.getOrCreateInstance(modalEl).show();
            });
        @endif
    })();
</script>
