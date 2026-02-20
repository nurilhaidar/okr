<!DOCTYPE html>
<html lang="en" class="light-style layout-navbar-fixed layout-menu-fixed layout-compact" dir="ltr"
    data-theme="theme-default" data-assets-path="{{ asset('plugin/vuexy/assets') }}/"
    data-template="vertical-menu-template">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>@yield('title', 'OKR Management System')</title>

    <meta name="description" content="OKR Management System" />
    <meta name="keywords" content="okr, management, objectives, key results" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('plugin/vuexy/assets/img/favicon/favicon.ico') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&ampdisplay=swap"
        rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('plugin/vuexy/assets/vendor/fonts/fontawesome.css') }}" />
    <link rel="stylesheet" href="{{ asset('plugin/vuexy/assets/vendor/fonts/tabler-icons.css') }}" />
    <link rel="stylesheet" href="{{ asset('plugin/vuexy/assets/vendor/fonts/flag-icons.css') }}" />

    <!-- Core CSS -->
    <link rel="stylesheet" href="{{ asset('plugin/vuexy/assets/vendor/css/rtl/core.css') }}"
        class="template-customizer-core-css" />
    <link rel="stylesheet" href="{{ asset('plugin/vuexy/assets/vendor/css/rtl/theme-default.css') }}"
        class="template-customizer-theme-css" />
    <link rel="stylesheet" href="{{ asset('plugin/vuexy/assets/css/demo.css') }}" />

    <!-- Vendors CSS -->
    <link rel="stylesheet" href="{{ asset('plugin/vuexy/assets/vendor/libs/node-waves/node-waves.css') }}" />
    <link rel="stylesheet"
        href="{{ asset('plugin/vuexy/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css') }}" />
    <link rel="stylesheet" href="{{ asset('plugin/vuexy/assets/vendor/libs/typeahead-js/typeahead.css') }}" />
    <link rel="stylesheet" href="{{ asset('plugin/vuexy/assets/vendor/libs/select2/select2.css') }}" />
    <link rel="stylesheet"
        href="{{ asset('plugin/vuexy/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet"
        href="{{ asset('plugin/vuexy/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
    <link rel="stylesheet" href="{{ asset('plugin/vuexy/assets/vendor/libs/flatpickr/flatpickr.css') }}" />

    <!-- Page CSS -->

    <!-- Helpers -->
    <script src="{{ asset('plugin/vuexy/assets/vendor/js/helpers.js') }}"></script>
    <script src="{{ asset('plugin/vuexy/assets/vendor/js/template-customizer.js') }}"></script>
    <script src="{{ asset('plugin/vuexy/assets/js/config.js') }}"></script>

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="{{ asset('plugin/toastr/toastr.min.css') }}" />

    @stack('styles')

    <style>
        /* Ensure menu-sub is visible when parent has open class */
        .menu-item.open>.menu-sub {
            display: block !important;
        }

        .menu-item:not(.open)>.menu-sub {
            display: none;
        }

        .stat-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        .okr-progress {
            height: 8px;
            border-radius: 4px;
            overflow: hidden;
        }

        .okr-progress .progress-bar {
            transition: width 0.5s ease;
        }

        .text-truncate-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</head>

