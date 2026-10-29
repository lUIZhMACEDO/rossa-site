@extends('layout')

@section('title', 'Edit News Post')

@section('content')
<div class="container py-5">
    <div class="mb-4">
        <h1>Edit News Post</h1>
        <p class="text-muted mb-0">Changes are visible immediately.</p>
    </div>

    <form method="POST" action="{{ route('admin.news.update', $newsPost) }}" class="card card-body shadow-sm">
        @csrf
        @method('PUT')
        @include('admin.news._form', ['submitLabel' => 'Save Changes'])
    </form>
</div>
@endsection
