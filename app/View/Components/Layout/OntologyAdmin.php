<?php

namespace App\View\Components\Layout;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class OntologyAdmin extends Component
{
    public function render(): View|Closure|string
    {
        return view('components.layout.ontology-admin');
    }
}
