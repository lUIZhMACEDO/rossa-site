@props([
    'study',
    'viewonly' => false, // used to disable buttons on report page
])

@php
    $emptyComparisonGroups = $study->comparisonGroups->isEmpty();
@endphp
<div class="row">
    <div>
        <div class="table-responsive">
            <table class="table {{ $emptyComparisonGroups ? '' : 'table-hover' }}">
                <thead>
                    <tr>
                        <th>Comparison Group</th>
                        @if (!$viewonly)
                            <th>Actions</th>
                        @endif
                    </tr>
                </thead>
                @if($emptyComparisonGroups)
                    <tr>
                        <td colspan="100%" class="text-center text-muted">No comparison groups defined yet.</td>
                    </tr>
                @else
                    <tbody>
                        @foreach($study->comparisonGroups as $comparisonGroup)
                            <tr>
                                <td>
                                    @php
                                        $groups = $comparisonGroup->sampleGroups();
                                    @endphp
                                    @foreach($groups as $group)
                                        <span class="badge {{ $group->control ? 'bg-primary' : 'bg-secondary' }} me-1">
                                            {{ $group->label }}
                                            @if($group->control)
                                                <i class="bi-star-fill" title="Control"></i>
                                            @endif
                                        </span>
                                    @endforeach
                                </td>
                                @if (!$viewonly)
                                    <td>
                                        <div class="btn-group gap-2">
                                            <form method="GET"
                                                action="{{ route('study.comparison-groups.edit', [$study, $comparisonGroup]) }}"
                                                class="d-inline">
                                                <button type="submit" class="btn btn-sm btn-primary"
                                                    title="Edit"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-placement="top">
                                                    <i class="bi-pencil"></i>
                                                </button>
                                            </form>
                                            <form method="POST" 
                                                action="{{ route('study.comparison-groups.destroy', [$study, $comparisonGroup]) }}" 
                                                class="d-inline"
                                                onsubmit="return confirm('Are you sure you want to delete this comparison group?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger"
                                                    title="Delete"
                                                    data-bs-toggle="tooltip"
                                                    data-bs-placement="top">
                                                    <i class="bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                @endif
            </table>
        </div>
    </div>
</div>