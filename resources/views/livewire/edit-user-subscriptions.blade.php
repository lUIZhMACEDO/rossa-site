<div>
    <x-session-alerts />

    {{-- <div class="my-4">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="subscribeToNewsletter" wire:model.live="subscribeToNewsletter">
            <label class="form-check-label" for="subscribeToNewsletter">
                Receive the newsletter
            </label>
        </div>
    </div> --}}

    @if ($user->hasPermission('manage-studies'))
        <div class="card mb-4" style="border-left: 4px solid var(--bs-danger);">        
            <div class="card-body">
                <div class="card-title mb-3">
                    <h5>Administrative Subscriptions</h5>
                </div>
                <div class="mb-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="subscribeToStudySubmitted" wire:model.live="subscribeToStudySubmitted">
                        <label class="form-check-label" for="subscribeToStudySubmitted">
                            Receive email notifications when studies are submitted for approval
                        </label>
                    </div>
                </div>
            </div>
        </div>
    @endif


    <button wire:click="updateSubscription" class="btn btn-primary">Update Subscriptions</button>
</div>
