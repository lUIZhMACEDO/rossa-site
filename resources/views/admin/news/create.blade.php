@extends('layout')

@section('title', 'Create News Post')

@section('content')
<div class="container py-5">
    <div class="mb-4">
        <h1>Create News Post</h1>
        <p class="text-muted mb-0">The post will be published immediately.</p>
    </div>

    <form method="POST" action="{{ route('admin.news.store') }}" class="card card-body shadow-sm">
        @csrf
        @include('admin.news._form', ['submitLabel' => 'Publish News Post'])
    </form>
</div>
@endsection
