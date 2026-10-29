<ul class="nav nav-tabs report-tabs" role="tablist">
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ request()->routeIs('search.show') ? 'active' : '' }}" href="{{ route('search.show', $study) }}">
            Information
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ request()->routeIs('search.data-exploration') ? 'active' : '' }}" href="{{ route('search.data-exploration', $study) }}">
            Data Exploration
        </a>
    </li>
    <li class="nav-item" role="presentation">
        <a class="nav-link {{ request()->routeIs('search.data-download') ? 'active' : '' }}" href="{{ route('search.data-download', $study) }}">
            Download Original Data
        </a>
    </li>
</ul>