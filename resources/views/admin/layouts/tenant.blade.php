<!doctype html>
<html lang="en" data-layout="vertical" data-topbar="light" data-sidebar="dark" data-sidebar-size="lg">

<head>
    <meta charset="utf-8" />
    <title>Admin Dashboard | Multi-Tenant System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ url('assets/backend/images/favicon.ico') }}">

    <!-- CSS Links (Adjust paths based on your public folder) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- Layout config Js -->
    <script src="{{ url('assets/backend/js/layout.js') }}"></script>

    <!-- Custom Theme CSS (Ensure these paths match your assets) -->
    <link href="{{ url('assets/backend/css/app.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ url('assets/backend/css/custom.min.css') }}" rel="stylesheet" type="text/css" />

    @livewireStyles
</head>

<body>

    <!-- Begin page -->
    <div id="layout-wrapper">

        <!-- Header -->
        @include('layouts.partials.header')

        <!-- ========== App Menu (Sidebar) ========== -->
        <div class="app-menu navbar-menu">
            <!-- LOGO -->
            <div class="navbar-brand-box">
                <a href="{{ route('dashboard') }}" class="logo logo-dark">
                    <span class="logo-sm"><img src="{{ url('assets/backend/images/logo-sm.png') }}" height="22"></span>
                    <span class="logo-lg"><img src="{{ url('assets/backend/images/logo-dark.png') }}" height="25"></span>
                </a>
                <button type="button" class="btn btn-sm p-0 fs-20 header-item float-end btn-vertical-sm-hover" id="vertical-hover">
                    <i class="ri-record-circle-line"></i>
                </button>
            </div>

            <div id="scrollbar">
                <div class="container-fluid">
                    <div id="two-column-menu"></div>
                    <ul class="navbar-nav" id="navbar-nav">
                        <li class="menu-title"><span>Menu</span></li>

                        @if(session()->has('active_tenant_id'))
                        <li class="nav-item">
                            <a class="nav-link menu-link" href="{{ route('dashboard') }}">
                                <i class="bi bi-speedometer2"></i> <span>Dashboard</span>
                            </a>
                        </li>
                        <!-- Add other menu items here (Products, Orders, etc.) -->
                        @else
                        <li class="nav-item">
                            <a class="nav-link menu-link active" href="#!">
                                <i class="bi bi-shield-lock"></i> <span>Setup Required</span>
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
        <!-- Left Sidebar End -->

        <!-- Vertical Overlay-->
        <div class="vertical-overlay"></div>

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">

            <div class="page-content">
                <div class="container-fluid">

                    <!-- Flash Messages -->
                    @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif

                    <!-- Main Content Slot -->
                    {{ $slot }}

                </div>
            </div>

            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-sm-6">
                            <script>
                                document.write(new Date().getFullYear())
                            </script> © Your Company.
                        </div>
                        <div class="col-sm-6">
                            <div class="text-sm-end d-none d-sm-block">
                                Multi-tenant Admin Panel
                            </div>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <!-- END layout-wrapper -->

    <!-- JAVASCRIPT -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Theme Scripts -->
    <script src="{{ url('assets/backend/js/app.js') }}"></script>

    @livewireScripts
    @stack('scripts')
</body>

</html>