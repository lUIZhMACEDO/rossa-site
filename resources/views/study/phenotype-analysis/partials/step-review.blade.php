<div class="table-responsive">
    <table class="table table-sm table-bordered table-striped">
        <thead>
            <tr>
                <th>Group</th>
                @foreach(collect($upload->column_measurement_mappings)->filter() as $colIndex => $measurementId)
                    @php
                        $measurement = $upload->measurements->where('id', $measurementId)->first();
                    @endphp
                    <th class="text-nowrap table-success">
                        <div>
                            {{ $measurement?->display_name }}
                            @if ( $measurement?->unit )
                                ( {{ $measurement->unit }} )
                            @endif
                        </div>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($samples as $sample)
                <tr>
                    <td class="text-nowrap">{{ $sample->sampleGroup?->label ?? '-No Group-' }}</td>
                    @foreach(collect($upload->column_measurement_mappings)->filter() as $colIndex => $measurementId)
                        @php
                            $value = $sample->measurementValues->where('measurement_id', $measurementId)->first()?->value;
                        @endphp
                        <td class="text-nowrap">{{ $value }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
    
</div>