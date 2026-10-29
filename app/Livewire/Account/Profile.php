<?php

namespace App\Livewire\Account;

use App\Enums\InstitutionType;
use App\Models\Country;
use App\Models\User;
use App\Services\InvestigatorService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Profile extends Component
{
    public string $first_name = '';

    public string $last_name = '';

    public string $department = '';

    public string $organization = '';

    public string $institution_type = '';

    public string $countryCode = '';

    public ?string $state = null;

    public string $confirmPassword = '';

    /** @var array<string, mixed> */
    public array $original = [];

    public int $studiesCount = 0;

    public int $facilitiesCount = 0;

    public ?string $successMessage = null;

    public ?string $statusMessage = null;

    public function mount(): void
    {
        /** @var User $user */
        $user = Auth::user()->loadMissing('country');
        $this->loadFromUser($user);
        $this->studiesCount = $user->studies()->count();
        $this->facilitiesCount = $user->coreFacilities()->count();
    }

    public function updatedCountryCode(string $value): void
    {
        if ($value !== 'US') {
            $this->state = null;
        }
    }

    public function updated(string $property): void
    {
        $this->resetValidation($property);
    }

    public function getHasUnsavedChangesProperty(): bool
    {
        return $this->pendingChanges() !== [];
    }

    public function pendingChanges(): array
    {
        $changes = [];

        foreach ($this->trackedFields() as $field => $label) {
            if ($this->fieldIsChanged($field)) {
                $changes[$field] = [
                    'label' => $label,
                    'from' => $this->formatValue($field, $this->original[$field] ?? null),
                    'to' => $this->formatValue($field, $this->{$field}),
                ];
            }
        }

        return $changes;
    }

    public function fieldIsChanged(string $field): bool
    {
        $current = $this->normalizeValue($field, $this->{$field});
        $original = $this->normalizeValue($field, $this->original[$field] ?? null);

        return $current !== $original;
    }

    public function cancel(): void
    {
        $this->loadFromUser(Auth::user()->fresh()->loadMissing('country'));
        $this->confirmPassword = '';
        $this->successMessage = null;
        $this->statusMessage = null;
        $this->resetValidation();
    }

    public function save(InvestigatorService $investigatorService): void
    {
        $states = $investigatorService->getStates();

        $this->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'max:255'],
            'organization' => ['required', 'string', 'max:255'],
            'countryCode' => ['required', 'exists:countries,code'],
            'state' => ['nullable', 'required_if:countryCode,US', Rule::in(array_keys($states))],
            'institution_type' => ['required', Rule::enum(InstitutionType::class)],
            'confirmPassword' => ['required', 'current_password'],
        ], [], [
            'first_name' => 'first name',
            'last_name' => 'last name',
            'department' => 'primary department',
            'organization' => 'primary organization',
            'countryCode' => 'country',
            'state' => 'state',
            'institution_type' => 'institution type',
            'confirmPassword' => 'password',
        ]);

        if ($this->pendingChanges() === []) {
            $this->addError('confirmPassword', 'No profile changes to save.');

            return;
        }

        $country = Country::firstWhere('code', $this->countryCode);

        /** @var User $user */
        $user = Auth::user();
        $user->fill([
            'name' => trim("{$this->first_name} {$this->last_name}"),
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'department' => $this->department,
            'organization' => $this->organization,
            'country_id' => $country->id,
            'state' => $this->countryCode === 'US' ? $this->state : null,
            'institution_type' => $this->institution_type,
        ]);
        $user->save();

        Auth::setUser($user->fresh()->loadMissing('country'));

        $this->loadFromUser(Auth::user());
        $this->confirmPassword = '';
        $this->statusMessage = null;
        $this->successMessage = 'Your profile has been updated.';
    }

    public function resendVerificationEmail(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->statusMessage = 'Your email is already verified.';
            $this->successMessage = null;

            return;
        }

        $user->sendEmailVerificationNotification();

        $this->successMessage = null;
        $this->statusMessage = 'A new verification link has been sent to your email address.';
    }

    public function render(InvestigatorService $investigatorService)
    {
        return view('livewire.account.profile', [
            'countries' => Country::orderBy('name')->get(),
            'states' => $investigatorService->getStates(),
            'institutionTypes' => InstitutionType::cases(),
            'pendingChanges' => $this->pendingChanges(),
        ]);
    }

    // get all user info into component
    private function loadFromUser(User $user): void
    {
        $firstName = trim($user->first_name ?? '');
        $lastName = trim($user->last_name ?? '');

        if ($firstName === '' && $lastName === '' && filled($user->name)) {
            $parts = preg_split('/\s+/', trim($user->name), 2);
            $firstName = $parts[0] ?? '';
            $lastName = $parts[1] ?? '';
        }

        $this->first_name = $firstName;
        $this->last_name = $lastName;
        $this->department = $user->department ?? '';
        $this->organization = $user->organization ?? '';
        $this->institution_type = $user->institution_type?->value ?? '';
        $this->countryCode = $user->country?->code ?? '';
        $this->state = $user->state;

        $this->original = [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'department' => $this->department,
            'organization' => $this->organization,
            'institution_type' => $this->institution_type,
            'countryCode' => $this->countryCode,
            'state' => $this->state,
        ];
    }

    /** @return array<string, string> */
    private function trackedFields(): array
    {
        return [
            'first_name' => 'First name',
            'last_name' => 'Last name',
            'department' => 'Primary department',
            'organization' => 'Primary organization',
            'institution_type' => 'Institution type',
            'countryCode' => 'Country',
            'state' => 'State',
        ];
    }

    private function normalizeValue(string $field, mixed $value): mixed
    {
        if ($field === 'state') {
            return $value ?: null;
        }

        return $value ?? '';
    }

    private function formatValue(string $field, mixed $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        return match ($field) {
            'institution_type' => InstitutionType::from($value)->label(),
            'countryCode' => Country::firstWhere('code', $value)?->name ?? $value,
            'state' => app(InvestigatorService::class)->getStates()[$value] ?? $value,
            default => (string) $value,
        };
    }
}
