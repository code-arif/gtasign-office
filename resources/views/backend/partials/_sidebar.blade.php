<!--APP-SIDEBAR-->
<div class="sticky">
    <div class="app-sidebar__overlay" data-bs-toggle="sidebar"></div>
    <div class="app-sidebar" style="overflow: scroll">
        <div class="side-header">
            <a class="header-brand1" href="{{ route('admin.dashboard') }}">
                <img src="{{ asset(settings()->logo ?? 'default/logo.png') }}" id="header-brand-logo" alt="logo"
                    width="{{ settings()->logo_width ?? 200 }}" height="{{ settings()->logo_height ?? 100 }}">
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
                    <a class="side-menu__item {{ request()->routeIs('dashboard') ? 'has-link active' : '' }}"
                        href="{{ route('admin.dashboard') }}">
                        <i class="fa-solid fa-gauge-high side-menu__icon"></i>
                        <span class=" side-menu__label">Dashboard</span>
                    </a>
                </li>

                {{-- Manage Users --}}
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.setting.*') ? 'has-link active' : '' }}"
                        data-bs-toggle="slide" href="#">
                        <i class="side-menu__icon fa-solid fa-users"></i>
                        <span class="side-menu__label">Manage Users</span>
                        <i class="angle fa fa-angle-right"></i>
                    </a>

                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.categories.index') }}" class="slide-item"> Experts </a></li>
                        <li><a href="{{ route('admin.gigs.index') }}" class="slide-item">Clients</a></li>

                    </ul>
                </li>

                {{-- Manage gigs --}}
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.setting.*') ? 'has-link active' : '' }}"
                        data-bs-toggle="slide" href="#">
                        <i class="side-menu__icon fa-solid fa-gem"></i>
                        <span class="side-menu__label">Manage Gigs</span>
                        <i class="angle fa fa-angle-right"></i>
                    </a>

                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.categories.index') }}" class="slide-item"> Manage Category </a></li>
                        <li><a href="{{ route('admin.gigs.index') }}" class="slide-item">Gigs</a></li>

                    </ul>
                </li>

                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.setting.*') ? 'has-link active' : '' }}"
                        data-bs-toggle="slide" href="#">
                        <i class="side-menu__icon fa fa-file-contract"></i>
                        <span class="side-menu__label">Terms & Privacy</span>
                        <i class="angle fa fa-angle-right"></i>
                    </a>

                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.cms.privecyandterms.terms') }}" class="slide-item">Terms &
                                Condition</a></li>
                        <li><a href="{{ route('admin.cms.privecyandterms.privacy') }}" class="slide-item">Privacy
                                Policy</a></li>

                    </ul>
                </li>

                <li>
                    <h3>CMS</h3>
                </li>

                {{-- home page --}}
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.cms.home*') ? 'has-link active' : '' }}"
                        data-bs-toggle="slide" href="#">
                        <i class="side-menu__icon fa fa-home"></i>
                        <span class="side-menu__label">Home Page</span>
                        <i class="angle fa fa-angle-right"></i>
                    </a>

                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.cms.home.hero.section') }}" class="slide-item">Hero Section</a>
                        </li>
                        <li><a href="{{ route('admin.cms.home.training-camp.section') }}" class="slide-item">Training
                                Camps Section</a></li>
                        <li><a href="{{ route('admin.cms.home.slider.index') }}" class="slide-item">Partners
                                Section</a></li>
                        <li><a href="{{ route('admin.cms.home.features.index') }}" class="slide-item">Features
                                Section</a></li>
                        <li><a href="{{ route('admin.cms.home.operation.section') }}" class="slide-item">Operations
                                Section</a></li>
                        <li><a href="{{ route('admin.cms.home.testimonial.index') }}" class="slide-item">Testimonial
                                Section</a></li>
                    </ul>
                </li>

                {{-- About page --}}
                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.cms.about*') ? 'has-link active' : '' }}"
                        data-bs-toggle="slide" href="/">
                        <i class="side-menu__icon fa fa-user"></i>
                        <span class="side-menu__label">About Page</span>
                        <i class="angle fa fa-angle-right"></i>
                    </a>

                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.cms.about.index') }}" class="slide-item">About Us</a>
                        </li>
                        <li><a href="{{ route('admin.cms.about.team.index') }}" class="slide-item">Our Team</a></li>
                        <li><a href="{{ route('admin.cms.about.getting-started.index') }}" class="slide-item">Get
                                Started</a></li>
                    </ul>
                </li>
                <li>
                    <h3>Settings</h3>
                </li>

                <li class="slide">
                    <a class="side-menu__item {{ request()->routeIs('admin.setting.*') ? 'has-link active' : '' }}"
                        data-bs-toggle="slide" href="#">
                        <svg xmlns="http://www.w3.org/2000/svg" class="side-menu__icon" viewBox="0 0 512 512">
                            <path
                                d="M495.9 166.6c3.2 8.7 .5 18.4-6.4 24.6l-43.3 39.4c1.1 8.3 1.7 16.8 1.7 25.4s-.6 17.1-1.7 25.4l43.3 39.4c6.9 6.2 9.6 15.9 6.4 24.6c-4.4 11.9-9.7 23.3-15.8 34.3l-4.7 8.1c-6.6 11-14 21.4-22.1 31.2c-5.9 7.2-15.7 9.6-24.5 6.8l-55.7-17.7c-13.4 10.3-28.2 18.9-44 25.4l-12.5 57.1c-2 9.1-9 16.3-18.2 17.8c-13.8 2.3-28 3.5-42.5 3.5s-28.7-1.2-42.5-3.5c-9.2-1.5-16.2-8.7-18.2-17.8l-12.5-57.1c-15.8-6.5-30.6-15.1-44-25.4L83.1 425.9c-8.8 2.8-18.6 .3-24.5-6.8c-8.1-9.8-15.5-20.2-22.1-31.2l-4.7-8.1c-6.1-11-11.4-22.4-15.8-34.3c-3.2-8.7-.5-18.4 6.4-24.6l43.3-39.4C64.6 273.1 64 264.6 64 256s.6-17.1 1.7-25.4L22.4 191.2c-6.9-6.2-9.6-15.9-6.4-24.6c4.4-11.9 9.7-23.3 15.8-34.3l4.7-8.1c6.6-11 14-21.4 22.1-31.2c5.9-7.2 15.7-9.6 24.5-6.8l55.7 17.7c13.4-10.3 28.2-18.9 44-25.4l12.5-57.1c2-9.1 9-16.3 18.2-17.8C227.3 1.2 241.5 0 256 0s28.7 1.2 42.5 3.5c9.2 1.5 16.2 8.7 18.2 17.8l12.5 57.1c15.8 6.5 30.6 15.1 44 25.4l55.7-17.7c8.8-2.8 18.6-.3 24.5 6.8c8.1 9.8 15.5 20.2 22.1 31.2l4.7 8.1c6.1 11 11.4 22.4 15.8 34.3zM256 336a80 80 0 1 0 0-160 80 80 0 1 0 0 160z" />
                        </svg>
                        <span class="side-menu__label">Settings</span><i class="angle fa fa-angle-right"></i>
                    </a>
                    <ul class="slide-menu">
                        <li><a href="{{ route('admin.setting.general.index') }}" class="slide-item">General
                                Settings</a></li>
                        <!-- <li><a href="{{ route('admin.setting.env.index') }}" class="slide-item">Environment Settings</a></li> -->
                        <li><a href="{{ route('admin.setting.logo.index') }}" class="slide-item">Logo Settings</a>
                        </li>
                        <li><a href="{{ route('admin.setting.profile.index') }}" class="slide-item">Profile
                                Settings</a></li>
                        <li><a href="{{ route('admin.setting.mail.index') }}" class="slide-item">Mail Settings</a>
                        </li>
                        <li><a href="{{ route('admin.setting.stripe.index') }}" class="slide-item">Stripe
                                Settings</a></li>
                        {{-- <li><a href="{{ route('admin.setting.firebase.index') }}" class="slide-item">Firebase
                                Settings</a></li> --}}
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

<script>
    const sportsIcons = [
        "fa-football", "fa-basketball", "fa-baseball", "fa-volleyball", "fa-table-tennis", "fa-dumbbell",
        "fa-golf-ball-tee", "fa-hockey-puck"
    ];

    const iconElement = document.getElementById("sportsIcon");

    function changeIcon() {
        // Remove previous icon classes
        sportsIcons.forEach(icon => iconElement.classList.remove(icon));

        // Pick a random icon
        const randomIcon = sportsIcons[Math.floor(Math.random() * sportsIcons.length)];

        // Add new icon
        iconElement.classList.add(randomIcon);
    }

    // Change every 2 seconds
    setInterval(changeIcon, 2000);

    // Set one icon instantly on load
    changeIcon();
</script>

<!--/APP-SIDEBAR-->

<style>
    .side-header {
        width: 100px;
        height: 75px;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    #header-brand-logo {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
    }


</style>
