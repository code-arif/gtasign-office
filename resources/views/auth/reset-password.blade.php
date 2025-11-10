@extends('website.app')

@section('contents')
    <section class="login-screen-area reset-password">
        <div class="container mb-5">
            <div class="row">
                <div class="col-lg-12">
                    <div class="login-screen">
                        <div class="title-wrap text-center">
                            <h2>Password Reset</h2>
                            <p>We’ll send a password reset link to this email.</p>
                        </div>
                        <div class="login-form">
                            <form method="POST" action="{{ route('password.store') }}">
                                @csrf

                                <!-- Password Reset Token -->
                                <input type="hidden" name="token" value="{{ $request->route('token') }}">


                                <div class="single-field last-field mb-5">
                                    {{-- <input type="email" placeholder="Your email address"> --}}
                                    <input  name="email" id="email" placeholder="Enter Your Email"
                                        type="email" value="{{ old('email') }}">
                                    @error('email')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror

                                </div>

                                <div class="single-field last-field mb-5">
                                    {{-- <input type="email" placeholder="Your email address"> --}}
                                    <input  name="password" id="password"
                                        placeholder="Enter Your Password" type="password" value="{{ old('password') }}">
                                    @error('password')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror

                                </div>

                                <div class="single-field last-field mb-5">
                                    {{-- <input type="email" placeholder="Your email address"> --}}
                                    <input  name="password_confirmation" id="password_confirmation"
                                        placeholder="Enter Your Confirm Password" type="password"
                                        value="{{ old('password_confirmation') }}">
                                    @error('password')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror

                                </div>



                                <div class="submit-btn">
                                    <button type="submit">
                                        Reset Password
                                    </button>
                                </div>


                            </form>
                        </div>
                        <div class="login-option">
                            <a href="{{ route('login') }}">I have a password. Sign in.</a>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
