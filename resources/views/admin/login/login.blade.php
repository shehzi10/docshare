<!doctype html>
<html lang="en">

<head>

    <meta charset="utf-8" />
    <title>Admin Login | Docshr</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesdesign" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('public/admin/assets/images/favicon.ico') }}">

    <!-- Bootstrap Css -->
    <link href="{{ asset('public/admin/assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="{{ asset('public/admin/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="{{ asset('public/admin/assets/css/app.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('public/admin/assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />

    <style>
        .btn-primary {
            color: #fff;
            background-color: #0659fd;
            border-color: #0659fd;
        }

        .btn-primary:hover {
            color: #fff;
            background-color: #7ad0f1;
            border-color: #7ad0f1;
        }
    </style>
</head>

<body style="background: #0659fd;">

    <div class="account-pages my-5 pt-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-4 col-lg-6 col-md-8">
                    <div class="card">
                        <div class="card-body p-4">
                            <div class="">
                                <div class="text-center">
                                    <a href="index.html" class="">
                                        <img src="{{ asset('public/admin/assets/images/logo-sm.png') }}" alt="" class="auth-logo logo-dark mx-auto">
                                        <img src="{{ asset('public/admin/assets/images/logo-sm.png') }}" alt=""
                                            class="auth-logo logo-light mx-auto">
                                    </a>
                                </div>
                                <!-- end row -->
                                <h4 class="font-size-18 text-muted mt-2 text-center">Welcome !</h4>
                                <p class="mb-5 text-center">Log in to Docshr Admin Panel</p>
                                <form class="form-horizontal" method="POST" action="{{ route('doLogin') }}">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="mb-4">
                                                <label class="form-label" for="username">Username</label>
                                                <input type="text" class="form-control" id="username"
                                                    placeholder="Enter Email" name="email">
                                            </div>
                                            <div class="mb-4">
                                                <label class="form-label" for="userpassword">Password</label>
                                                <input type="password" class="form-control" id="userpassword"
                                                    placeholder="Enter password" name="password">
                                            </div>

                                            {{--  <div class="row">
                                                <div class="col">
                                                    <div class="form-check">
                                                        <input type="checkbox" class="form-check-input"
                                                            id="customControlInline">
                                                        <label class="form-label" class="form-check-label"
                                                            for="customControlInline">Remember me</label>
                                                    </div>
                                                </div>
                                                <div class="col-7">
                                                    <div class="text-md-end mt-3 mt-md-0">
                                                        <a href="recoverpw.html" class="text-muted"><i
                                                                class="mdi mdi-lock"></i> Forgot password?</a>
                                                    </div>
                                                </div>
                                            </div>  --}}
                                            <div class="d-grid mt-4">
                                                <button class="btn btn-primary waves-effect waves-light"
                                                    type="submit">Log In</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <!-- end row -->
        </div>
    </div>
    <!-- end Account pages -->



    <!-- JAVASCRIPT -->
    <script src="{{ asset('public/admin/assets/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('public/admin/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('public/admin/assets/libs/metismenu/metisMenu.min.js') }}"></script>
    <script src="{{ asset('public/admin/assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('public/admin/assets/libs/node-waves/waves.min.js') }}"></script>

    <script src="assets/js/app.js"></script>

</body>

</html>
