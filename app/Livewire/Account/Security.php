<?php

namespace App\Livewire\Account;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Security extends Component
{
    public string $new_email = '';

    public string $emailConfirmPassword = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public ?string $successMessage = null;

    public function updated(string $property): void
    {
        $this->resetValidation($property);
    }

    public function updateEmail(): void
    {
        /** @var User $user */
        $user = Auth::user();

        $this->validate([
            'new_email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'emailConfirmPassword' => ['required', 'current_password'],
        ], [], [
            'new_email' => 'new email',
            'emailConfirmPassword' => 'password',
        ]);

        if (strcasecmp($this->new_email, $user->email) === 0) {
            $this->addError('new_email', 'That is already your current email address.');

            return;
        }

        $user->forceFill([
            'email' => $this->new_email,
            'email_verified_at' => null,
        ])->save();

        $user->sendEmailVerificationNotification();
        Auth::setUser($user->fresh());

        $this->reset(['new_email', 'emailConfirmPassword']);
        $this->resetValidation();
        $this->successMessage = 'Your email has been updated. Please verify your new email address before using features that require verification.';
    }

    public function updatePassword(): void
    {
        $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'min:8', 'confirmed'],
        ], [], [
            'current_password' => 'current password',
            'password' => 'new password',
        ]);

        /** @var User $user */
        $user = Auth::user();
        $user->forceFill([
            'password' => Hash::make($this->password),
        ])->save();

        $this->reset(['current_password', 'password', 'password_confirmation']);
        $this->resetValidation();
        $this->successMessage = 'Your password has been updated.';
    }

    public function render()
    {
        return view('livewire.account.security');
    }
}
