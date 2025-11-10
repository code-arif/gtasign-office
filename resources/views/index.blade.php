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
    <!----- Responsive Menu Start ----->
    <div class="popup-menu">
        <div class="menu-inner">
            <div class="menu-top">
                <a href="index.html"><img src="{{ asset('website/img/logo.png') }}" alt=""></a>
                <button class="menu-x"><img src="{{ asset('website/img/times.svg') }} " alt=""></button>
            </div>
            <div class="mobile-nav">
                <ul>
                     <li><a href="product.html">FLEKSWISS</a></li>
                    <li><a href="product.html">PENADA</a></li>
                    <li><a href="product.html">PENADA KIDS</a></li>
                    <li><a href="product.html">CLROTTE</a></li>
                    <li><a href="product.html">KAYZZ</a></li>
                    <li><a href="product.html" class="stock-info">Stock Clearance</a></li>
                </ul>
            </div>
            <div class="user__information">
                <a href="login.html">Log In</a>
                <a href="register.html">Sign Up</a>
                <a href="shopping-cart-list.html">Cart</a>
            </div>
        </div>
    </div>
    <!----- Responsive Menu End ----->



    <!----- Search Popup Start ----->
    <div class="popup-search">
        <div class="search-inner">
            <div class="menu-top justify-end">
                <button class="search-x"><img src="{{ asset('website/img/times.svg') }}" alt=""></button>
            </div>
            <div class="search-bar">
                <form action="">
                    <label for="">Search</label>
                    <div class="search-field">
                        <input type="text">
                        <button><img src="{{ asset('website/img/search.svg') }}" alt=""></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!----- Search Popup End ----->



    <!--------- Header Area Start ------>
    <header class="header_area">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-xl-5 rs-mainmenu">
                    <div class="main-menu">
                        <nav>
                            <ul id="navigation">
                                <li><a href="product.html">FLEKSWISS</a></li>
                                <li><a href="product.html">PENADA</a></li>
                                <li><a href="product.html">PENADA KIDS</a></li>
                                <li><a href="product.html">CLROTTE</a></li>
                                <li><a href="product.html">KAYZZ</a></li>
                                <li><a href="product.html" class="stock-info">Stock Clearance</a></li>
                            </ul>
                        </nav>
                    </div>
                </div>
                <div class="col-xl-2 col-4">
                    <div class="logo">
                        <a href="index.html"><img src="{{ asset('website/img/logo.png') }}" alt=""></a>
                    </div>
                </div>
                <div class="col-xl-5 col-8">
                    <div class="header-right">
                        <div class="user-option">
                            <a href="login.html">Log In</a>
                            <a href="register.html">Sign Up</a>
                        </div>
                        <div class="search-btn">
                            <button><img src="assets/img/search.svg" alt=""></button>
                        </div>
                        <div class="cart-btn">
                            <a href="shopping-cart-list.html"><img src="assets/img/bag.svg" alt=""> <span>10</span></a>
                        </div>
                        <div class="menu_trigger">
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <!--------- Header Area End ------>



    <!--------- Hero Area Start ------>
    <section class="hero-area">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="hero-slider owl-carousel">
                        <div class="slider-item">
                            <img src="{{ asset('website/img/slide-1.jpg') }}" alt="">
                        </div>
                        <div class="slider-item">
                            <img src="{{ asset('website/img/slide-2.jpg') }}" alt="">
                        </div>
                        <div class="slider-item">
                            <img src="{{ asset('website/img/slide-3.jpg') }}" alt="">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--------- Hero Area End ------>




    <!--------- About Area Start ------>
    <section class="about-area">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="title-wrap text-center">
                        <h2>About Us</h2>
                    </div>
                </div>
                <div class="about-wrapper">
                    <div class="about-box box-left">
                        <p>Samjo Ko-Tech is a Korean manufacturer of optical frames based in Daegu, South Korea. We have been serving as an eyewear frame wholesaler in Malaysia for over 15 years.</p>
                        <p>The name “SJK”, short for the Korean phrase meaning “Union of Three,” <br> reflects our commitment to combining design, variety, and quality to inspire our customers.</p>
                    </div>
                    <div class="about-box box-right">
                        <p>We specialize in TR90 and Ultem frames, with TR90 being a premium 100% Swiss material. <br>  Every step of our process— from  <br>design to manufacturing—is done entirely in Korea.</p>
                        <p>100% Made in Korea.</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="about-card">
                        <img src="{{ asset('website/img/card-1.jpg') }}" class="card-thumbnail" alt="">
                        <div class="card-inner">
                            <h3>TR90 Specialists</h3>
                            <p>Premium material 100% <br> from Switzerland</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="about-card">
                        <img src="{{ asset('website/img/card-1.jpg') }} " class="card-thumbnail" alt="">
                        <div class="card-inner">
                            <h3>100% Made in South Korea</h3>
                            <p>Design to production, <br> all local</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="about-card">
                        <img src="{{ asset('website/img/card-1.jpg') }} " class="card-thumbnail" alt="">
                        <div class="card-inner">
                            <h3>15+ Years Expertise</h3>
                            <p>Malaysian market <br> since 2009</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--------- About Area End ------>



    <!--------- Delivary Info Area Start ------>
    <section class="delivary-info-area">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="title-wrap">
                        <h2>Order & Delivery</h2>
                        <p>Easy steps from signup to shipment</p>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="delivary-info-wrapper">
                        <div class="delivary-note">
                            <p>Note: Wholesale prices are only visible after logging in with your approved wholesale account.  Account Setup</p>
                        </div>
                        <div class="setup-box-wrap">
                            <div class="setup-box">
                                <div class="box-title">
                                    <h4>Account Setup</h4>
                                    <span><img src="assets/img/bill.svg" alt=""></span>
                                </div>
                                <div class="setup-description">
                                    <p>Visit our website and click “Sign Up”. Enter company name, registration number & contact details. Verify your email or WhatsApp confirmation code. Your wholesale account will be activated within 24 hours.</p>
                                </div>
                                <img src="assets/img/arrow.svg" class="box-arrow" alt="">
                            </div>
                            <div class="setup-box">
                                <div class="box-title">
                                    <h4>Place Your Order</h4>
                                    <span><img src="assets/img/basket.svg" alt=""></span>
                                </div>
                                <div class="setup-description">
                                    <p>Visit our website and click “Sign Up”. Enter company name, registration number & contact details. Verify your email or WhatsApp confirmation code. Your wholesale account will be activated within 24 hours.</p>
                                </div>
                                <img src="assets/img/arrow.svg" class="box-arrow" alt="">
                            </div>
                            <div class="setup-box">
                                <div class="box-title">
                                    <h4>Payment Methods</h4>
                                    <span><img src="assets/img/pay.svg" alt=""></span>
                                </div>
                                <div class="setup-description">
                                    <p>Visit our website and click “Sign Up”. Enter company name, registration number & contact details. Verify your email or WhatsApp confirmation code. Your wholesale account will be activated within 24 hours.</p>
                                </div>
                                <img src="assets/img/arrow.svg" class="box-arrow" alt="">
                            </div>
                            <div class="setup-box">
                                <div class="box-title">
                                    <h4>Shipping & Tracking</h4>
                                    <span><img src="assets/img/location.svg" alt=""></span>
                                </div>
                                <div class="setup-description">
                                    <p>Visit our website and click “Sign Up”. Enter company name, registration number & contact details. Verify your email or WhatsApp confirmation code. Your wholesale account will be activated within 24 hours.</p>
                                </div>
                                <img src="assets/img/arrow.svg" class="box-arrow" alt="">
                            </div>
                            <div class="setup-box">
                                <div class="box-title">
                                    <h4>Returns & After-Sales Service</h4>
                                    <span><img src="assets/img/headphones.svg" alt=""></span>
                                </div>
                                <div class="setup-description">
                                    <p>Visit our website and click “Sign Up”. Enter company name, registration number & contact details. Verify your email or WhatsApp confirmation code. Your wholesale account will be activated within 24 hours.</p>
                                </div>
                            </div>
                        </div>

                        <div class="setup-slider-wrap">
                            <div class="setup-slider owl-carousel">
                                <div class="setup-box">
                                    <div class="box-title">
                                        <h4>Account Setup</h4>
                                        <span><img src="assets/img/bill.svg" alt=""></span>
                                    </div>
                                    <div class="setup-description">
                                        <p>Visit our website and click “Sign Up”. Enter company name, registration number & contact details. Verify your email or WhatsApp confirmation code. Your wholesale account will be activated within 24 hours.</p>
                                    </div>
                                    <img src="assets/img/arrow.svg" class="box-arrow" alt="">
                                </div>
                                <div class="setup-box">
                                    <div class="box-title">
                                        <h4>Place Your Order</h4>
                                        <span><img src="assets/img/basket.svg" alt=""></span>
                                    </div>
                                    <div class="setup-description">
                                        <p>Visit our website and click “Sign Up”. Enter company name, registration number & contact details. Verify your email or WhatsApp confirmation code. Your wholesale account will be activated within 24 hours.</p>
                                    </div>
                                    <img src="assets/img/arrow.svg" class="box-arrow" alt="">
                                </div>
                                <div class="setup-box">
                                    <div class="box-title">
                                        <h4>Payment Methods</h4>
                                        <span><img src="assets/img/pay.svg" alt=""></span>
                                    </div>
                                    <div class="setup-description">
                                        <p>Visit our website and click “Sign Up”. Enter company name, registration number & contact details. Verify your email or WhatsApp confirmation code. Your wholesale account will be activated within 24 hours.</p>
                                    </div>
                                    <img src="assets/img/arrow.svg" class="box-arrow" alt="">
                                </div>
                                <div class="setup-box">
                                    <div class="box-title">
                                        <h4>Shipping & Tracking</h4>
                                        <span><img src="assets/img/location.svg" alt=""></span>
                                    </div>
                                    <div class="setup-description">
                                        <p>Visit our website and click “Sign Up”. Enter company name, registration number & contact details. Verify your email or WhatsApp confirmation code. Your wholesale account will be activated within 24 hours.</p>
                                    </div>
                                    <img src="assets/img/arrow.svg" class="box-arrow" alt="">
                                </div>
                                <div class="setup-box">
                                    <div class="box-title">
                                        <h4>Returns & After-Sales Service</h4>
                                        <span><img src="assets/img/headphones.svg" alt=""></span>
                                    </div>
                                    <div class="setup-description">
                                        <p>Visit our website and click “Sign Up”. Enter company name, registration number & contact details. Verify your email or WhatsApp confirmation code. Your wholesale account will be activated within 24 hours.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--------- Delivary Info Area End ------>



    <!--------- Discover Area Start ------>
    <section class="discover-area">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="discover-content">
                        <h2>Discover Our Premium <br> Eyeglass Frames</h2>
                        <div class="discover-text">
                            <p>Handcrafted in Korea for lasting comfort and style.
                            From timeless classics to modern silhouettes, find the perfect frame that
                            reflects who you are.</p>
                            <div class="view-btn">
                                <a href="#" class="common-btn">Browse Collection</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--------- Discover Area End ------>


     <!--------- Footer Area Start ------>
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-xl-4 col-lg-4">
                    <div class="footer-widget">
                        <h4>Our Address</h4>
                        <p>Samjo Ko-Tech Sdn Bhd <br>
                        No. 3-3, Jalan PJU 8/5H, Damansara Perdana, 47820 Petaling Jaya, Selangor, Malaysia</p>
                    </div>
                </div>
                <div class="col-xl-5 col-lg-6">
                    <div class="footer-widget">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="chlid-widget">
                                    <h4>Business Hours</h4>
                                    <p>Monday – Friday</p>
                                    <p>9:30 AM – 6:00 PM (GMT +8)</p>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="child-widget">
                                    <h4>Contact Info</h4>
                                    <ul>
                                        <li><img src="assets/img/phone.svg" alt=""> <span>(60) 16-723-1720 / (60) 3-7722-1126</span></li>
                                        <li><img src="assets/img/env.svg" alt=""> <span>samjoeyewear@gmail.com</span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-3 col-lg-2">
                    <div class="social-info">
                        <ul>
                            <li><a href=""><img src="assets/img/facebook.svg" alt=""></a></li>
                            <li><a href=""><img src="assets/img/instagram.svg" alt=""></a></li>
                            <li><a href=""><img src="assets/img/youtube.svg" alt=""></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    <!--------- Footer Area End ------>

    <!-- jQuery first, then Popper.js, then Bootstrap JS -->
    <script src="{{ asset('website/js/jquery.min.js') }} "></script>
    <script src="{{ asset('website/js/owl.carousel.min.js') }}"></script>
    <script src="{{ asset('website/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('website/js/main.js') }}"></script>
    <script>
        (function ($) {
    "use strict";

    jQuery(document).ready(function($){

            function initSetupSlider() {
                if ($(window).width() < 768) { // only mobile
                    if (!$('.setup-box-wrap').hasClass('owl-loaded')) {
                        $('.setup-box-wrap').owlCarousel({
                            items: 1,
                            margin: 15,
                            loop: false,
                            nav: true,
                            dots: true
                        });
                    }
                } else {
                    // destroy owl on larger screens
                    if ($('.setup-box-wrap').hasClass('owl-loaded')) {
                        $('.setup-box-wrap').trigger('destroy.owl.carousel').removeClass('owl-loaded owl-hidden');
                        $('.setup-box-wrap').find('.owl-stage-outer').children().unwrap();
                    }
                }
            }

            initSetupSlider();
            $(window).on('resize', initSetupSlider);

        });
    })(jQuery);

    </script>

</body>

</html>
