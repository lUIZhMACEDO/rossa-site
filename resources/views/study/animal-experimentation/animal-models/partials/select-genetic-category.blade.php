<div class="select-genetic-category">

    @php
        $conditionals = $availableGeneticCategories->filter(function($category) {
            return str_contains(strtolower($category->name), 'conditional');
        })->sortBy('name')->values();

        $globals = $availableGeneticCategories->filter(function($category) {
        return str_contains(strtolower($category->name), 'global');
    })->sortBy(function($category) {
        $name = strtolower($category->name);
        if (str_contains($name, 'knockout/knockin')) {
            $rank = 0;
        } elseif (str_contains($name, 'double knockout')) {
            $rank = 99;
        } else {
            $rank = 50;
        }
        return sprintf('%02d-%s', $rank, $name);
    })->values();

        $other = $availableGeneticCategories->filter(function($category) use ($conditionals, $globals) {
            return !($conditionals->contains($category) || $globals->contains($category));
        })->sortBy(function($category) {
            return match ($category->template) {
                'standard_transgenic' => 0,
                'custom_animal_model' => 1,
                default => 2,
            };
        })->values();

        function getColorByCategory($category) {
        $name = strtolower($category->name);
        if (str_contains($name, 'conditional')) {
            return 'info';
        } elseif (str_contains($name, 'global')) {
            return 'primary';
        } elseif (str_contains($name, 'custom')) {
            return 'purple';
        } elseif (str_contains($name, 'transgenic')) {
            return 'green';
        } else {
            return 'red';
        }
    }
    @endphp

    {{-- Globals --}}
    @if ($globals->isNotEmpty())
        <h5 class="fw-semibold mb-2" style="color: #14273f;">Globals</h5>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-0 rounded overflow-hidden mb-5" style="border: 1px solid #5F5E5A;">
            @foreach ($globals as $geneticCategory)
                <div class="col d-flex" @if(!$loop->last) style="border-right: 1px solid #5F5E5A;" @endif>
                    <x-genetic-category-card
                        :$geneticCategory
                        :selected="false"
                        :color="getColorByCategory($geneticCategory)"
                    />
                </div>
            @endforeach
        </div>
    @endif

    {{-- Other Options: Conditionals (left) + Other (right) --}}
<div class="row g-4 mb-5">

    {{-- Conditionals --}}
    @if ($conditionals->isNotEmpty())
        <div class="col-lg-6">
            <h5 class="fw-semibold mb-2" style="color: #14273f;">Conditionals</h5>
            <div class="row row-cols-1 row-cols-sm-2 g-0 rounded overflow-hidden" style="border: 1px solid #5F5E5A;">
                @foreach ($conditionals as $geneticCategory)
                    <div class="col d-flex" @if(!$loop->last) style="border-right: 1px solid #5F5E5A;" @endif>
                        <x-genetic-category-card
                            :$geneticCategory
                            :selected="false"
                            :color="getColorByCategory($geneticCategory)"
                        />
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Other --}}
    @php
        $otherOptions = collect([null])->concat($other);
    @endphp
    <div class="col-lg-6">
        <h5 class="fw-semibold mb-2" style="color: #14273f;">Other Options</h5>
        <div class="row row-cols-1 row-cols-sm-3 g-0 rounded overflow-hidden" style="border: 1px solid #5F5E5A;">
            @foreach ($otherOptions as $geneticCategory)
                <div class="col d-flex" @if(!$loop->last) style="border-right: 1px solid #5F5E5A;" @endif>
                    <x-genetic-category-card
                        :geneticCategory="$geneticCategory"
                        :selected="false"
                        :color="$geneticCategory ? getColorByCategory($geneticCategory) : 'secondary'"
                    />
                </div>
            @endforeach
        </div>
    </div>

</div>