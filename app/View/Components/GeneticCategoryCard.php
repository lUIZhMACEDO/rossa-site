<?php

namespace App\View\Components;

use App\Models\GeneticCategory;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class GeneticCategoryCard extends Component
{
    public $geneticCategory;

    public $selected;

    public $hideSelectButton;

    /**
     * Create a new component instance.
     */
    public function __construct(?GeneticCategory $geneticCategory, $selected = false, $hideSelectButton = false)
    {
        $this->geneticCategory = $geneticCategory;
        $this->selected = $selected;
        $this->hideSelectButton = $hideSelectButton;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('study.animal-experimentation.animal-models.partials.genetic-category-card');
    }
}
