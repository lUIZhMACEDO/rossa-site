<x-card-select 
    :buttonName="'analysis_type_id'"
    :buttonValue="$analysisType ? $analysisType->id : ''"
    :color="$color ?? 'primary'"
>
    <x-slot name="title">
        {{ $analysisType ? $analysisType->name : 'Generic Animal Data' }}
    </x-slot>

    @if($analysisType)
        {{ $analysisType->description }}
    @else
        For additional animal data that does not fit into predefined analysis types
    @endif

    <x-slot name="button">
        Select {{ $analysisType->abbreviation ?? '' }}
    </x-slot>
</x-card-select>
