@extends('layout')

@section('title', 'Register Account')

@section('content')
<x-layouts.split-auth>
    @if ( Config::get('app.enable_registration') )
        <div class="card shadow-sm mb-3 overflow-hidden" style="border-left: 4px solid var(--bs-primary);">
            <div class="card-body px-4 pt-4 pb-0">
                <h1 class="h2 mb-0">Register a New Account</h1>
            </div>

            <form id="register-form" method="POST" action="{{ route('register.submit') }}">
                @csrf
                <input type="hidden" name="redirect" value="{{ $redirect ?? '/' }}">

                <div class="px-4 py-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label required">First Name</label>
                            <input type="text"
                                class="form-control @error('first_name') is-invalid @enderror"
                                id="first_name" name="first_name"
                                value="{{ old('first_name') }}"
                                required
                                autofocus>
                            @error('first_name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label required">Last Name</label>
                            <input type="text"
                                class="form-control @error('last_name') is-invalid @enderror"
                                id="last_name" name="last_name"
                                value="{{ old('last_name') }}"
                                autocomplete="family-name"
                                required>
                            @error('last_name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="px-4 py-3 bg-light">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="department" class="form-label required">Primary Department</label>
                            <input type="text"
                                class="form-control @error('department') is-invalid @enderror"
                                id="department" name="department"
                                value="{{ old('department') }}"
                                autocomplete="organization-title"
                                required>
                            @error('department') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="organization" class="form-label required">Primary Organization</label>
                            <input type="text"
                                class="form-control @error('organization') is-invalid @enderror"
                                id="organization" name="organization"
                                value="{{ old('organization') }}"
                                autocomplete="organization"
                                required>
                            @error('organization') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-12">
                            <label for="institution_type" class="form-label required">Institution Type</label>
                            <select class="form-select @error('institution_type') is-invalid @enderror"
                                id="institution_type"
                                name="institution_type"
                                required>
                                <option value="">Select type...</option>
                                <option value="academic_nonprofit" @selected(old('institution_type') === 'academic_nonprofit')>
                                    Academic or non-profit institution
                                </option>
                                <option value="commercial_for_profit" @selected(old('institution_type') === 'commercial_for_profit')>
                                    Commercial or for-profit organization
                                </option>
                            </select>
                            @error('institution_type') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="px-4 py-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="country" class="form-label required">Country</label>
                            <select class="form-select @error('country') is-invalid @enderror"
                                id="country"
                                name="country"
                                required>
                                <option value="">Select Country...</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->code }}" @selected(old('country') === $country->code)>
                                        {{ $country->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('country') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="state"
                                id="state-label"
                                class="form-label @if(old('country') === 'US') required @endif">State</label>
                            <select class="form-select @error('state') is-invalid @enderror"
                                id="state"
                                name="state"
                                @if(old('country') !== 'US') disabled @endif
                                @if(old('country') === 'US') required @endif>
                                <option value="">Select State...</option>
                                @foreach($states as $code => $name)
                                    <option value="{{ $code }}" @selected(old('state') === $code)>
                                        {{ $name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('state') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <div class="px-4 py-3 bg-light">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label required">Email</label>
                            <input type="email"
                                class="form-control @error('email') is-invalid @enderror"
                                id="email"
                                name="email"
                                value="{{ old('email') }}"
                                autocomplete="email"
                                required>
                            @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="password" class="form-label required">Password</label>
                            <input type="password"
                                class="form-control @error('password') is-invalid @enderror"
                                id="password"
                                name="password"
                                autocomplete="new-password"
                                required>
                            @error('password') <span class="invalid-feedback">{{ $message }}</span> @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label required">Confirm Password</label>
                            <input type="password"
                                class="form-control @error('password') is-invalid @enderror"
                                id="password_confirmation"
                                name="password_confirmation"
                                autocomplete="new-password"
                                required>
                        </div>
                    </div>
                </div>

                <div class="px-4 py-3">
                    <button type="submit" class="btn btn-primary w-100">Register</button>
                </div>
            </form>

            <div class="text-center px-4 py-3 bg-light border-top">
                <small class="text-muted">
                    <a href="{{ route('login', ['redirect' => $redirect ?? '/']) }}" class="text-decoration-none">Already have an account? Login</a>
                    <span class="mx-2">&bull;</span>
                    <a href="{{ route('forgot-password.show') }}" class="text-decoration-none">Forgot Password</a>
                </small>
            </div>

        </div>
    @else
        <div class="card shadow-sm mb-3" style="border-left: 4px solid var(--bs-primary);">
            <div class="card-body p-4 text-center">
                <h1 class="h2 mb-3">Registration Coming Soon</h1>
                <p class="text-muted">Registration is currently disabled. ROSSA is expected to release in January of 2026 for submission.</p>
                <div class="mt-4">
                    <small class="text-muted">
                        <a href="{{ route('login', ['redirect' => $redirect ?? '/']) }}" class="text-decoration-none">Login</a>
                        <span class="mx-2">&bull;</span>
                        <a href="{{ route('forgot-password.show') }}" class="text-decoration-none">Forgot Password</a>
                    </small>
                </div>
            </div>
        </div>
    @endif
</x-layouts.split-auth>

<script>
    const countrySelect = document.getElementById('country');
    const stateSelect = document.getElementById('state');
    const stateLabel = document.getElementById('state-label');

    countrySelect.addEventListener('change', function () {
        const isUS = this.value === 'US';
        stateSelect.disabled = !isUS;
        stateSelect.required = isUS;
        if (!isUS) stateSelect.value = '';
        stateLabel.classList.toggle('required', isUS);
    });
</script>

@endsection