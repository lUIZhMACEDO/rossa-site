@extends('layout')

@section('content')
<div class="container mt-5">
    <h1>ROSSA User Feedback</h1>
    <div class="card">
        <div class="card-body">
            @if ( $feedbacks->isEmpty() )
                <p class="card-text">No feedbacks are available yet.</p>
            @endif

            <div class="col-md-3">
                <form action="{{ route('admin.feedback') }}" method="GET">
                    <div class="d-flex align-items-end gap-3">
                        <div>
                            <label for="status" class="form-label">Status</label>
                            <select name="status" onchange="this.form.submit()" class="form-select" style="min-width: 200px;">
                                <option value="all" {{ !request('status') ? 'selected' : '' }}>All</option>
                                <option value="unresolved" {{ request('status') === 'unresolved' ? 'selected' : '' }}>Unresolved</option>
                                <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                            </select>
                        </div>
                        <div>
                            <label for="order" class="form-label">Order</label>
                            <select name="order" onchange="this.form.submit()" id="order-select" class="form-select" style="min-width: 200px;">
                                <option value="desc" {{ request('order', 'desc') === 'desc' ? 'selected' : '' }}>Newest First</option>
                                <option value="asc" {{ request('order') === 'asc' ? 'selected' : '' }}>Oldest First</option>
                            </select>
                        </div>
                    </div>
                    
                </form>
            </div>
                
                {{ $feedbacks->links() }}


                <table class="table table-striped table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>User Email</th>
                            <th>Feedback</th>
                            <th>Page</th>
                            <th>Created At</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ( $feedbacks as $feedback )
                            <tr>
                                <td>{{ $feedback->id }}</td>
                                <td>{{ $feedback->user ? $feedback->user->email : 'Anonymous' }}</td>
                                <td>{{ $feedback->feedback }}</td>
                                <td>
                                    @if($feedback->previous_url)
                                        <a href="{{ $feedback->previous_url }}" target="_blank" rel="noopener noreferrer">
                                            {{ $feedback->previous_url }}
                                        </a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-nowrap">{{ $feedback->created_at->format('Y-m-d') }}</td>
                                <td>
                                    @if($feedback->resolved)
                                        <span class="badge bg-success">Resolved</span>
                                    @else
                                        <span class="badge text-bg-warning">Unresolved</span>
                                    @endif
                                </td>
                                @if (auth()->user()->isAdmin())
                                    <td>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-light border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li>
                                                    <h6 class="dropdown-header">Actions</h6>
                                                </li>
                                                <li>
                                                    <form action="{{ route('feedback.toggle', $feedback->id) }}" method="POST" class="m-0">
                                                        @csrf
                                                        @method('PATCH')
                                                        <button type="submit" class="dropdown-item">
                                                            @if($feedback->resolved)
                                                                Set as Unresolved
                                                            @else
                                                                Set as Resolved
                                                            @endif
                                                        </button>
                                                    </form>
                                                </li>
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form action="{{ route('feedback.delete', $feedback->id) }}" method="POST" class="m-0" onsubmit="return confirm('Are you sure you want to delete this feedback?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger">
                                                            <i class="bi bi-trash me-2"></i> Delete
                                                        </button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>

            {{ $feedbacks->links() }}
        </div>
    </div>
</div>
@endsection