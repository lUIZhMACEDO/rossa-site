@php
    $user = auth()->user();
@endphp

<div>
    @if (!$user->hasVerifiedEmail())
        <div class="alert alert-warning mb-4">
            <p class="mb-2">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                Your email address is not verified. Check your inbox for a verification link, or request a new one.
            </p>
            <button type="button" wire:click="resendVerificationEmail" class="btn btn-sm btn-primary">
                Resend verification email
            </button>
        </div>
    @endif

    @if ($successMessage)
        <div class="alert alert-success">{{ $successMessage }}</div>
    @endif

    @if ($statusMessage)
        <div class="alert alert-info">{{ $statusMessage }}</div>
    @endif

    <div class="card shadow-sm mb-4" style="border-left: 4px solid var(--bs-primary);">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
                <div>
                    <h1 class="h3 mb-1">{{ $user->display_name }}</h1>
                    <p class="mb-1"
                        <span class="text-muted">
                        {{ $user->email }}
                        @if ($user->hasVerifiedEmail())
                            <i class="bi bi-check-circle-fill text-success"
                                data-bs-toggle="tooltip"
                                title="Email verified">
                            </i>
                        @else
                            <i class="bi bi-exclamation-circle-fill text-warning"
                                data-bs-toggle="tooltip"
                                title="Email not verified">
                            </i>
                        @endif
                    </p>
                    @if ($user->organization || $user->department)
                        <p class="small mb-1">
                            @if ($user->department)
                                {{ $user->department }}
                            @endif
                            @if ($user->department && $user->organization)
                                &middot;
                            @endif
                            @if ($user->organization)
                                {{ $user->organization }}
                            @endif
                        </p>
                    @endif
                    <p class="text-muted small mb-0">Joined on {{ $user->created_at->format('F j, Y') }}</p>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <p class="text-muted small mb-1">Studies submitted</p>
                                <p class="h3 mb-0">{{ $studiesCount }}</p>
                            </div>
                            <a href="{{ route('my-studies') }}" class="btn btn-sm btn-outline-primary">View submissions</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <div class="d-flex justify-content-between align-items-start gap-3">
                            <div>
                                <p class="text-muted small mb-1">Core facilities</p>
                                <p class="h3 mb-0">{{ $facilitiesCount }}</p>
                            </div>
                            <a href="{{ route('core-facility.home') }}" class="btn btn-sm btn-outline-primary">View facilities</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm" style="border-left: 4px solid var(--bs-primary);">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                <div>
                    <h2 class="h4 mb-1">Personal information</h2>
                    <p class="text-muted small mb-0">Update your registration details. Email and password are managed under Security.</p>
                </div>
                @if ($this->hasUnsavedChanges)
                    <span class="badge text-bg-warning">Unsaved changes</span>
                @endif
            </div>

            <form wire:submit="save">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="first_name" class="form-label required">First name</label>
                        <input type="text"
                            id="first_name"
                            wire:model.live="first_name"
                            @class([
                                'form-control',
                                'is-invalid' => $errors->has('first_name'),
                                'is-changed' => !$errors->has('first_name') && $this->fieldIsChanged('first_name'),
                            ])
                            autocomplete="given-name">
                        @error('first_name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="last_name" class="form-label required">Last name</label>
                        <input type="text"
                            id="last_name"
                            wire:model.live="last_name"
                            @class([
                                'form-control',
                                'is-invalid' => $errors->has('last_name'),
                                'is-changed' => !$errors->has('last_name') && $this->fieldIsChanged('last_name'),
                            ])
                            autocomplete="family-name">
                        @error('last_name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-12">
                        <label for="email" class="form-label">Email</label>
                        <input type="email"
                            id="email"
                            class="form-control"
                            value="{{ $user->email }}"
                            disabled>
                        <div class="form-text">Change your email under <a href="{{ route('account.security') }}">Security</a>.</div>
                    </div>
                    <div class="col-md-6">
                        <label for="department" class="form-label required">Primary department</label>
                        <input type="text"
                            id="department"
                            wire:model.live="department"
                            @class([
                                'form-control',
                                'is-invalid' => $errors->has('department'),
                                'is-changed' => !$errors->has('department') && $this->fieldIsChanged('department'),
                            ])
                            autocomplete="organization-title">
                        @error('department') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="organization" class="form-label required">Primary organization</label>
                        <input type="text"
                            id="organization"
                            wire:model.live="organization"
                            @class([
                                'form-control',
                                'is-invalid' => $errors->has('organization'),
                                'is-changed' => !$errors->has('organization') && $this->fieldIsChanged('organization'),
                            ])
                            autocomplete="organization">
                        @error('organization') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-12">
                        <label for="institution_type" class="form-label required">Institution type</label>
                        <select id="institution_type"
                            wire:model.live="institution_type"
                            @class([
                                'form-select',
                                'is-invalid' => $errors->has('institution_type'),
                                'is-changed' => ! $errors->has('institution_type') && $this->fieldIsChanged('institution_type'),
                            ])>
                            <option value="">Select type...</option>
                            @foreach ($institutionTypes as $type)
                                <option value="{{ $type->value }}">{{ $type->label() }}</option>
                            @endforeach
                        </select>
                        @error('institution_type') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="countryCode" class="form-label required">Country</label>
                        <select id="countryCode"
                            wire:model.live="countryCode"
                            @class([
                                'form-select',
                                'is-invalid' => $errors->has('countryCode'),
                                'is-changed' => ! $errors->has('countryCode') && $this->fieldIsChanged('countryCode'),
                            ])>
                            <option value="">Select country...</option>
                            @foreach ($countries as $country)
                                <option value="{{ $country->code }}">{{ $country->name }}</option>
                            @endforeach
                        </select>
                        @error('countryCode') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="state" @class(['form-label', 'required' => $countryCode === 'US'])>State</label>
                        <select id="state"
                            wire:model.live="state"
                            @class([
                                'form-select',
                                'is-invalid' => $errors->has('state'),
                                'is-changed' => ! $errors->has('state') && $this->fieldIsChanged('state'),
                            ])
                            @disabled($countryCode !== 'US')>
                            <option value="">Select state...</option>
                            @foreach ($states as $code => $name)
                                <option value="{{ $code }}">{{ $name }}</option>
                            @endforeach
                        </select>
                        @error('state') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>

                @if ($this->hasUnsavedChanges)
                    <hr class="my-4">

                    <div class="row g-3 align-items-start">
                        <div class="col-lg-8">
                            <div class="bg-secondary bg-opacity-10 rounded p-3">
                                <p class="fw-semibold mb-2">Confirm changes</p>
                                <ul class="list-unstyled small mb-0">
                                    @foreach ($pendingChanges as $change)
                                        <li class="mb-1">
                                            <span class="text-muted">{{ $change['label'] }}:</span>
                                            {{ $change['from'] }} &rarr; {{ $change['to'] }}
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <label for="confirmPassword" class="form-label required">Confirm with password</label>
                            <input type="password"
                                id="confirmPassword"
                                wire:model="confirmPassword"
                                class="form-control @error('confirmPassword') is-invalid @enderror"
                                autocomplete="current-password">
                            @error('confirmPassword') <span class="invalid-feedback">{{ $message }}</span> @enderror

                            <div class="d-flex flex-wrap gap-2 mt-3">
                                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">
                                    <span wire:loading.remove wire:target="save">Save changes</span>
                                    <span wire:loading wire:target="save">Saving...</span>
                                </button>
                                <button type="button" wire:click="cancel" class="btn btn-outline-secondary">
                                    Cancel
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>
