<div>
    @livewire('partials.mechanical-testing', [
        'study' => $study,
        'upload' => $upload,
        'availableSubTabs' => null,
        'activeSubTab' => null,
        'analysisTypeId' => $analysisType->id ?? null
    ])
</div>