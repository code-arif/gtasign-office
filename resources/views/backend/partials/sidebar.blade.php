<!--APP-SIDEBAR-->
<div class="sticky">
    <div class="app-sidebar__overlay" data-bs-toggle="sidebar"></div>
    <div class="app-sidebar" style="overflow: scroll">
        <div class="side-header">
            <a class="header-brand1" href="{{ route('home') }}">
                <img src="{{ asset($settings->logo ?? 'default/logo.png') }}" class="header-brand-img desktop-logo"
                    alt="logo">
                <img src="{{ asset($settings->logo ?? 'default/logo.png') }}" class="header-brand-img toggle-logo"
                    alt="logo">
                <img src="{{ asset($settings->logo ?? 'default/logo.png') }}" class="header-brand-img light-logo"
                    alt="logo">
                <img src="{{ asset($settings->logo ?? 'default/logo.png') }}" class="header-brand-img light-logo1"
                    alt="logo">
            </a>
        </div>
        <div class="main-sidemenu">
            <div class="slide-left disabled" id="slide-left"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191"
                    width="24" height="24" viewBox="0 0 24 24">
                    <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z" />
                </svg>
            </div>
            <ul class="side-menu mt-2">
                <li>
                    <h3>Menu</h3>
                </li>
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('dashboard') ? 'has-link' : '' }}"
                        href="{{ route('dashboard') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon"
                            enable-background="new 0 0 24 24" viewBox="0 0 24 24">
                            <path
                                d="M19.9794922,7.9521484l-6-5.2666016c-1.1339111-0.9902344-2.8250732-0.9902344-3.9589844,0l-6,5.2666016C3.3717041,8.5219116,2.9998169,9.3435669,3,10.2069702V19c0.0018311,1.6561279,1.3438721,2.9981689,3,3h2.5h7c0.0001831,0,0.0003662,0,0.0006104,0H18c1.6561279-0.0018311,2.9981689-1.3438721,3-3v-8.7930298C21.0001831,9.3435669,20.6282959,8.5219116,19.9794922,7.9521484z M15,21H9v-6c0.0014038-1.1040039,0.8959961-1.9985962,2-2h2c1.1040039,0.0014038,1.9985962,0.8959961,2,2V21z M20,19c-0.0014038,1.1040039-0.8959961,1.9985962-2,2h-2v-6c-0.0018311-1.6561279-1.3438721-2.9981689-3-3h-2c-1.6561279,0.0018311-2.9981689,1.3438721-3,3v6H6c-1.1040039-0.0014038-1.9985962-0.8959961-2-2v-8.7930298C3.9997559,9.6313477,4.2478027,9.0836182,4.6806641,8.7041016l6-5.2666016C11.0455933,3.1174927,11.5146484,2.9414673,12,2.9423828c0.4853516-0.0009155,0.9544067,0.1751099,1.3193359,0.4951172l6,5.2665405C19.7521973,9.0835571,20.0002441,9.6313477,20,10.2069702V19z" />
                        </svg>
                        <span class="side-menu__label">Dashboard</span>
                    </a>
                </li>




                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('category') ? 'has-link' : '' }}"
                        href="{{ route('admin.category.index') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24"
                            fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M20.59 13.41l-7.59 7.59c-.36.36-.86.59-1.41.59s-1.05-.23-1.41-.59l-7.59-7.59c-.36-.36-.59-.86-.59-1.41s.23-1.05.59-1.41l7.59-7.59c.36-.36.86-.59 1.41-.59s1.05.23 1.41.59l7.59 7.59c.36.36.59.86.59 1.41s-.23 1.05-.59 1.41zM12 4.41L4.41 12 12 19.59 19.59 12 12 4.41z" />
                            <circle cx="12" cy="12" r="2" />
                        </svg>
                        <span class="side-menu__label">Category</span>
                    </a>
                </li>

                 <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('book') ? 'has-link' : '' }}"
                        href="{{ route('admin.book.index') }}">

                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M18 2H6a2 2 0 0 0-2 2v16a1 1 0 0 0 1.447.894L12 18l6.553 2.894A1 1 0 0 0 20 20V4a2 2 0 0 0-2-2zM6 4h12v13.382l-5.553-2.353a1 1 0 0 0-.894 0L6 17.382V4z" />
                        </svg>

                        <span class="side-menu__label">Books</span>
                    </a>
                </li>

                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('model') ? 'has-link' : '' }}"
                        href="{{ route('admin.model.index') }}">

                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24"
                            fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M21 8.5v7.75c0 .6-.33 1.15-.86 1.42l-7 3.75a1.5 1.5 0 0 1-1.28 0l-7-3.75A1.5 1.5 0 0 1 3 16.25V8.5A1.5 1.5 0 0 1 4.28 7.3l7-3.75a1.5 1.5 0 0 1 1.44 0l7 3.75A1.5 1.5 0 0 1 21 8.5zM12 4.2L5 7.6v6.9l7 3.75 7-3.75V7.6L12 4.2zM8.5 11.5a3.5 3.5 0 1 0 7 0 3.5 3.5 0 0 0-7 0z" />
                        </svg>

                        <span class="side-menu__label">Product Model</span>
                    </a>
                </li>

                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('order') ? 'has-link' : '' }}"
                        href="{{ route('admin.order.index') }}">

                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24"
                            fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M21 8.5v7.75c0 .6-.33 1.15-.86 1.42l-7 3.75a1.5 1.5 0 0 1-1.28 0l-7-3.75A1.5 1.5 0 0 1 3 16.25V8.5A1.5 1.5 0 0 1 4.28 7.3l7-3.75a1.5 1.5 0 0 1 1.44 0l7 3.75A1.5 1.5 0 0 1 21 8.5zM12 4.2L5 7.6v6.9l7 3.75 7-3.75V7.6L12 4.2zM8.5 11.5a3.5 3.5 0 1 0 7 0 3.5 3.5 0 0 0-7 0z" />
                        </svg>

                        <span class="side-menu__label">Order</span>
                    </a>
                </li>

                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            class="side-menu__icon" viewBox="0 0 16 16">
                            <path
                                d="M7.5 5.5a.5.5 0 0 0-1 0v.634l-.549-.317a.5.5 0 1 0-.5.866L6 7l-.549.317a.5.5 0 1 0 .5.866l.549-.317V8.5a.5.5 0 1 0 1 0v-.634l.549.317a.5.5 0 1 0 .5-.866L8 7l.549-.317a.5.5 0 1 0-.5-.866l-.549.317zm-2 4.5a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1z" />
                            <path
                                d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z" />
                        </svg>
                        <span class="side-menu__label">CMS(Frontend)</span><i class="angle fa fa-angle-right"></i>
                    </a>



                    <ul class="slide-menu">


                        <li><a href="{{ route('admin.cms.home.banner.index') }}" class="slide-item">
                                Banner (Section)
                            </a>
                        </li>

                        <li><a href="{{ route('admin.cms.home.about_us.index') }}" class="slide-item">
                                About us (Section)
                            </a>
                        </li>

                        <li><a href="{{ route('admin.cms.home.orders.index') }}" class="slide-item">
                                Order summary (Section)
                            </a>
                        </li>





                    </ul>








                </li>


                {{-- <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('product') ? 'has-link' : '' }}"
                        href="{{ route('admin.product.index') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" width="24" height="24"
                            fill="currentColor" viewBox="0 0 24 24">
                            <path
                                d="M21 8.5v7.75c0 .6-.33 1.15-.86 1.42l-7 3.75a1.5 1.5 0 0 1-1.28 0l-7-3.75A1.5 1.5 0 0 1 3 16.25V8.5A1.5 1.5 0 0 1 4.28 7.3l7-3.75a1.5 1.5 0 0 1 1.44 0l7 3.75A1.5 1.5 0 0 1 21 8.5zM12 4.2 5 7.6v6.9l7 3.75 7-3.75V7.6L12 4.2zM8.5 11.5a3.5 3.5 0 1 0 7 0 3.5 3.5 0 0 0-7 0z" />
                        </svg>

                        <span class="side-menu__label">Product Items </span>
                    </a>
                </li> --}}







                <h3>User and Chat Manage</h3>

                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('user') ? 'has-link' : '' }}"
                        href="{{ route('admin.user.index') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            class="side-menu__icon" viewBox="0 0 24 24">
                            <path
                                d="M12 2a5 5 0 1 1-5 5 5 5 0 0 1 5-5zm0 14c-4.4 0-8 2.2-8 5a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1c0-2.8-3.6-5-8-5z" />
                        </svg>
                        <span class="side-menu__label">User List</span>
                    </a>
                </li>

                {{-- <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.chat.*') ? 'active' : '' }}"
                        href="{{ route('admin.chat.index') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            class="side-menu__icon" viewBox="0 0 24 24">
                            <path
                                d="M12 2a5 5 0 1 1-5 5 5 5 0 0 1 5-5zm0 14c-4.4 0-8 2.2-8 5a1 1 0 0 0 1 1h14a1 1 0 0 0 1-1c0-2.8-3.6-5-8-5z" />
                        </svg>
                        <span class="side-menu__label">Chat System</span>
                    </a>
                </li> --}}



                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('dynamic_page') ? 'has-link' : '' }}"
                        href="{{ route('admin.dynamic_page.index') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            class="side-menu__icon" viewBox="0 0 16 16">
                            <path
                                d="M8 1a7 7 0 1 0 7 7A7 7 0 0 0 8 1zm0 1.5a5.5 5.5 0 1 1-5.5 5.5A5.507 5.507 0 0 1 8 2.5zm-.25 4.75a.75.75 0 1 1 1.5 0v1a.75.75 0 0 1-1.5 0v-1zm.25 4.25a.75.75 0 1 1 0-1.5h.002a.75.75 0 1 1 0 1.5H8z" />
                        </svg>
                        <span class="side-menu__label">Dynamic Page</span>
                    </a>
                </li>






                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('faq') ? 'has-link' : '' }}"
                        href="{{ route('admin.faq.index') }}">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                            class="side-menu__icon" viewBox="0 0 16 16">
                            <path
                                d="M8 1a7 7 0 1 0 7 7A7 7 0 0 0 8 1zm0 1.5a5.5 5.5 0 1 1-5.5 5.5A5.507 5.507 0 0 1 8 2.5zm-.25 4.75a.75.75 0 1 1 1.5 0v1a.75.75 0 0 1-1.5 0v-1zm.25 4.25a.75.75 0 1 1 0-1.5h.002a.75.75 0 1 1 0 1.5H8z" />
                        </svg>
                        <span class="side-menu__label">FAQ</span>
                    </a>
                </li>



                <li class="slide">
                    <a class="side-menu__item" data-bs-toggle="slide" href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 512 512">
                            <path
                                d="M495.9 166.6c3.2 8.7 .5 18.4-6.4 24.6l-43.3 39.4c1.1 8.3 1.7 16.8 1.7 25.4s-.6 17.1-1.7 25.4l43.3 39.4c6.9 6.2 9.6 15.9 6.4 24.6c-4.4 11.9-9.7 23.3-15.8 34.3l-4.7 8.1c-6.6 11-14 21.4-22.1 31.2c-5.9 7.2-15.7 9.6-24.5 6.8l-55.7-17.7c-13.4 10.3-28.2 18.9-44 25.4l-12.5 57.1c-2 9.1-9 16.3-18.2 17.8c-13.8 2.3-28 3.5-42.5 3.5s-28.7-1.2-42.5-3.5c-9.2-1.5-16.2-8.7-18.2-17.8l-12.5-57.1c-15.8-6.5-30.6-15.1-44-25.4L83.1 425.9c-8.8 2.8-18.6 .3-24.5-6.8c-8.1-9.8-15.5-20.2-22.1-31.2l-4.7-8.1c-6.1-11-11.4-22.4-15.8-34.3c-3.2-8.7-.5-18.4 6.4-24.6l43.3-39.4C64.6 273.1 64 264.6 64 256s.6-17.1 1.7-25.4L22.4 191.2c-6.9-6.2-9.6-15.9-6.4-24.6c4.4-11.9 9.7-23.3 15.8-34.3l4.7-8.1c6.6-11 14-21.4 22.1-31.2c5.9-7.2 15.7-9.6 24.5-6.8l55.7 17.7c13.4-10.3 28.2-18.9 44-25.4l12.5-57.1c2-9.1 9-16.3 18.2-17.8C227.3 1.2 241.5 0 256 0s28.7 1.2 42.5 3.5c9.2 1.5 16.2 8.7 18.2 17.8l12.5 57.1c15.8 6.5 30.6 15.1 44 25.4l55.7-17.7c8.8-2.8 18.6-.3 24.5 6.8c8.1 9.8 15.5 20.2 22.1 31.2l4.7 8.1c6.1 11 11.4 22.4 15.8 34.3zM256 336a80 80 0 1 0 0-160 80 80 0 1 0 0 160z" />
                        </svg>
                        <span class="side-menu__label">Settings</span><i class="angle fa fa-angle-right"></i>
                    </a>

                    <ul class="slide-menu">
                        <li><a href="{{ route('setting.general.index') }}" class="slide-item">General Settings</a>
                        </li>
                        <li><a href="{{ route('setting.profile.index') }}" class="slide-item">Profile Settings</a>
                        </li>

                        <li><a href="{{ route('setting.mail.index') }}" class="slide-item">Mail Settings</a></li>
                        <li><a href="{{ route('setting.stripe.index') }}" class="slide-item">Stripe Settings</a></li>
                        <li><a href="{{ route('admin.testimonial.index') }}" class="slide-item">Testimonials</a></li>

                        <li><a href="{{ route('admin.social_media.index') }}" class="slide-item">Social Media</a>
                        </li>
                        <li><a href="{{ route('setting.social.index') }}" class="slide-item">Google Settings</a></li>
                    </ul>
                </li>
            </ul>
            <div class="slide-right" id="slide-right"><svg xmlns="http://www.w3.org/2000/svg" fill="#7b8191"
                    width="24" height="24" viewBox="0 0 24 24">
                    <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z" />
                </svg>
            </div>
        </div>
    </div>
</div>
<!--/APP-SIDEBAR-->
