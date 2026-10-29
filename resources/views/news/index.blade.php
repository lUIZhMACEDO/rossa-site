@extends('layout')

@section('title', 'News')
@section('description', 'The latest releases, events, workshops, and announcements from the Rodent Open Science Skeletal Archive.')

@section('content')
<div class="container py-3">
    <div class="news-hero-band rounded-3 px-4 py-4 px-md-5 py-md-4 mb-3">
        <h1 class="text-white mb-2 fs-2">ROSSA News</h1>
        <p class="lead mb-0 news-hero-subtitle">Releases, events, workshops, and other updates from ROSSA.</p>
    </div>

    <div class="news-scroll-area d-flex flex-column gap-3">
        @forelse ($newsPosts as $newsPost)
            @include('news._blast', ['headingTag' => 'h2', 'variant' => 'card'])
        @empty
            <p class="text-muted">There are no news posts yet.</p>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $newsPosts->links() }}
    </div>
</div>
@endsection