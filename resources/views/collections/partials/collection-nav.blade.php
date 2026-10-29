@php
    $navContext = $navContext
        ?? (request()->is('admin/collections*') ? 'admin' : 'investigator');

    $informationRoute = $navContext === 'admin'
        ? route('admin.collections.edit', $collection)
        : route('my-collections.edit', $collection);
    $studiesRoute = $navContext === 'admin'
        ? route('admin.collections.studies', $collection)
        : route('my-collections.studies', $collection);
    $reportRoute = route('collections.show', [
        'collection' => $collection,
        'from' => $navContext,
    ]);

    $informationActive = $navContext === 'admin'
        ? request()->routeIs('admin.collections.edit')
        : request()->routeIs('my-collections.edit');
    $studiesActive = $navContext === 'admin'
        ? request()->routeIs('admin.collections.studies')
        : request()->routeIs('my-collections.studies');
    $reportActive = request()->routeIs('collections.show');

    $user = auth()->user();
    $showWorkspaceTabs = $user && $collection->canUserView($user);
    $canEditMetadata = $user && $collection->canUserEditMetadata($user);
@endphp

@if($collection->exists)
    <ul class="nav nav-tabs mb-2">
        @if($showWorkspaceTabs)
            <li class="nav-item">
                <a href="{{ $informationRoute }}"
                    @class(['nav-link', 'active' => $informationActive])>
                    {{ $canEditMetadata ? 'Edit collection information' : 'Collection information' }}
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ $studiesRoute }}"
                    @class(['nav-link', 'active' => $studiesActive])>
                    Studies
                </a>
            </li>
        @endif
        <li class="nav-item">
            <a href="{{ $reportRoute }}"
                @class(['nav-link', 'active' => $reportActive])
                title="View only. This is the page visitors see.">
                Public report (view only)
            </a>
        </li>
    </ul>
    @if($showWorkspaceTabs)
        <p class="text-muted small mb-4">
            @if($canEditMetadata)
                This is the collection editor. Save, edit, and delete stay on these pages. The public report does not include them.
            @else
                You can review this collection. Changes cannot be saved. The public report is view only.
            @endif
        </p>
    @endif
@endif
