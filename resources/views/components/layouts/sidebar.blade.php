@php $showFeedback = false; @endphp
@php $hideSessionAlerts = true; @endphp
@extends('layout')

@section('content')
@isset($header)
    {{ $header }}
@endisset
<div class="sidebar-layout d-flex flex-xl-nowrap" data-sidebar-layout>
    <aside id="submission-sidebar"
        class="sidebar-column bg-light flex-xl-shrink-0"
        data-sidebar-panel>
        <button type="button"
            class="sidebar-toggle sidebar-rail-btn d-xl-none"
            aria-controls="submission-sidebar"
            aria-expanded="false"
            aria-label="Open form sections menu"
            data-sidebar-toggle>
            <i class="bi bi-list" aria-hidden="true"></i>
        </button>
        <div class="sidebar-inner">
            <div class="sidebar-content" data-sidebar-inert>
                {{ $sidebar }}
            </div>
        </div>
    </aside>
    <button type="button"
        class="sidebar-backdrop d-xl-none"
        tabindex="-1"
        aria-label="Close form sections menu"
        data-sidebar-backdrop
        hidden></button>
    <div class="main-content flex-grow-1 w-100" style="min-width: 0; max-width: 1320px;">
        <x-session-alerts />
        {{ $slot }}
        <div class="d-block px-4 mb-5"><x-feedback-form class="border border-2 p-3" /></div>
    </div>
</div>
@endsection
