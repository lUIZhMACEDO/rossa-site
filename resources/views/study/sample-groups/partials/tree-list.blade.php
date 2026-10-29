<ul>
    @forelse($sampleGroups as $sampleGroup)
        <li>{{ $sampleGroup->label }} (Group {{ $sampleGroup->id }})
            @if (!$viewonly)
                / <a href="{{ route('study.sample-groups.edit', [$study, $sampleGroup]) }}" class="link-primary">Edit</a> / 
                <form action="{{ route('study.sample-groups.delete', [$study, $sampleGroup]) }}" method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this sample group? This action cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="sample_group_id" value="{{ $sampleGroup->id }}">
                    <button type="submit" class="link-danger" style="border:none; background:none; padding:0; cursor:pointer;">Delete</button>
                </form>
            @endif
            <ul class="noicon">
                <li>Animal: {{ $sampleGroup->animalModel->experimentalStrainLabel() }}</li>
                <li>Genotype: {{ $sampleGroup->genotypeWithAllele() }}</li>
                <li>Age: {{ $sampleGroup->age }}wk</li>
                {{-- TODO(Aaron): this needs to be updated. no longer using drugTreatment, using drug-administration-plan --}}
                @if ( $sampleGroup->drugAdministrationPlan )
                    <li>{{ $sampleGroup->drugAdministrationPlan->full_display_name }} (ID: {{ $sampleGroup->drugAdministrationPlan->id }})</li>
                @endif
                @if ( $sampleGroup->gonadectomyProcedure )
                    <li>Gonadectomy Procedure: {{ $sampleGroup->gonadectomyProcedure->getDisplayName() }} (ID: {{ $sampleGroup->gonadectomyProcedure->id }})</li>
                @endif
                @if ( $sampleGroup->otherInterventionPlan )
                    <li>Intervention: {{ $sampleGroup->otherInterventionPlan->plan_name }} (ID: {{ $sampleGroup->otherInterventionPlan->id }})</li>
                @endif
                @if ( $sampleGroup->cre_status )
                    <li>CRE Status: {{ ucfirst($sampleGroup->cre_status) }}</li>
                @endif
            </ul>
        </li>
    @empty
        <li>No sample groups found.</li>
    @endforelse
</ul>