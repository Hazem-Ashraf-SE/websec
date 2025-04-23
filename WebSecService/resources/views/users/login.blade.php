@extends('layouts.app')
@section('title', 'Login')
@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Login</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('do_login') }}">
                        @csrf
                        @foreach($errors->all() as $error)
                        <div class="alert alert-danger">
                            <strong>Error!</strong> {{$error}}
                        </div>
                        @endforeach
                        <div class="mb-3">
                            <label for="email" class="form-label">Email:</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password:</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Login</button>
                            <a href="{{ route('register') }}" class="btn btn-outline-secondary">Register New Account</a>
                        </div>
                        <div class="form-group mb-2">
                            <a href="{{ route('login_with_google') }}" class="btn btn-success">Login with Google</a>
                            <a href="{{ route('redirectToFacebook') }}" class="btn btn-success">Login with Facebook</a>

                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
