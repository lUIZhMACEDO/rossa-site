@props(['title' => 'Account'])

@php $showFeedback = false; @endphp

@extends('layout')

@section('title', $title)

@section('content')
<div class="container py-4">
    <div class="row g-4">
        <div class="col-lg-3">
            <x-account.sidebar />
        </div>
        <div class="col-lg-9">
            {{ $slot }}
        </div>
    </div>
</div>
@endsection
