<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <!-- Dark Logo-->
        <a href="{{ route('dashboard') }}" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ asset('assets/images/logo-sm.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ asset('assets/images/logo.png') }}" alt="" height="17">
            </span>
        </a>
        <!-- Light Logo-->
        <a href="{{ route('dashboard') }}" class="logo logo-light">
            <span class="logo-sm">
                <img src="{{ asset('assets/images/logo-sm.png') }}" alt="" height="22">
            </span>
            <span class="logo-lg">
                <img src="{{ asset('assets/images/logo-white.png') }}" alt="" height="17" style="width: 200px;height: 50px;">
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>
    
    <div class="dropdown sidebar-user m-1 rounded">
        <button type="button" class="btn material-shadow-none" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="d-flex align-items-center gap-2">
                <img class="rounded header-profile-user" src="{{ auth()->user()->avatar ?? asset('assets/images/users/avatar-1.jpg') }}" alt="Header Avatar">
                <span class="text-start">
                    <span class="d-block fw-medium sidebar-user-name-text">{{ auth()->user()->name ?? 'Anna Adame' }}</span>
                    <span class="d-block fs-14 sidebar-user-name-sub-text"><i class="ri ri-circle-fill fs-10 text-success align-baseline"></i> <span class="align-middle">Online</span></span>
                </span>
            </span>
        </button>
        <div class="dropdown-menu dropdown-menu-end">
            <h6 class="dropdown-header">Welcome {{ auth()->user()->name ?? 'Anna' }}!</h6>
            <a class="dropdown-item" href="{{ route('profile') }}"><i class="mdi mdi-account-circle text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Profile</span></a>
            <a class="dropdown-item" href="{{ route('messages') }}"><i class="mdi mdi-message-text-outline text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Messages</span></a>
            <a class="dropdown-item" href="{{ route('tasks') }}"><i class="mdi mdi-calendar-check-outline text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Taskboard</span></a>
            <a class="dropdown-item" href="{{ route('faqs') }}"><i class="mdi mdi-lifebuoy text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Help</span></a>
            <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="{{ route('profile') }}"><i class="mdi mdi-wallet text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Balance : <b>${{ number_format(auth()->user()->balance ?? 5971.67, 2) }}</b></span></a>
            <a class="dropdown-item" href="{{ route('settings') }}"><span class="badge bg-success-subtle text-success mt-1 float-end">New</span><i class="mdi mdi-cog-outline text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Settings</span></a>
            <a class="dropdown-item" href="{{ route('lock-screen') }}"><i class="mdi mdi-lock text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Lock screen</span></a>
            <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form-sidebar').submit();"><i class="mdi mdi-logout text-muted fs-16 align-middle me-1"></i> <span class="align-middle">Logout</span></a>
            <form id="logout-form-sidebar" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
        </div>
    </div>
    
    <div id="scrollbar">
        <div class="container-fluid">
            <div id="two-column-menu"></div>
            <ul class="navbar-nav" id="navbar-nav">
                <li class="menu-title"><span data-key="t-menu">Menu</span></li>
                
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}" role="button">
                        <i class="ri-dashboard-2-line"></i> <span data-key="t-dashboards">Dashboards</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('vouchers.*') ? 'active' : '' }}" href="#sidebarVouchers" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('vouchers.*') ? 'true' : 'false' }}" aria-controls="sidebarVouchers">
                        <i class="ri-coupon-line"></i> <span data-key="t-maps">Vouchers</span>
                    </a>
                    <div class="collapse menu-dropdown {{ request()->routeIs('vouchers.*') ? 'show' : '' }}" id="sidebarVouchers">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('vouchers.create') }}" class="nav-link {{ request()->routeIs('vouchers.create') ? 'active' : '' }}" data-key="t-google">
                                    New Voucher
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('vouchers.index') }}" class="nav-link {{ request()->routeIs('vouchers.index') ? 'active' : '' }}" data-key="t-vector">
                                    List Redeemed Vouchers
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('vouchers.import.upload-bulk-vouchers') }}" class="nav-link {{ request()->routeIs('vouchers.import.upload-bulk-vouchers') ? 'active' : '' }}" data-key="t-vector">
                                    Bulk Upload Vouchers
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('merchants.*') ? 'active' : '' }}" href="#sidebarMerchants" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('merchants.*') ? 'true' : 'false' }}" aria-controls="sidebarMerchants">
                        <i class="ri-store-line"></i> <span data-key="t-maps">Merchants</span>
                    </a>
                    <div class="collapse menu-dropdown {{ request()->routeIs('merchants.*') ? 'show' : '' }}" id="sidebarMerchants">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('merchants.create') }}" class="nav-link {{ request()->routeIs('merchants.create') ? 'active' : '' }}" data-key="t-google">
                                    New Merchant
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('merchants.index') }}" class="nav-link {{ request()->routeIs('merchants.index') ? 'active' : '' }}" data-key="t-vector">
                                    List Merchants
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
                
                <li class="nav-item">
                    <a class="nav-link menu-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="#sidebarUsers" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('users.*') ? 'true' : 'false' }}" aria-controls="sidebarUsers">
                        <i class="ri-team-line"></i> <span data-key="t-maps">Users</span>
                    </a>
                    <div class="collapse menu-dropdown {{ request()->routeIs('users.*') ? 'show' : '' }}" id="sidebarUsers">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="{{ route('users.create') }}" class="nav-link {{ request()->routeIs('users.create') ? 'active' : '' }}" data-key="t-google">
                                    New User
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('users.index') }}" class="nav-link {{ request()->routeIs('users.index') ? 'active' : '' }}" data-key="t-vector">
                                    List Users
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
        </div>
        <!-- Sidebar -->
    </div>
    <div class="sidebar-background"></div>
</div>