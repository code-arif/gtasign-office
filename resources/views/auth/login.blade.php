@extends('website.app')

@section('contents')
    <section class="login-screen-area">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="login-screen">
                        <div class="title-wrap text-center">
                            <h2>Log in</h2>
                            <p>Enter your email and password to sign in to your account.</p>
                        </div>

                        <div class="login-form">
                            {{-- Global error alert --}}
                            @if ($errors->any())
                                <div class="alert alert-danger mb-3">
                                    <ul class="mb-0">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('login') }}">
                                @csrf

                                <input type="hidden" name="role" value="user">

                                {{-- Email --}}
                                <div class="single-field">
                                    <input type="email" name="email" value="{{ old('email') }}" required
                                        placeholder="Your email address">
                                    @error('email')
                                        <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Branch Code --}}
                                <div class="single-field">
                                    <input type="text" name="branch_code" value="{{ old('branch_code') }}" required
                                        placeholder="Branch Code">
                                    @error('branch_code')
                                        <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Password --}}
                                <div class="single-field last-field">
                                    <input type="password" name="password" required placeholder="Password">
                                    @error('password')
                                        <span class="text-danger small d-block mt-1">{{ $message }}</span>
                                    @enderror
                                </div>

                                {{-- Remember --}}
                                <div class="single-checkbox">
                                    <div class="form-check">
                                        <input class="form-check-input" required type="checkbox" name="remember"
                                            id="flexCheckDefault">
                                        <label class="form-check-label" for="flexCheckDefault">
                                            Save ID
                                        </label>
                                    </div>
                                </div>

                                <div class="submit-btn">
                                    <button type="submit">Log In</button>
                                </div>
                            </form>
                        </div>

                        <div class="login-option">
                            <a href="{{ route('password.request') }}">Forgot your password ?</a>
                            <span></span>
                            <a href="{{ route('register') }}">Create new account</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
