@extends('website.app')

@section('contents')
    <!--------- Product Area Start ------>
    <section class="login-screen-area">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="title-wrap text-center">
                        <h2>Create new account</h2>
                        <p>Create an account to track your future orders, checkout faster, and sync your favorites.</p>
                    </div>

                    <div class="login-screen">
                        <div class="login-form">
                            <form method="POST" action="{{ route('register') }}">
                                @csrf

                                {{-- Customer Name --}}
                                <div class="single-field">
                                    <input type="text" name="name" value="{{ old('name') }}" required
                                        placeholder="Customer’s Name">
                                    @error('name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Branch Code --}}
                                <div class="single-field">
                                    <input type="text" name="branch_name" value="{{ old('branch_name') }}" required
                                        placeholder="Branch Code">
                                    @error('branch_name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Email --}}
                                <div class="single-field">
                                    <input type="email" name="email" value="{{ old('email') }}" required
                                        placeholder="Your email address">
                                    @error('email')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Phone --}}
                                <div class="single-field">
                                    <input type="text" name="phone_number" value="{{ old('phone_number') }}" required
                                        placeholder="Phone number">
                                    @error('phone_number')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Password --}}
                                <div class="single-field last-field">
                                    <input type="password" name="password" required autocomplete="new-password"
                                        placeholder="Password">
                                    @error('password')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                {{-- Confirm Password --}}
                                <div class="single-field last-field">
                                    <input type="password" name="password_confirmation" required autocomplete="new-password"
                                        placeholder="Confirm Password">
                                </div>

                                <div class="submit-btn mt-5">
                                    <button type="submit">Register</button>
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
