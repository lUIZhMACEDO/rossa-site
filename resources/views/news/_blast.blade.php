{{--
    news._blast

    variant: 'default' (news index page, unchanged) or 'card' (homepage,
    used for every post the same way — no separate featured treatment).
    Falls back to 'default' when no variant is passed, so
    news/index.blade.php needs no changes.
--}}
@php
    $variant = $variant ?? 'default';
    $headingTag = $headingTag ?? 'h2';
@endphp

@if ($variant === 'card')
    <div class="card news-card">
        <div class="card-body d-flex flex-column gap-1">
            <span class="news-date-badge align-self-start">
                {{ $newsPost->published_at->format('F j, Y') }}
            </span>
            <{{ $headingTag }} class="news-title mb-0">{{ $newsPost->title }}</{{ $headingTag }}>
            <p class="news-excerpt mb-0 text-break" style="white-space: pre-line">{{ $newsPost->excerpt }}</p>
            @if ($newsPost->link_url)
                <a href="{{ $newsPost->link_url }}" class="d-inline-block mt-1 text-break">
                    {{ $newsPost->link_label }} <i class="bi-arrow-right" aria-hidden="true"></i>
                </a>
            @endif
        </div>
    </div>

@else
    <article class="border-bottom py-3">
        <div class="row gy-2">
            <div class="col-md-2">
                <time class="small text-muted" datetime="{{ $newsPost->published_at->toDateString() }}">
                    {{ $newsPost->published_at->format('F j, Y') }}
                </time>
            </div>
            <div class="col-md-10">
                <{{ $headingTag }} class="h5 mb-2">{{ $newsPost->title }}</{{ $headingTag }}>
                <p class="mb-0 text-break" style="white-space: pre-line">{{ $newsPost->excerpt }}</p>
                @if ($newsPost->link_url)
                    <a href="{{ $newsPost->link_url }}" class="d-inline-block mt-2 text-break">
                        {{ $newsPost->link_label }} <i class="bi-arrow-right" aria-hidden="true"></i>
                    </a>
                @endif
            </div>
        </div>
    </article>
@endif