@if (!app()->environment('production') || (auth()->check() && auth()->user()->isAdmin()))
    <div {{$attributes}}>
        <h6 class="mb-3">During development, we'd love your feedback on issues, bugs, user experience issues, frustrations, etc.</h6>
        <form action="{{ route('feedback.store') }}" method="POST" class="feedback-form">
            @csrf
            <div class="mb-3">
                <textarea class="form-control" id="feedback" name="feedback" rows="4" placeholder="Enter your feedback here"></textarea>
            </div>

            <button class="btn btn-secondary">Send Feedback</button>
        </form>
    </div>
@endif
