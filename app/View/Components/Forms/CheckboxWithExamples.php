<?php

namespace App\View\Components\Forms;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class CheckboxWithExamples extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(
        public string $id,
        public string $label,
        public ?string $model = null,
        public ?string $examples = null,
        public ?string $buttonText = 'Show Examples',
        public bool $disabled = false
    ) {
        $this->model = $model ?? $this->id;
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.forms.checkbox-with-examples');
    }
}
