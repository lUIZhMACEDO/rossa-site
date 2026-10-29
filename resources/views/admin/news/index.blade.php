@extends('layout')

@section('title', 'Manage News')

@section('content')
<div class="container py-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h1 class="mb-1">News</h1>
            <p class="text-muted mb-0">Create and manage public ROSSA news posts.</p>
        </div>
        <a href="{{ route('admin.news.create') }}" class="btn btn-primary">Create News Post</a>
    </div>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Post</th>
                        <th>Published</th>
                        <th>Last updated</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($newsPosts as $newsPost)
                        <tr>
                            <td>
                                <a class="fw-semibold" href="{{ route('admin.news.edit', $newsPost) }}">{{ $newsPost->title }}</a>
                                <div class="small text-muted">{{ $newsPost->excerpt }}</div>
                            </td>
                            <td class="text-nowrap">{{ $newsPost->published_at->format('M j, Y') }}</td>
                            <td class="text-nowrap">{{ $newsPost->updated_at->format('M j, Y') }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('admin.news.edit', $newsPost) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form method="POST" action="{{ route('admin.news.destroy', $newsPost) }}" class="d-inline"
                                      onsubmit="return confirm('Permanently delete this news post?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-5">No news posts have been published.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($newsPosts->hasPages())
            <div class="card-footer">
                {{ $newsPosts->links() }}
            </div>
        @endif
    </div>
</div>
@endsection