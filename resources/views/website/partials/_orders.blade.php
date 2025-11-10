<section class="delivary-info-area">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="title-wrap">
                        <h2>Order & Delivery</h2>

                        @if ($order)
                            <p> {{ $order ? $order->sub_description : '' }} </p>
                        @else
                            <p>Easy steps from signup to shipment</p>
                        @endif


                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="delivary-info-wrapper">
                        <div class="delivary-note">

                            @if ($order)
                                <p> Note: {{ $order ? $order->description : '' }} </p>
                            @else
                                <p>
                                    Note: Wholesale prices are only visible after logging in with your approved wholesale
                                    account. Account Setup
                                </p>
                            @endif


                        </div>


                        @if ($order_items->count() > 0)
                            <div class="setup-box-wrap">
                                @foreach ($order_items as $item)
                                    <div class="setup-box">
                                        <div class="box-title">
                                            <h4>{{ $item->title }}</h4>
                                            <span><img src="{{ asset($item->image) }}" alt=""></span>
                                        </div>
                                        <div class="setup-description">
                                            <p>{!! $item->description !!}</p>
                                        </div>
                                        <img src="{{ asset('website/img/arrow.svg') }}" class="box-arrow" alt="">
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="setup-box-wrap">
                                <div class="setup-box">
                                    <div class="box-title">
                                        <h4>Account Setup</h4>
                                        <span><img src="assets/img/bill.svg" alt=""></span>
                                    </div>
                                    <div class="setup-description">
                                        <p>Visit our website and click “Sign Up”. Enter company name, registration number &
                                            contact details. Verify your email or WhatsApp confirmation code. Your wholesale
                                            account will be activated within 24 hours.</p>
                                    </div>
                                    <img src="assets/img/arrow.svg" class="box-arrow" alt="">
                                </div>
                                <div class="setup-box">
                                    <div class="box-title">
                                        <h4>Place Your Order</h4>
                                        <span><img src="assets/img/basket.svg" alt=""></span>
                                    </div>
                                    <div class="setup-description">
                                        <p>Visit our website and click “Sign Up”. Enter company name, registration number &
                                            contact details. Verify your email or WhatsApp confirmation code. Your wholesale
                                            account will be activated within 24 hours.</p>
                                    </div>
                                    <img src="assets/img/arrow.svg" class="box-arrow" alt="">
                                </div>
                                <div class="setup-box">
                                    <div class="box-title">
                                        <h4>Payment Methods</h4>
                                        <span><img src="assets/img/pay.svg" alt=""></span>
                                    </div>
                                    <div class="setup-description">
                                        <p>Visit our website and click “Sign Up”. Enter company name, registration number &
                                            contact details. Verify your email or WhatsApp confirmation code. Your wholesale
                                            account will be activated within 24 hours.</p>
                                    </div>
                                    <img src="assets/img/arrow.svg" class="box-arrow" alt="">
                                </div>
                                <div class="setup-box">
                                    <div class="box-title">
                                        <h4>Shipping & Tracking</h4>
                                        <span><img src="assets/img/location.svg" alt=""></span>
                                    </div>
                                    <div class="setup-description">
                                        <p>Visit our website and click “Sign Up”. Enter company name, registration number &
                                            contact details. Verify your email or WhatsApp confirmation code. Your wholesale
                                            account will be activated within 24 hours.</p>
                                    </div>
                                    <img src="assets/img/arrow.svg" class="box-arrow" alt="">
                                </div>
                                <div class="setup-box">
                                    <div class="box-title">
                                        <h4>Returns & After-Sales Service</h4>
                                        <span><img src="assets/img/headphones.svg" alt=""></span>
                                    </div>
                                    <div class="setup-description">
                                        <p>Visit our website and click “Sign Up”. Enter company name, registration number &
                                            contact details. Verify your email or WhatsApp confirmation code. Your wholesale
                                            account will be activated within 24 hours.</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
