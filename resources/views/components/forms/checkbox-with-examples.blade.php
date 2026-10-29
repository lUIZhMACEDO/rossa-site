<div class="mb-2" x-data="{ showExample: false }">
    <div class="d-flex align-items-center">
        <x-forms.checkbox
            :id="$id"
            :label="$label"
            :model="$model"
            :$disabled
        />
        @if ( $disabled )
            <span class="text-muted fst-italic ms-2 me-2 mb-1">Not yet supported.</span>
        @endif
        @if(!$disabled && $examples)
            <button type="button" @class(['btn btn-link text-decoration-none p-0 ms-2', 'disabled' => $disabled]) x-on:click="showExample = !showExample" @disabled($disabled)>
                {{ $buttonText }}
            </button>
        @endif
    </div>
    @if($examples)
        <div x-show="showExample" x-transition.duration.200ms class="mt-2">
            <div class="card card-body">
                {!! nl2br(e($examples)) !!}
            </div>
        </div>
    @endif
</div>