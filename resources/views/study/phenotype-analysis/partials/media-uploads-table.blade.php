@php
    use App\Support\MediaUploadTable;

    $sectionNumber = $sectionNumber ?? '02';
@endphp

<div class="rounded p-3 p-md-4" style="background-color: #0D3B52;" id="media-uploads-table">

    <div class="d-flex align-items-baseline gap-2 mb-3">
        <span class="font-monospace" style="font-size: .9375rem; color: #E6F1FB;">{{ $sectionNumber }}</span>
        <h2 class="h5 mb-0" style="color: #fff;">Uploaded Micro-CT images</h2>
        @if($mediaUploads->isNotEmpty())
            <span style="font-size: .8125rem; color: #E6F1FB;">
                {{ $mediaUploads->count() }} {{ Str::plural('file', $mediaUploads->count()) }}
            </span>
        @endif
    </div>

    <div class="bg-white rounded p-3">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead>
                    <tr>
                        <th>Filename</th>
                        <th>Status</th>
                        <th>Processing level</th>
                        <th>Bone type</th>
                        <th>Scan extent</th>
                        <th style="min-width: 14rem;">Sample group</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody id="media-uploads-tbody">
                    @forelse ($mediaUploads as $media)
                        @include('study.phenotype-analysis.partials.media-upload-row', MediaUploadTable::rowViewData($study, $media))
                    @empty
                        <tr id="media-uploads-empty">
                            <td colspan="7" class="text-center py-4" style="color: #2C2C2A;">
                                No Micro-CT files uploaded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
