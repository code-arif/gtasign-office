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

                    @if ($about_us)
                        <p> {{ $about_us ? $about_us->first_description : '' }} </p>
                    @else
                        <p>Samjo Ko-Tech is a Korean manufacturer of optical frames based in Daegu, South Korea. We have
                            been serving as an eyewear frame wholesaler in Malaysia for over 15 years.</p>
                    @endif



                    @if ($about_us)
                        <p> {{ $about_us ? $about_us->second_description : '' }} </p>
                    @else
                        <p>The name “SJK”, short for the Korean phrase meaning “Union of Three,” <br> reflects our
                            commitment to combining design, variety, and quality to inspire our customers..</p>
                    @endif


                </div>

                <div class="about-box box-right">

                    @if ($about_us)
                        <p> {{ $about_us ? $about_us->third_description : '' }} </p>
                    @else
                        <p>At Samjo Ko-Tech, we prioritize quality and customer satisfaction. Our frames are crafted
                            using premium materials and undergo rigorous quality control to ensure durability and
                            comfort.</p>
                    @endif


                    {{-- forth --}}
                    @if ($about_us)
                        <p> {{ $about_us ? $about_us->forth_description : '' }} </p>
                    @else
                        <p>We invite retailers and optical shops to explore our extensive collection of eyewear frames.
                            Join us in delivering exceptional eyewear solutions to customers worldwide.</p>
                    @endif




                </div>


            </div>
        </div>


        <div class="row">

            @if ($about_us_sections->count() > 0)
                @foreach ($about_us_sections as $section)
                    <div class="col-lg-4 col-md-6">
                        <div class="about-card">
                            <img src="{{ asset($section->image) }}" class="card-thumbnail" alt="">
                            <div class="card-inner">
                                <h3>
                                    {{ $section->title }}
                                </h3>
                                <p>
                                    {!! $section->description !!}
                                </p>
                            </div>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="row">
                    <div class="col-lg-4 col-md-6">
                        <div class="about-card">
                            <img src="assets/img/card-1.jpg" class="card-thumbnail" alt="">
                            <div class="card-inner">
                                <h3>TR90 Specialists</h3>
                                <p>Premium material 100% <br> from Switzerland</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="about-card">
                            <img src="assets/img/card-2.jpg" class="card-thumbnail" alt="">
                            <div class="card-inner">
                                <h3>100% Made in South Korea</h3>
                                <p>Design to production, <br> all local</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-md-6">
                        <div class="about-card">
                            <img src="assets/img/card-3.jpg" class="card-thumbnail" alt="">
                            <div class="card-inner">
                                <h3>15+ Years Expertise</h3>
                                <p>Malaysian market <br> since 2009</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</section>
