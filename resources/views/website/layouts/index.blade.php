@extends('website.app')

@section('contents')
    <!--------- header end ------>


    <!--------- Hero Area Start ------>
    <section class="hero-area">
        <div class="container">
            <div class="row">

                @if ($banners->count() > 0 && $banners != null)

                    <div class="col-lg-12">
                        <div class="hero-slider owl-carousel">
                            @foreach ($banners as $banner)
                                <div class="slider-item">
                                    <img src="{{ asset($banner->image) }}" alt="">
                                </div>
                            @endforeach
                        </div>
                    </div>

                @else
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
                @endif

            </div>
        </div>
    </section>
    <!--------- Hero Area End ------>




    <!--------- About Area Start ------>
    @include('website.partials._about')
    <!--------- About Area End ------>



    <!--------- Delivary Info Area Start ------>
    @include('website.partials._orders')
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

                                @php
                                    $category = App\Models\Category::first();
                                    // dd($category);
                                @endphp


                                <a href="{{ route('website.product.index', $category->slug ) }}" class="common-btn">Browse Collection</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!--------- Discover Area End ------>
@endsection
