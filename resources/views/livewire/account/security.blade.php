<div>
    @if ($successMessage)
        <div class="alert alert-success">{{ $successMessage }}</div>
    @endif

    <div class="card shadow-sm mb-4" style="border-left: 4px solid var(--bs-primary);">
        <div class="card-body p-4">
            <h1 class="h3 mb-2">Security</h1>
            <p class="text-muted mb-0">Manage your sign-in email and password.</p>
        </div>
    </div>

    <div class="card shadow-sm mb-4" style="border-left: 4px solid var(--bs-primary);">
        <div class="card-body p-4">
            <h2 class="h4 mb-2">Email address</h2>
            <p class="text-muted small mb-4">
                Current email: <strong>{{ auth()->user()->email }}</strong>.
                Changing your email will require verification before you can use features that require a verified address.
            </p>

            <form wire:submit="updateEmail">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="new_email" class="form-label required">New email</label>
                        <input type="email"
                            id="new_email"
                            wire:model="new_email"
                            class="form-control @error('new_email') is-invalid @enderror"
                            autocomplete="email">
                        @error('new_email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="emailConfirmPassword" class="form-label required">Confirm with password</label>
                        <input type="password"
                            id="emailConfirmPassword"
                            wire:model="emailConfirmPassword"
                            class="form-control @error('emailConfirmPassword') is-invalid @enderror"
                            autocomplete="current-password">
                        @error('emailConfirmPassword') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-3" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="updateEmail">Update email</span>
                    <span wire:loading wire:target="updateEmail">Updating...</span>
                </button>
            </form>
        </div>
    </div>

    <div class="card shadow-sm" style="border-left: 4px solid var(--bs-primary);">
        <div class="card-body p-4">
            <h2 class="h4 mb-2">Password</h2>
            <p class="text-muted small mb-4">
                Choose a strong password with at least 8 characters.
                <a href="{{ route('forgot-password.show') }}">Forgot your password?</a>
            </p>

            <form wire:submit="updatePassword">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="current_password" class="form-label required">Current password</label>
                        <input type="password"
                            id="current_password"
                            wire:model="current_password"
                            class="form-control @error('current_password') is-invalid @enderror"
                            autocomplete="current-password">
                        @error('current_password') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="password" class="form-label required">New password</label>
                        <input type="password"
                            id="password"
                            wire:model="password"
                            class="form-control @error('password') is-invalid @enderror"
                            autocomplete="new-password">
                        @error('password') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>
                    <div class="col-md-4">
                        <label for="password_confirmation" class="form-label required">Confirm new password</label>
                        <input type="password"
                            id="password_confirmation"
                            wire:model="password_confirmation"
                            class="form-control @error('password') is-invalid @enderror"
                            autocomplete="new-password">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-3" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="updatePassword">Update password</span>
                    <span wire:loading wire:target="updatePassword">Updating...</span>
                </button>
            </form>
        </div>
    </div>
</div>
