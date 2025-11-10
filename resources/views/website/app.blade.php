<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>SKJ</title>
    <link href="{{ asset('website/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('website/css/owl.carousel.css') }}" rel="stylesheet">
    <link href="{{ asset('website/css/style.css') }}" rel="stylesheet">
    <link href="{{ asset('website/css/responsive.css') }}" rel="stylesheet">

</head>

<body>

    <div class="overlay-bg"></div>


    <!--------- header start ------>

    @include('website.partials.header')


    @yield('contents')

    {{-- @stack('scripts') --}}


     <!--------- Footer Area Start ------>
    @include('website.partials.footer')
    <!--------- Footer Area End ------>




    <!-- jQuery first, then Popper.js, then Bootstrap JS -->

    @include('website.partials.scripts')


</body>

</html>
