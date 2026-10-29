@extends('layout')

@section('title', 'Contact Requests')

@section('content')
<div class="py-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1>Contact Requests</h1>
                <p class="text-muted">Manage and review contact form submissions</p>
            </div>
        </div>

        <div class="row mb-4">
            {{-- Search bar --}}
            <div class="col-md-12">
                <form method="GET" action="{{ route('admin.contact-requests') }}" class="d-flex">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Search by name, email or message..." 
                           value="{{ request('search') }}">
                    <button type="submit" class="btn btn-outline-primary ms-2">Search</button>
                    @if(request('search'))
                        <a href="{{ route('admin.contact-requests') }}" class="btn btn-outline-secondary ms-2">Clear</a>
                    @endif
                </form>
            </div>
        </div>

        <div>
            <p class="text-muted small mb-2"><strong>{{ $contactRequests->total() }}</strong> Total Requests</p>
        </div>
        

        <div class="card shadow-sm">
            <div class="card-body p-0">
                @if($contactRequests->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Message</th>
                                    <th>Submitted</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($contactRequests as $request)
                                    <tr>
                                        <td class="align-middle">
                                            <span class="badge bg-secondary">#{{ $request->id }}</span>
                                        </td>
                                        <td class="align-middle">{{ $request->name }}</td>
                                        <td class="align-middle">
                                            <a href="mailto:{{ $request->email }}">{{ $request->email }}</a>
                                        </td>
                                        <td class="align-middle">
                                            <div style="max-width: 400px;">
                                                {{ Str::limit($request->message, 100) }}
                                                @if(strlen($request->message) > 100)
                                                    <button class="btn btn-sm btn-link p-0" 
                                                            type="button" 
                                                            data-bs-toggle="collapse" 
                                                            data-bs-target="#message-{{ $request->id }}">
                                                        Show more
                                                    </button>
                                                    <div class="collapse mt-2" id="message-{{ $request->id }}">
                                                        <div class="card card-body">
                                                            {{ $request->message }}
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <small class="text-muted">
                                                {{ $request->created_at->format('M d, Y') }}<br>
                                                {{ $request->created_at->format('g:i A') }}
                                            </small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-5">
                        <p class="text-muted">No contact requests found.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Pagination --}}
        @if($contactRequests->hasPages())
            <div class="mt-4">
                {{ $contactRequests->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
