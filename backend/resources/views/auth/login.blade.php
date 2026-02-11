<!DOCTYPE html>
<html lang="en" class="light-style layout-wide customizer-hide" dir="ltr"
    data-assets-path="{{ asset('plugin/vuexy/assets') }}/" data-theme="theme-default">

<head>
    <meta charset="utf-8" />
    <meta name="viewport"
        content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

    <title>Login - OKR Management System</title>

    <meta name="description" content="OKR Management System Login" />

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('plugin/vuexy/assets/img/favicon/favicon.ico') }}" />

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Public+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;1,300;1,400;1,500;1,600;1,700&display=swap"
        rel="stylesheet" />

    <!-- Icons -->
    <link rel="stylesheet" href="{{ asset('plugin/vuexy/assets/vendor/fonts/fontawesome.css') }}" />
    <link rel="stylesheet" href="{{ asset('plugin/vuexy/assets/vendor/fonts/tabler-icons.css') }}" />

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
    <link rel="stylesheet"
        href="{{ asset('plugin/vuexy/assets/vendor/libs/@form-validation/umd/styles/index.min.css') }}" />

    <!-- Toastr CSS -->
    <link rel="stylesheet" href="{{ asset('plugin/toastr/toastr.min.css') }}" />

    <!-- Page CSS -->
    <link rel="stylesheet" href="{{ asset('plugin/vuexy/assets/vendor/css/pages/page-auth.css') }}" />

    <style>
        .btn-loading {
            position: relative;
            pointer-events: none;
            opacity: 0.7;
        }

        .btn-loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 16px;
            height: 16px;
            margin: -8px 0 0 -8px;
            border: 2px solid #fff;
            border-top-color: transparent;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <!-- Content -->
    <div class="container-xxl">
        <div class="authentication-wrapper authentication-basic container-p-y">
            <div class="authentication-inner py-4">
                <!-- Login Card -->
                <div class="card">
                    <div class="card-body">
                        <!-- Logo -->
                        <div class="app-brand justify-content-center mb-4 mt-2">
                            <a href="{{ url('/') }}" class="app-brand-link gap-2">
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
                                <span class="app-brand-text demo text-body fw-bold ms-1">OKR System</span>
                            </a>
                        </div>
                        <!-- /Logo -->

                        <h4 class="mb-1 pt-2">Welcome to OKR Management! 👋</h4>
                        <p class="mb-4">Please sign-in to your account and start the adventure</p>

                        <form id="loginForm" method="POST" action="{{ route('login.process') }}" class="mb-3">
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="{{ old('email') }}" placeholder="Enter your email" required />
                            </div>

                            <div class="mb-3 form-password-toggle">
                                <div class="d-flex justify-content-between">
                                    <label class="form-label" for="password">Password</label>
                                </div>
                                <div class="input-group input-group-merge">
                                    <input type="password" id="password"
                                        class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                                        name="password" placeholder="Enter your password" required
                                        aria-describedby="password" />
                                    <span class="input-group-text cursor-pointer password-toggle">
                                        <i class="ti ti-eye-off"></i>
                                    </span>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember-me"
                                        {{ old('remember') ? 'checked' : '' }} />
                                    <label class="form-check-label" for="remember-me"> Remember Me </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <button type="submit" class="btn btn-primary d-grid w-100" id="loginBtn">
                                    Sign in
                                </button>
                            </div>
                        </form>

                        <p class="text-center">
                            <span>Forgot your password?</span>
                            <a href="javascript:void(0);"
                                onclick="alert('Contact your administrator to reset your password.')">
                                <span>Reset here</span>
                            </a>
                        </p>
                    </div>
                </div>
                <!-- /Login Card -->
            </div>
        </div>
    </div>

    <!-- Core JS -->
    <script src="{{ asset('plugin/vuexy/assets/vendor/libs/jquery/jquery.js') }}"></script>
    <script src="{{ asset('plugin/vuexy/assets/vendor/libs/popper/popper.js') }}"></script>
    <script src="{{ asset('plugin/vuexy/assets/vendor/js/bootstrap.js') }}"></script>
    <script src="{{ asset('plugin/vuexy/assets/vendor/libs/node-waves/node-waves.js') }}"></script>
    <script src="{{ asset('plugin/vuexy/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js') }}"></script>
    <script src="{{ asset('plugin/vuexy/assets/vendor/libs/hammer/hammer.js') }}"></script>
    <script src="{{ asset('plugin/vuexy/assets/vendor/libs/i18n/i18n.js') }}"></script>
    <script src="{{ asset('plugin/vuexy/assets/vendor/libs/typeahead-js/typeahead.js') }}"></script>
    <script src="{{ asset('plugin/vuexy/assets/vendor/js/menu.js') }}"></script>

    <!-- Vendors JS -->
    <script src="{{ asset('plugin/vuexy/assets/vendor/libs/@form-validation/umd/bundle/popular.min.js') }}"></script>
    <script src="{{ asset('plugin/vuexy/assets/vendor/libs/@form-validation/umd/plugin-bootstrap5/index.min.js') }}">
    </script>
    <script src="{{ asset('plugin/vuexy/assets/vendor/libs/@form-validation/umd/plugin-auto-focus/index.min.js') }}">
    </script>

    <!-- Main JS -->
    <script src="{{ asset('plugin/vuexy/assets/js/main.js') }}"></script>

    <!-- Toastr JS -->
    <script src="{{ asset('plugin/toastr/toastr.min.js') }}"></script>

    <!-- Page JS -->
    <script src="{{ asset('plugin/vuexy/assets/js/pages-auth.js') }}"></script>

    <script>
        // Toast notification function
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

        // Password toggle
        document.addEventListener('DOMContentLoaded', function() {
            const passwordToggle = document.querySelector('.password-toggle');
            const passwordInput = document.getElementById('password');

            if (passwordToggle && passwordInput) {
                passwordToggle.addEventListener('click', function() {
                    const icon = this.querySelector('i');
                    if (passwordInput.type === 'password') {
                        passwordInput.type = 'text';
                        icon.classList.remove('ti-eye-off');
                        icon.classList.add('ti-eye');
                    } else {
                        passwordInput.type = 'password';
                        icon.classList.remove('ti-eye');
                        icon.classList.add('ti-eye-off');
                    }
                });
            }

            // Display toastr notifications for session messages
            @if (session('success'))
                showToast('Success', '{{ session('success') }}', 'success');
            @endif

            @if (session('error'))
                showToast('Error', '{{ session('error') }}', 'error');
            @endif

            @error('email')
                showToast('Validation Error', '{{ $message }}', 'error');
            @enderror

            @error('password')
                showToast('Validation Error', '{{ $message }}', 'error');
            @enderror

            // Show loading state on form submit
            const loginForm = document.getElementById('loginForm');
            const loginBtn = document.getElementById('loginBtn');

            if (loginForm && loginBtn) {
                loginForm.addEventListener('submit', function() {
                    loginBtn.classList.add('btn-loading');
                    loginBtn.disabled = true;
                });
            }
        });
    </script>
</body>

</html>
