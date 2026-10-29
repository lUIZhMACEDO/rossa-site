@props(['geneticCategory' => null, 'selected' => false, 'color' => 'primary', 'hideSelectButton' => false])

<x-card-select 
    :color="$color" 
    :selected="$selected"
    :hideSelectButton="$hideSelectButton"
    :buttonName="'genetic_category_id'"
    :buttonValue="$geneticCategory ? $geneticCategory->id : ''"
>
    <x-slot name="title">
        {{ $geneticCategory ? $geneticCategory->name : 'No Genetic Modification' }}
    </x-slot>

    @if($geneticCategory)
        {{ $geneticCategory->description }}
    @else
        For studies using a specific animal strain without genetic modifications
    @endif

    <x-slot name="button">        
        {{ $selected ? 'Selected' : 'Select' }}
    </x-slot>
</x-card-select>
