<div class="app-menu navbar-menu">
    <!-- LOGO -->
    <div class="navbar-brand-box">
        <a href="{{ route('admin.dashboard') }}" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ url('assets/images/logo-sm.png') }}" alt="" height="30">
            </span>
            <span class="logo-lg">
                <img src="{{ isset($settings['dark_logo']) ? asset('storage/' . $settings['dark_logo']) : url('assets/images/logo-dark.png') }}" alt="" height="30">
            </span>
        </a>
        <a href="{{ route('admin.dashboard') }}" class="logo logo-light">
            <span class="logo-sm">
                <img src="{{ url('assets/images/logo-sm.png') }}" alt="" height="30">
            </span>
            <span class="logo-lg">
                <img src="{{ isset($settings['white_logo']) ? asset('storage/' . $settings['white_logo']) : url('assets/images/logo-light.png') }}" alt="" height="30">
            </span>
        </a>
        <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
            <i class="ri-record-circle-line"></i>
        </button>
    </div>

    <div id="scrollbar">
        <div class="container-fluid">
            <div id="two-column-menu"></div>
            <ul class="navbar-nav" id="navbar-nav">

                <li class="menu-title"><span data-key="t-menu">Main Menu</span></li>

                <!-- 1. Single Link Example (Dashboard) -->
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link menu-link {{ Route::is('admin.dashboard') ? 'active' : '' }}" wire:navigate>
                        <i class="bi bi-speedometer2"></i> <span data-key="t-dashboard">Dashboard</span>
                    </a>
                </li>

                <!-- 2. Multiple Link Example (Dropdown) -->
                <li class="nav-item">
                    <a class="nav-link menu-link" href="#sidebarCatalog" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="sidebarCatalog">
                        <i class="bi bi-grid"></i> <span data-key="t-catalog">Catalog</span>
                    </a>
                    <div class="collapse menu-dropdown " id="sidebarCatalog">
                        <ul class="nav nav-sm flex-column">
                            <li class="nav-item">
                                <a href="#" class="nav-link" wire:navigate>
                                    Products
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#" class="nav-link" wire:navigate>
                                    Categories
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

            </ul>
        </div>
    </div>
    <div class="sidebar-background"></div>
</div>