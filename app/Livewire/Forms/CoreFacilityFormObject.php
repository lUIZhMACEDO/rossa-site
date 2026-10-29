<?php

namespace App\Livewire\Forms;

use App\Enums\AnalysisTypeEnum;
use App\Enums\CoreFacilityStatus;
use App\Mail\CfSubmissionAdminEmail;
use App\Mail\CoreFacilitySubmissionEmail;
use App\Models\CoreFacility;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Validate;
use Livewire\Form;

class CoreFacilityFormObject extends Form
{
    // PROPERTIES WITH VALIDATION
    #[Validate('required', as: 'facility name', message: 'Please enter a facility name.')]
    #[Validate('string')]
    #[Validate('max:255')]
    public $facility_name;

    #[Validate('required', as: 'web address', message: 'Please enter a valid web address.')]
    #[Validate('url', as: 'web address', message: 'Please enter a valid web address.')]
    #[Validate('max:255')]
    public $web_address;

    #[Validate('required', as: 'institution', message: 'Please enter an institution.')]
    #[Validate('string')]
    #[Validate('max:255')]
    public $institution;

    #[Validate('required', as: 'contact name', message: 'Please enter a contact name.')]
    #[Validate('string')]
    #[Validate('max:255')]
    public $contact_name;

    #[Validate('required', as: 'contact email', message: 'Please enter a valid email address.')]
    #[Validate('email', as: 'contact email', message: 'Please enter a valid email address.')]
    #[Validate('max:255')]
    public $contact_email;

    #[Validate('nullable')]
    #[Validate('string')]
    #[Validate('max:255')]
    public $secondary_contact_name;

    #[Validate('nullable')]
    #[Validate('email', as: 'secondary contact email', message: 'Please enter a valid email address.')]
    #[Validate('max:255')]
    public $secondary_contact_email;

    #[Validate([
        'analysis_types' => 'array',
        'other_analysis_types' => 'array|max:5',
        'other_analysis_types.*' => 'nullable|string|max:255',
    ], message: [
        'other_analysis_types.max' => 'You can add a maximum of 5 custom analysis types.',
        'other_analysis_types.*.max' => 'Custom analysis type name cannot exceed 255 characters.',
    ])]
    public $analysis_types = [];

    public $other_analysis_types = [];

    // OTHER DATA
    public $availableAnalysisTypes = []; // set in mount of parent component

    public ?CoreFacility $facility = null;

    public bool $showSecondaryContact = false;

    public function boot()
    {
        // withValidator - allows us to add custom validation rules to FormObject
        $this->withValidator(function ($validator) {
            $validator->after(function ($validator) {
                // require at least one analysis type (standard or custom)
                $hasStandardTypes = ! empty($this->analysis_types);
                $hasCustomTypes = ! empty(array_filter($this->other_analysis_types, fn ($type) => ! empty(trim($type))));

                if (! $hasStandardTypes && ! $hasCustomTypes) {
                    $validator->errors()->add('analysis_types', 'Please select at least one analysis type or add a custom analysis type.');
                }
            });
        });
    }

    // populate form with facility data
    public function setFacility(CoreFacility $facility)
    {
        $this->facility = $facility;
        $this->facility_name = $facility->facility_name;
        $this->web_address = $facility->web_address;
        $this->institution = $facility->institution;
        $this->contact_name = $facility->contact_name;
        $this->contact_email = $facility->contact_email;
        $this->secondary_contact_name = $facility->secondary_contact_name;
        $this->secondary_contact_email = $facility->secondary_contact_email;
        $this->showSecondaryContact = ! empty($facility->secondary_contact_name) || ! empty($facility->secondary_contact_email);

        // separate available and custom analysis types
        $facilityTypes = $facility->analysis_types ?? [];
        $standardTypes = [];
        $customTypes = [];

        foreach ($facilityTypes as $type) {
            if (isset($type['slug']) && $type['slug'] === 'other') {
                $customTypes[] = $type['name'] ?? '';
            } else {
                $standardTypes[] = json_encode($type);
            }
        }

        $this->analysis_types = $standardTypes;
        $this->other_analysis_types = array_filter($customTypes);
    }

    public function addOtherAnalysisType()
    {
        if (count($this->other_analysis_types) < 5) {
            $this->other_analysis_types[] = '';
        }
    }

    public function removeOtherAnalysisType($index)
    {
        unset($this->other_analysis_types[$index]);
        $this->other_analysis_types = array_values($this->other_analysis_types);
    }

    // store
    public function store()
    {
        $this->nullSecondaryContactIfHidden();

        // validate
        $validated = $this->validate();

        $processedAnalysisTypes = $this->getProcessedAnalysisTypes();

        // create facility
        $coreFacility = new CoreFacility(
            array_merge(
                Arr::except($validated, ['analysis_types']),
                [
                    'analysis_types' => $processedAnalysisTypes,
                ]
            )
        );
        $coreFacility->user_id = Auth::id();
        $coreFacility->last_refreshed_at = now();
        $coreFacility->save();

        // send email to user
        if (Auth::user()) {
            Mail::to(Auth::user()->email)->queue(new CoreFacilitySubmissionEmail($coreFacility->id));
            Mail::to(config('mail.rossa_admin_mass_email'))->queue(new CfSubmissionAdminEmail($coreFacility->id));
        }

        // reset form
        $this->reset();
        $this->availableAnalysisTypes = AnalysisTypeEnum::getStandardTypes();
    }

    // update - inside modal
    public function update()
    {
        $this->nullSecondaryContactIfHidden();

        // validate
        $validated = $this->validate();

        $processedAnalysisTypes = $this->getProcessedAnalysisTypes();

        // check if facility expired - mark as accepted
        if ($this->facility->status === CoreFacilityStatus::EXPIRED) {
            $this->facility->status = CoreFacilityStatus::ACCEPTED;
            $this->facility->expires_at = now()->addYear()->startOfDay();
        }
        // if facility is rejected - mark as pending
        if ($this->facility->status === CoreFacilityStatus::REJECTED) {
            $this->facility->status = CoreFacilityStatus::PENDING;
        }

        // update last refreshed at
        $this->facility->last_refreshed_at = now();

        // update facility including analysis types
        $this->facility->update(array_merge(
            Arr::except($validated, ['analysis_types']),
            ['analysis_types' => $processedAnalysisTypes]
        ));
    }

    private function nullSecondaryContactIfHidden(): void
    {
        if (! $this->showSecondaryContact) {
            $this->secondary_contact_name = null;
            $this->secondary_contact_email = null;
        }
    }

    /*
     * Get processed analysis types for storage without modifying form properties
     */
    private function getProcessedAnalysisTypes(): array
    {
        $processedTypes = [];

        // standard analysis types
        foreach ($this->analysis_types as $type) {
            $decoded = is_string($type) ? json_decode($type, true) : $type;

            if ($decoded && isset($decoded['slug']) && $decoded['slug'] !== 'other') {
                $processedTypes[] = $decoded;
            }
        }

        // custom analysis types
        foreach ($this->other_analysis_types as $customType) {
            if (! empty(trim($customType))) {
                $processedTypes[] = [
                    'slug' => 'other',
                    'name' => trim($customType),
                    'shorthand' => trim($customType),
                ];
            }
        }

        return $processedTypes;
    }
}
