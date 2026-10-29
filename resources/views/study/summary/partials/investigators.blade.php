<div class="table-responsive">
    <table class="table">
        <thead>
            <tr>
                <th>Investigator</th>
                <th>Department</th>
                <th>Location</th>
                <th>Role</th>
            </tr>
        </thead>
        <tbody>
                @if($study->investigators->isNotEmpty())
                    @foreach($study->investigators as $investigator)
                        <tr>
                            <td>
                                {{ $investigator->first_name }} {{ $investigator->last_name }}
                                <a href="mailto:{{ $investigator->email }}" class="d-block text-muted">{{ $investigator->email }}</a>
                            </td>
                            <td>
                                {{ $investigator->department }}
                                <span class="d-block text-muted">{{ $investigator->organization }}</span>
                            </td>
                            <td>{{ $investigator->state ? $investigator->state . ',' : '' }} {{ $investigator->country->name }}</td>
                            <td>
                                <x-investigator-role-badges :labels="$investigator->roleLabels()" />
                            </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="6" class="text-muted text-center">No investigators have been added to this study.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>