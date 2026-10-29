@extends('layout')

@section('content')
<div class="py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card mb-3">
                    <div class="card-body">
                        <h1>Forgot Password</h1>
                        <form method="POST" action="{{ route('forgot-password.submit') }}">
                            @csrf

                            <div class="form-group mb-3">
                                <label for="email">Email address</label>
                                <input type="email" class="form-control" id="email" name="email" required autofocus>
                            </div>

                            <button type="submit" class="btn btn-primary">Submit</button>
                        </form>
                        
                    </div>
                </div>

                <p class="small text-center">
                    <a href="{{ route('login') }}">Login</a>
                    &bull;
                    <a href="{{ route('register.show') }}">Register New Account</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection