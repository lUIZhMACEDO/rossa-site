<div class="alert alert-info">
    No measurements with statistics available for this comparison group.
    @if(!($publicAccess ?? false))
        If you have {{ $modality ?? 'measurement' }} data for this comparison group, return to the
        <a href="{{ route('study.phenotype-analysis', ['study' => $study->id]) }}">Upload Data</a> page to review your uploads,
        or return to <a href="{{ route('study.comparison-groups.index', ['study' => $study->id]) }}">Comparison Groups</a> to review your comparison groups.
    @endif
</div>
