@extends('layout')

@section('content')
<div class="py-5">
    <div class="container mt-4">
        <h1>Ontology Admin</h1>
        <p class="text-muted">View ontology connections and mappings within ROSSA.</p>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <ul class="nav nav-tabs" id="ontologyAdminTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <a @class(['nav-link', 'active' => request()->routeIs('admin.ontology', 'admin.ontology.animal-lines')]) id="animal-lines-tab" href="{{ route('admin.ontology.animal-lines') }}" aria-selected="{{ request()->routeIs('admin.ontology', 'admin.ontology.animal-lines') ? 'true' : 'false' }}">
                    Animal Strains {{--({{ $animalLines->total() ?? 0 }})--}}
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a @class(['nav-link', 'active' => request()->routeIs('admin.ontology.disease-lines')]) id="disease-lines-tab" href="{{ route('admin.ontology.disease-lines') }}" aria-selected="{{ request()->routeIs('admin.ontology.disease-lines') ? 'true' : 'false' }}">
                    Disease Lines {{--({{ $diseaseLines->total() ?? 0 }})--}}
                </a>
            </li>
        </ul>

        <div class="tab-content" id="ontologyAdminTabsContent">
            <div class="tab-pane fade show active" id="tabContent" role="tabpanel" aria-labelledby="animal-lines-tab">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
@endsection
