@extends('website.app')

@section('contents')
<section class="thank-you-area" style="padding: 80px 0; text-align:center;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="thank-you-wrapper">
                    <img src="{{ asset('website/img/thank-you.svg') }}" alt="Thank You" style="max-width:200px; margin-bottom:20px;">
                    <h1>Thank You!</h1>
                    <p>Your order has been successfully submitted. We will process it shortly.</p>
                    <a href="{{ url('/') }}" class="common-btn btn-black mt-3">Return to Home</a>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
