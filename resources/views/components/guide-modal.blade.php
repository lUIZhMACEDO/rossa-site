<div class="modal fade" id="guideModal" tabindex="-1" aria-labelledby="guideModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <a href="@yield('guide-link', \App\Support\SubmissionGuide::url() ?? route('guides.data-ingestion'))" class="btn btn-link position-absolute end-0 me-5 mt-2" target="_blank">
                    View The Full Guide
                </a>
                <h5 class="modal-title" id="guideModalLabel">Help</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body guide-headers">
                @section('guide-content')
                    <p>View the full data ingestion guide <a href="{{ route('guides.data-ingestion') }}" target="_blank">here</a>.</p>
                @show
            </div>
            <div class="modal-footer">
                <a href="@yield('guide-link', \App\Support\SubmissionGuide::url() ?? route('guides.data-ingestion'))" class="btn btn-link me-5 mt-2" target="_blank">
                    View The Full Guide
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>