<body>
    <!-- Layout wrapper -->
    <div class="layout-wrapper layout-content-navbar">
        <div class="layout-container">
            {{-- @if (View::hasSection('sidebar'))
                @yield('sidebar')
            @else --}}
            <!-- Default Sidebar -->
            <aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
                <div class="app-brand demo">
                    <a href="{{ auth()->user()->role && auth()->user()->role->name === 'Admin' ? route('admin.dashboard') : route('dashboard') }}"
                        class="app-brand-link">
                        <span class="app-brand-logo demo">
                            <svg width="32" height="22" viewBox="0 0 32 22" fill="none"
                                xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M0.00172773 0V6.85398C0.00172773 6.85398 -0.133178 9.01207 1.98092 10.8388L13.6912 21.9964L19.7809 21.9181L18.8042 9.88248L16.4951 7.17289L9.23799 0H0.00172773Z"
                                    fill="#7367F0" />
                                <path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd"
                                    d="M7.69824 16.4364L12.5199 3.23696L16.5541 7.25596L7.69824 16.4364Z"
                                    fill="#161616" />
                                <path opacity="0.06" fill-rule="evenodd" clip-rule="evenodd"
                                    d="M8.07751 15.9175L13.9419 4.63989L16.5849 7.28475L8.07751 15.9175Z"
                                    fill="#161616" />
                                <path fill-rule="evenodd" clip-rule="evenodd"
                                    d="M7.77295 16.3566L23.6563 0H32V6.88383C32 6.88383 31.8262 9.17836 30.6591 10.4057L19.7824 22H13.6938L7.77295 16.3566Z"
                                    fill="#7367F0" />
                            </svg>
                        </span>
                        <span class="app-brand-text demo menu-text fw-bold">OKR System</span>
                    </a>

                    <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
                        <i class="ti menu-toggle-icon d-none d-xl-block ti-sm align-middle"></i>
                        <i class="ti ti-x d-block d-xl-none ti-sm align-middle"></i>
                    </a>
                </div>


                <ul class="menu-inner py-1">
                    <!-- Dashboard -->
                    <li class="menu-item {{ request()->routeIs('admin.dashboard', 'dashboard') ? 'active' : '' }}">
                        <a href="{{ auth()->user()->role && auth()->user()->role->name === 'Admin' ? route('admin.dashboard') : route('dashboard') }}"
                            class="menu-link">
                            <i class="menu-icon tf-icons ti ti-smart-home"></i>
                            <div data-i18n="Dashboard">Dashboard</div>
                        </a>
                    </li>

                    @if (auth()->user()->role && auth()->user()->role->name === 'Admin')
                        <!-- Employees Management -->
                        <li class="menu-header small text-uppercase">
                            <span class="menu-header-text">Employee Management</span>
                        </li>
                        <!-- Employees -->
                        <li class="menu-item {{ request()->routeIs('admin.employees*') ? 'active' : '' }}">
                            <a href="{{ route('admin.employees') }}" class="menu-link">
                                <i class="menu-icon tf-icons ti ti-users"></i>
                                <div data-i18n="Employees">Employees</div>
                            </a>
                        </li>
                        <!-- Roles -->
                        <li class="menu-item {{ request()->routeIs('admin.roles*') ? 'active' : '' }}">
                            <a href="{{ route('admin.roles') }}" class="menu-link">
                                <i class="menu-icon tf-icons ti ti-shield"></i>
                                <div data-i18n="Roles">Roles</div>
                            </a>
                        </li>

                        <!-- OrgUnit Management -->
                        <li class="menu-header small text-uppercase">
                            <span class="menu-header-text">OrgUnit Management</span>
                        </li>

                        <!-- Organization Units -->
                        <li class="menu-item {{ request()->routeIs('admin.org-units*') ? 'active open' : '' }}">
                            <a href="javascript:void(0);" class="menu-link menu-toggle">
                                <i class="menu-icon tf-icons ti ti-building"></i>
                                <div data-i18n="Organization Units">Organization Units</div>
                            </a>
                            <ul class="menu-sub">
                                <li class="menu-item {{ request()->routeIs('admin.org-units') ? 'active' : '' }}">
                                    <a href="{{ route('admin.org-units') }}" class="menu-link">
                                        <div data-i18n="Manage Units">Manage Units</div>
                                    </a>
                                </li>
                                <li
                                    class="menu-item {{ request()->routeIs('admin.org-units.structure') ? 'active' : '' }}">
                                    <a href="{{ route('admin.org-units.structure') }}" class="menu-link">
                                        <div data-i18n="Structure">Structure</div>
                                    </a>
                                </li>
                            </ul>
                        </li>

                        <!-- Org Unit Roles -->
                        <li class="menu-item {{ request()->routeIs('admin.org-unit-roles*') ? 'active' : '' }}">
                            <a href="{{ route('admin.org-unit-roles') }}" class="menu-link">
                                <i class="menu-icon tf-icons ti ti-users-group"></i>
                                <div data-i18n="Unit Roles">Org Unit Roles</div>
                            </a>
                        </li>

                        <!-- OKR Management -->
                        <li class="menu-header small text-uppercase">
                            <span class="menu-header-text">OKR Management</span>
                        </li>
                        <!-- OKR Types -->
                        <li class="menu-item {{ request()->routeIs('admin.okr-types*') ? 'active' : '' }}">
                            <a href="{{ route('admin.okr-types') }}" class="menu-link">
                                <i class="menu-icon tf-icons ti ti-target"></i>
                                <div data-i18n="OKR Types">OKR Types</div>
                            </a>
                        </li>
                        <!-- Admin OKRs -->
                        <li class="menu-item {{ request()->routeIs('admin.okrs*') ? 'active' : '' }}">
                            <a href="{{ route('admin.okrs') }}" class="menu-link">
                                <i class="menu-icon tf-icons ti ti-target-arrow"></i>
                                <div data-i18n="OKRs">OKRs</div>
                            </a>
                        </li>
                    @endif

                    @if (!auth()->user()->role || auth()->user()->role->name !== 'Admin')
                        <!-- OKR Management header for employees -->
                        <li class="menu-header small text-uppercase">
                            <span class="menu-header-text">OKR Management</span>
                        </li>
                    @endif

                    <!-- Employee OKRs -->
                    @if (!auth()->user()->role || auth()->user()->role->name !== 'Admin')
                        <li class="menu-item {{ request()->routeIs('okrs*') ? 'active' : '' }}">
                            <a href="{{ route('okrs.index') }}" class="menu-link">
                                <i class="menu-icon tf-icons ti ti-target-arrow"></i>
                                <div data-i18n="OKRs">My OKRs</div>
                            </a>
                        </li>
                    @endif
                    <!-- Check-ins -->
                    <li class="menu-item {{ request()->routeIs('admin.check-ins*') ? 'active' : '' }}">
                        <a href="{{ route('admin.check-ins.index') }}" class="menu-link">
                            <i class="menu-icon tf-icons ti ti-checkbox"></i>
                            <div data-i18n="Check-ins">Check-ins</div>
                        </a>
                    </li>
                </ul>
            </aside>
            <!-- / Menu -->
            {{-- @endif --}}

            <!-- Layout container -->
            <div class="layout-page">
                @if (View::hasSection('navbar'))
                    @yield('navbar')
                @else
                    <!-- Default Navbar -->
                    <nav
                        class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme">
                        <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
                            <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0);">
                                <i class="ti ti-menu-2 ti-md"></i>
                            </a>
                        </div>

                        <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
                            <ul class="navbar-nav flex-row align-items-center ms-auto">
                                <li class="nav-item navbar-dropdown dropdown-user">
                                    <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);"
                                        data-bs-toggle="dropdown">
                                        <div class="avatar avatar-online">
                                            <img src="/plugin/vuexy/img/avatars/5.png" alt
                                                class="w-px-40 h-auto rounded-circle" />
                                        </div>
                                    </a>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li>
                                            <a class="dropdown-item" href="javascript:void(0);">
                                                <div class="d-flex">
                                                    <div class="shrink-0 me-3">
                                                        <div class="avatar avatar-online">
                                                            <img src="/plugin/vuexy/img/avatars/5.png" alt
                                                                class="w-px-40 h-auto rounded-circle" />
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ auth()->user()->name }}</h6>
                                                        <small
                                                            class="text-muted">{{ auth()->user()->role->name ?? 'No Role' }}</small>
                                                    </div>
                                                </div>
                                            </a>
                                        </li>
                                        <li>
                                            <div class="dropdown-divider"></div>
                                        </li>
                                        <li>
                                            <form method="POST" action="{{ route('logout') }}">
                                                @csrf
                                                <button type="submit" class="dropdown-item">
                                                    <i class="ti ti-logout me-2"></i>
                                                    <span class="align-middle">Log Out</span>
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </div>
                    </nav>
                    <!-- / Navbar -->
                @endif

                <!-- Content wrapper -->
                <div class="content-wrapper">
                    <!-- Content -->
                    <div class="container-xxl flex-grow-1 container-p-y">
                        @yield('content')
                    </div>
                    <!-- / Content -->

                    <!-- Footer -->
                    <footer class="content-footer footer bg-footer-theme">
                        <div
                            class="container-xxl d-flex flex-wrap justify-content-between py-2 flex-md-row flex-column">
                            <div class="mb-2 mb-md-0">
                                &copy; {{ date('Y') }} OKR Management System. All rights reserved.
                            </div>
                        </div>
                    </footer>
                    <!-- / Footer -->
                </div>
                <!-- Content wrapper -->
            </div>
            <!-- / Layout page -->
        </div>
    </div>

    <!-- Core JS -->
    <script src="{{ asset('plugin/vuexy/assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('plugin/vuexy/assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('plugin/vuexy/assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('plugin/vuexy/assets/vendor/libs/node-waves/node-waves.js') }}"></script>
    <script src="{{ asset('plugin/vuexy/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('plugin/vuexy/assets/vendor/libs/hammer/hammer.js') }}"></script>
    <script src="{{ asset('plugin/vuexy/assets/vendor/js/menu.js') }}"></script>

    <!-- Main JS -->
    <script src="{{ asset('plugin/vuexy/assets/js/main.js') }}"></script>

    <!-- Select2 JS -->
    <script src="{{ asset('plugin/vuexy/assets/vendor/libs/select2/select2.js') }}"></script>

    <!-- Toastr JS -->
    <script src="{{ asset('plugin/toastr/toastr.min.js') }}"></script>


    <script>
        function showToast(title, message, type = 'info') {
            const toastrType = type === 'error' ? 'error' : type;
            toastr.options = {
                closeButton: true,
                progressBar: true,
                positionClass: 'toast-top-right',
                timeOut: '5000',
                extendedTimeOut: '1000',
            };
            toastr[toastrType](message, title);
        }
    </script>

    @stack('scripts')

    @yield('page_scripts')
</body>

</html>
