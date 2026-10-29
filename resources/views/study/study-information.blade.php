@section('title', 'Study Information')

@section('guide-content')
    @include('guides.data-ingestion._01_study-info')
@endsection

<x-layouts.submission :$study>
<div class="pt-0 pb-5" @if($study->isLockedForEditing(auth()->user())) data-input-locked @endif>

    {{-- Page header --}}
    <div class="p-3 mb-4" style="background-color: #0D3B52; border-radius: 0.375rem;">
        <h1 class="mb-1" style="color: #fff;">Study Information</h1>
        <p class="mb-0" style="color: #E6F1FB;">
            Please add basic details about your study, including title, summary, funding, publication information.
        </p>
    </div>
    <div class="alert mb-4 d-flex align-items-center" role="alert" style="background-color: rgba(26, 125, 181, 0.08); border-left: 4px solid #1A7DB5; color: #14273f;">
    <i class="bi bi-info-circle-fill me-2" style="color: #1A7DB5; font-size: 1.5rem;"></i>
        <span><strong>Note:</strong> If you need help completing this form, the <strong>"? Help"</strong> button at the top right is available at any point. You can also invite others to help you with the submission process by clicking the <strong>Share</strong> button at the top right.</span>
    </div>
    
    {{-- @fragment('study-information-form') --}}
    <div class="card mb-4" style="border-left: 4px solid #0d3b52;">
        <div class="card-title text-white p-3 mb-0" style="background-color: #0d3b52;">
            <h3 class="mb-0">Study Information</h3>
        </div>
        <div class="card-body">
            <form id="study-information-form"
                method="POST"
                id="study-information-form"
                action="{{ route('study.study-information.store', ['study' => $study->id]) }}">
                @csrf
                
                <div class="mb-3">
                    <label for="title" class="form-label required">Study Title</label>
                    <input type="text" class="form-control @error('title') is-invalid @enderror" 
                        id="title" name="title" value="{{ old('title', $study->title) }}" 
                        @error('title') aria-describedby="title-error" @enderror>
                    @error('title')
                        <div id="title-error" class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            

                <div class="mb-3">
                    <label for="summary" class="form-label required">Study Summary</label>
                    <textarea class="form-control @error('summary') is-invalid @enderror" 
                            id="summary" name="summary" rows="4" 
                            @error('summary') aria-describedby="summary-error" @enderror>{{ old('summary', $study->summary) }}</textarea>
                    @error('summary')
                        <div id="summary-error" class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="funding_sources" class="form-label">Funding Sources</label>
                    <textarea class="form-control @error('funding_sources') is-invalid @enderror" 
                            id="funding_sources" name="funding_sources" rows="2"
                            @error('funding_sources') aria-describedby="funding_sources-error" @enderror>{{ old('funding_sources', $study->funding_sources) }}</textarea>
                    @error('funding_sources')
                        <div id="funding_sources-error" class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="conflicts" class="form-label">Conflicts of Interest</label>
                    <textarea class="form-control @error('conflicts') is-invalid @enderror" 
                            id="conflicts" name="conflicts" rows="2"
                            @error('conflicts') aria-describedby="conflicts-error" @enderror>{{ old('conflicts', $study->conflicts) }}</textarea>
                    @error('conflicts')
                        <div id="conflicts-error" class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                <div class="mb-3">
                    <div class="col-md-3">
                        <label for="completion_date" class="form-label required">Study Completion Date</label>
                        <input type="date" class="form-control @error('completion_date') is-invalid @enderror" 
                            id="completion_date" name="completion_date" 
                            value="{{ old('completion_date', $study->completion_date?->format('Y-m-d')) }}"
                            @error('completion_date') aria-describedby="completion_date-error" @enderror>
                        @error('completion_date')
                            <div id="completion_date-error" class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-4" style="border-left: 4px solid #0d3b52;">
        <div class="card-title text-white p-3 mb-0" style="background-color: #0d3b52;">
            <h3 class="mb-0">Publication Information</h3>
        </div>
        <div class="card-body">
            <div x-data="{ 
                isPublished: {{ old('is_published', $study->is_published) === null ? 'true' : (old('is_published', $study->is_published) ? 'true' : 'false') }},
                publicationPlan: '{{ old('publication_plan', $study->is_published ? '' : ($study->publication_plan ?? '')) }}',
                doi: '{{ old('doi', $study->doi ?? '') }}',
                pubmedId: '{{ old('pubmed_id', $study->pubmed_id ?? '') }}',
                embargoStartDate: '{{ old('embargo_start_date', $study->embargo_start_date?->format('Y-m-d')) ?? '' }}',
                embargoMonths: @json((int) old('embargo_months', $study->embargo_months ?? 0)),
                reminderDate() { 
                    if (!this.embargoStartDate || !this.embargoMonths || this.embargoMonths <= 0) {
                        return null;
                    }
                    let months = parseInt(this.embargoMonths);
                    let d = new Date(this.embargoStartDate);
                    d.setMonth(d.getMonth() + months);
                    return d.toISOString().split('T')[0];
                },
                clearPublicationPlan() { this.publicationPlan = '' },
                {{-- showSpecialIssueInfo() { return this.publicationPlan === 'special_issue' }, --}}
                showEmbargoField() { return this.publicationPlan === 'different_journal' }
            }">
                <div class="mb-3">
                    <label class="form-label required">Has this study been published?</label>
                    <div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio"
                                name="is_published" id="published_yes" value="1" 
                                x-model="isPublished"
                                form="study-information-form"
                                @error('is_published') aria-describedby="is_published-error" @enderror>
                            <label class="form-check-label" for="published_yes">Yes</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio"
                                name="is_published" id="published_no" value="0" 
                                x-model="isPublished"
                                form="study-information-form"
                                @error('is_published') aria-describedby="is_published-error" @enderror>
                            <label class="form-check-label" for="published_no">No</label>
                        </div>
                        @error('is_published')
                            <div id="is_published-error" class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <!-- Published Fields -->
                <div x-show="isPublished == true">
                    <div class="mb-3">
                        <label for="doi" class="form-label required">Digital Object Identifier (DOI)</label>
                        <div class="d-flex flex-wrap align-items-center gap-2 mt-1 small text-muted">
                            <div id="doi-validation"></div> {{-- replaced by study.partials.url-validation --}}
                            <span>https://doi.org/<span x-text="doi"></span></span>
                        </div>
                        <input type="text" class="form-control @error('doi') is-invalid @enderror" id="doi" name="doi" 
                            x-model="doi"
                            form="study-information-form"
                            placeholder="xxxxx/xxxxx"
                            hx-post="{{ route('study.validate-doi', $study) }}"
                            hx-trigger="keyup changed delay:500ms"
                            hx-target="#doi-validation"
                            hx-headers='{"X-CSRF-Token": "{{ csrf_token() }}"}'
                            @error('doi') aria-describedby="doi-error" @enderror>
                        @error('doi')
                            <div id="doi-error" class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="pubmed_id" class="form-label required">PubMed ID</label>
                        <div class="d-flex flex-wrap align-items-center gap-2 mt-1 small text-muted">
                            <div id="pubmed-validation"></div> {{-- replaced by study.partials.url-validation --}}
                            <span>https://pubmed.ncbi.nlm.nih.gov/<span x-text="pubmedId"></span></span>
                        </div>
                        <input type="text" class="form-control @error('pubmed_id') is-invalid @enderror" id="pubmed_id" name="pubmed_id" 
                            x-model="pubmedId"
                            form="study-information-form"
                            hx-post="{{ route('study.validate-pubmed-id', $study) }}"
                            hx-trigger="keyup changed delay:500ms"
                            hx-target="#pubmed-validation"
                            hx-headers='{"X-CSRF-Token": "{{ csrf_token() }}"}'
                            @error('pubmed_id') aria-describedby="pubmed_id-error" @enderror>
                        @error('pubmed_id')
                            <div id="pubmed_id-error" class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
                </div>

                <!-- Unpublished Fields -->
                <div x-show="isPublished == false">
                    <label class="form-label required">Publication Plan</label>
                    <div>
                        {{-- <div class="form-check">
                            <input class="form-check-input" type="radio" 
                                id="special_issue" name="publication_plan" value="special_issue"
                                x-model="publicationPlan"
                                form="study-information-form"
                                @error('publication_plan') aria-describedby="publication_plan-error" @enderror>
                            <label class="form-check-label" for="special_issue">
                                I would like to publish in the skeletal phenotyping special issue of Bone
                            </label>
                        </div> --}}
                        <div class="form-check">
                            <input class="form-check-input" type="radio" 
                                id="different_journal" name="publication_plan" value="different_journal"
                                x-model="publicationPlan"
                                form="study-information-form"
                                @error('publication_plan') aria-describedby="publication_plan-error" @enderror>
                            <label class="form-check-label" for="different_journal">
                                I am currently submitting this study to a journal for peer review and need an embargo period
                            </label>
                            <button type="button" class="btn btn-sm btn-outline-danger ms-2" @click="clearPublicationPlan()">Clear Selection</button>
                        </div>
                        @error('publication_plan')
                            <div id="publication_plan-error" class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>


                    {{-- <div>
                        <div class="form-check">
                            <input type="radio" class="form-check-input" id="screening_study" name="publication_plan" value="screening_study"
                                x-model="publicationPlan"
                                form="study-information-form">
                            <label for="screening_study" class="form-check-label">This is an unpublished screening study.</label>
                            <div class="form-text">We'd still love to include your data, but this form might not be suitable.  Please contact us at <a href="mailto:{{ config('mail.rossa_contact_email') }}">{{ config('mail.rossa_contact_email') }}</a>.</div>
                        </div>
                        
                    </div> --}}

                    {{-- <div x-show="showSpecialIssueInfo()" class="mt-2">
                        <p>
                            To learn more about this special skeletal phenotyping issue, please 
                            <a href="#" target="_blank">click here</a>
                        </p>
                    </div> --}}

                    <div x-show="showEmbargoField()" class="mt-3">
                        {{-- Embargo period start date --}}
                        <div class="col">
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="embargo_start_date" class="form-label required">Embargo Period Start Date</label>
                                    <input type="date" class="form-control @error('embargo_start_date') is-invalid @enderror" 
                                        id="embargo_start_date" name="embargo_start_date" 
                                        value="{{ old('embargo_start_date', $study->embargo_start_date?->format('Y-m-d')) }}"
                                        form="study-information-form"
                                        x-model="embargoStartDate"
                                        @if($study->submitted_at) readonly @endif
                                        @error('embargo_start_date') aria-describedby="embargo_start_date-error" @enderror>
                                    @if($study->submitted_at)
                                        <div class="form-text">The embargo start date cannot be changed after submission.</div>
                                    @endif
                                    @error('embargo_start_date')
                                        <div id="embargo_start_date-error" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="mb-3">
                                    <label for="embargo_months" class="form-label required">Estimated Embargo Period (months)</label>
                                    <input type="number" class="form-control @error('embargo_months') is-invalid @enderror" id="embargo_months" name="embargo_months" 
                                        min="0" value="{{ old('embargo_months', $study->embargo_months) }}" form="study-information-form"
                                        x-model.number="embargoMonths"
                                        @error('embargo_months') aria-describedby="embargo_months-error" @enderror>
                                    @error('embargo_months')
                                        <div id="embargo_months-error" class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-info" role="alert">
                            <strong>Reminder Date:</strong>
                            <template x-if="reminderDate()">
                                <span>You will be reminded on <span x-text="reminderDate()">{{ old('embargo_start_date', $study->embargo_start_date?->format('Y-m-d')) && old('embargo_months', $study->embargo_months) ? \Illuminate\Support\Carbon::parse(old('embargo_start_date', $study->embargo_start_date?->format('Y-m-d')))->addMonths((int) old('embargo_months', $study->embargo_months))->toDateString() : '' }}</span></span>
                            </template>
                            <template x-if="!reminderDate()">
                                <span>Please select both an embargo start date and embargo period to calculate the reminder date.</span>
                            </template>
                        </div>
                        <div class="mt-2 text-muted small">
                            <p>To ensure data quality, ROSSA only accepts skeletal phenotyping data from studies that have undergone peer review and have been published. Please provide an estimated embargo period. We will contact you at the end of this embargo period to verify publication of your manuscript. If your manuscript has not yet been published, we will ask you to extend your embargo period. If your manuscript has been published, we will ask you for your DOI and PubMed ID. Thank you!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <x-session-alerts />

    <div class="mt-4 d-flex justify-content-end">
        <button type="submit" class="btn btn-primary btn-lg" form="study-information-form">Save & Continue <i class="bi-arrow-right"></i></button>
    </div>
    {{-- @endfragment --}}
</div>
</x-layouts.submission>