<!----- Responsive Menu Start ----->
<div class="popup-menu">
    <div class="menu-inner">
        <div class="menu-top">
            <a href="{{ route('home') }}"><img src="{{ asset('website/img/logo.png') }}" alt=""></a>
            <button class="menu-x"><img src="{{ asset('website/img/times.svg') }} " alt=""></button>
        </div>

        @php
            $categories = App\Models\Category::all();
        @endphp


        <div class="mobile-nav">
            <ul>
                @foreach ($categories as $category)
                    <li><a
                            href="{{ route('website.product.index', $category->slug) }}">{{ strtoupper($category->title) }}</a>
                    </li>
                @endforeach



                <li><a href="{{ route('website.product.clearance') }}" class="stock-info">STOCK CLEARANCE</a></li>

            </ul>
        </div>

        {{-- <div class="user__information">
            <a href="login.html">Log In</a>
            <a href="register.html">Sign Up</a>
            <a href="shopping-cart-list.html">Cart</a>
        </div> --}}

        @auth
            <div class="user__information">
                <a href="{{ route('website.profile.index') }}" class="mobile-profile">
                    <img src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim(Auth::user()->email))) . '?s=40&d=identicon' }}"
                        + alt="{{ Auth::user()->name }}" style="width:30px;height:30px;border-radius:50%;object-fit:cover;">
                    {{-- {{ Auth::user()->name }} --}}
                </a>
                <a href="{{ route('website.cart.index') }}">
                    Cart ({{ App\Models\Cart::where('user_id', Auth::id())->count() }})
                </a>
                <a href="#"
                    onclick="event.preventDefault(); document.getElementById('mobile-logout-form').submit();">Log Out</a>
                <form id="mobile-logout-form" action="{{ route('logout') }}" method="POST" style="display:none;">
                    @csrf
                </form>
            </div>
        @else
            <div class="user__information">
                <a href="{{ route('login') }}">Log In</a>
                <a href="{{ route('register') }}">Sign Up</a>
                <a href="{{ route('website.cart.index') }}">Cart (0)</a>
            </div>
        @endauth

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
            <form action="{{ route('website.search') }}" method="GET">
                <label for="">Search</label>
                <div class="search-field">
                    <input type="text" name="search" placeholder="Search products..." required>
                    <button type="submit"><img src="{{ asset('website/img/search.svg') }}" alt=""></button>
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
                    @php
                        $categories = App\Models\Category::all();
                    @endphp
                    <nav>
                        <ul id="navigation">

                            @foreach ($categories as $category)
                                <li>
                                    <a
                                        href="{{ route('website.product.index', $category->slug) }}">{{ strtoupper($category->title) }}</a>
                                </li>
                            @endforeach


                            <li><a href="{{ route('website.product.clearance') }}" class="stock-info">STOCK
                                    CLEARANCE</a></li>
                        </ul>
                    </nav>
                </div>
            </div>
            <div class="col-xl-2 col-4">
                <div class="logo">
                    <a href="{{ route('home') }}"><img src="{{ asset('website/img/logo.png') }}" alt=""></a>
                </div>
            </div>
            <div class="col-xl-5 col-8">
                <div class="header-right">
                    {{-- <div class="user-option">
                        <a href="{{ route('login') }}">Log In</a>
                        <a href="{{ route('register') }}">Sign Up</a>
                    </div> --}}

                    <div class="search-btn">

                        <button><img src="{{ asset('website/img/search.svg') }}" alt=""></button>
                    </div>
                    <div class="cart-btn">
                        <a href="{{ route('website.cart.index') }}"><img src="{{ asset('website/img/bag.svg') }}"
                                alt="">
                            <span>
                                @auth
                                    {{ App\Models\Cart::where('user_id', Auth::id())->count() }}
                                @else
                                    0
                                @endauth
                            </span>
                        </a>
                    </div>
                    <div class="menu_trigger">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                    @auth
                        <div class="user-profile dropdown">
                            <div class="user-option">
                                <a href="{{ route('website.profile.index') }}" class="profile-toggle" id="profileDropdown"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <img src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim(Auth::user()->email))) . '?s=40&d=identicon' }}"
                                        alt="{{ Auth::user()->name }}"
                                        style="width:25px;height:25px;border-radius:60%;object-fit:cover;">
                                    {{-- <span class="profile-name">{{ Auth::user()->name }}</span> --}}
                                </a>
                                <a href="#"
                                    onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Log
                                    Out</a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                    style="display: none;">
                                    @csrf
                                </form>
                            </div>
                        </div>
                    @else
                        <div class="user-option">
                            <a href="{{ route('login') }}">Log In</a>
                            <a href="{{ route('register') }}">Sign Up</a>
                        </div>

                    @endauth
                </div>
            </div>
        </div>
    </div>
</header>
<!--------- Header Area End ------>
