<?php

namespace App\Livewire;

use App\Enums\PermissionType;
use App\Enums\SubscriptionType;
use App\Models\Subscription;
use App\Models\User;
use Livewire\Component;

class EditUserSubscriptions extends Component
{
    public User $user;

    public bool $subscribeToStudySubmitted = false;

    public bool $subscribeToNewsletter = false;

    protected function rules()
    {
        return [
            'subscribeToStudySubmitted' => [
                'boolean',
                function ($attribute, $value, $fail) {
                    if ($value && ! $this->user->hasPermission(PermissionType::MANAGE_STUDIES->value)) {
                        $fail('You do not have permission to subscribe to study notifications.');
                    }
                },
            ],
            'subscribeToNewsletter' => ['boolean'],
        ];
    }

    public function mount(User $user)
    {
        $this->user = $user;
        $subscriptionTypes = $this->user->subscriptions()->pluck('type')->all();

        $this->subscribeToStudySubmitted = in_array(SubscriptionType::STUDY_SUBMITTED->value, $subscriptionTypes, true);
        $this->subscribeToNewsletter = in_array(SubscriptionType::NEWSLETTER->value, $subscriptionTypes, true);
    }

    public function updateSubscription()
    {
        $this->validate();

        $selectedTypes = [];

        if ($this->subscribeToNewsletter) {
            $selectedTypes[] = SubscriptionType::NEWSLETTER->value;
        }

        if ($this->subscribeToStudySubmitted) {
            $selectedTypes[] = SubscriptionType::STUDY_SUBMITTED->value;
        }

        $subscriptionIds = Subscription::query()
            ->whereIn('type', $selectedTypes)
            ->pluck('id')
            ->all();

        $this->user->subscriptions()->sync($subscriptionIds);

        session()->flash('success', 'Subscriptions updated successfully.');
    }

    public function render()
    {
        return view('livewire.edit-user-subscriptions');
    }
}
