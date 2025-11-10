@extends('website.app')

@section('contents')
    <section class="login-screen-area reset-password">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="login-screen">
                        <div class="title-wrap text-center">
                            <h2>Password Reset</h2>
                            <p>We’ll send a password reset link to this email.</p>
                        </div>

                        {{-- need after success message --}}

                        @if (session('status'))
                            <div class="alert alert-success mt-3">
                                {{ session('status') }}
                            </div>
                        @endif


                        <div class="login-form">
                            <form method="POST" action="{{ route('password.email') }}">
                                @csrf


                                <div class="single-field last-field mb-5">
                                    {{-- <input type="email" placeholder="Your email address"> --}}
                                    <input class="form-control" name="email" id="eMail" placeholder="Enter Your Email"
                                        type="email" value="{{ old('email') }}">

                                    @error('email')
                                        <div class="alert alert-danger">{{ $message }}</div>
                                    @enderror

                                </div>

                                <div class="submit-btn">
                                    <button type="submit">Send Password Reset Link</button>
                                </div>


                            </form>
                        </div>
                        <div class="login-option mb-5">
                            <a href="#">I have a password. Sign in.</a>
                        </div>



                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
