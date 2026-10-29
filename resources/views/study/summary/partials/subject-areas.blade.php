@php
    $tagsByTab = $study->tags->groupBy('tab');
@endphp

@if($study->tags->isNotEmpty())
    <div class="row">
        @foreach(['Thematic Areas', 'Tissue Interactions', 'Cell Types'] as $tabName)
            <div class="col-md-4">
                <h5>{{ $tabName }}</h5>
                @if(isset($tagsByTab[$tabName]) && $tagsByTab[$tabName]->isNotEmpty())
                    <ul class="list">
                        @foreach($tagsByTab[$tabName] as $tag)
                            <li>{{ $tag->name }}</li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-muted ms-3">None selected.</p>
                @endif
            </div>
        @endforeach
    </div>
@else
    <p class="text-muted">No subject areas selected for this study.</p>
@endif

{{-- disease lines --}}
<h5 class="mt-4">Disease Lines</h5>
@if($study->diseaseLines->isNotEmpty())
    <div class="border rounded bg-light p-3" style="max-height: 400px; overflow-y: auto;">
        <div class="row">
            @foreach($study->diseaseLines as $disease)
                <div class="col-12 mb-2">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="card-title mb-0">
                                    <strong>{{ $disease->label }}</strong>
                                </h6>
                                @if ($disease->iri)
                                    <a href="{{ $disease->iri }}" 
                                        target="_blank" 
                                        class="text-nowrap"
                                        title="View Disease Line"
                                        data-bs-toggle="tooltip">
                                        {{ $disease->obo_id }}
                                    </a>
                                @else
                                    <span class="text-muted text-nowrap">{{ $disease->obo_id }}</span>
                                @endif
                            </div>
                            @if(!empty($disease->xrefs))
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($disease->xrefs as $xref)
                                        @if($xref['url'])
                                            <a href="{{ $xref['url'] }}"
                                                target="_blank"
                                                class="text-nowrap"
                                                title="{{ $xref['url'] }}"
                                                data-bs-toggle="tooltip">
                                                {{ $xref['database'] }}: {{ $xref['id'] }}
                                            </a>
                                        @else
                                            <span class="text-nowrap">{{ $xref['database'] }}: {{ $xref['id'] }}</span>
                                        @endif

                                        @if ( !$loop->last )
                                            <span>|</span>
                                        @endif
                                    @endforeach
                                </div>
                            @else
                                <p class="text-muted mb-0">No cross-references available.</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@else
    <p class="text-muted">No disease lines have been associated with this study.</p>
@endif