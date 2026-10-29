<div class="container">
    <form wire:submit.prevent="store">
        @include('livewire.partials.cf-fields')
        <button type="submit" class="btn btn-primary">Submit Registration</button>
    </form>
</div>