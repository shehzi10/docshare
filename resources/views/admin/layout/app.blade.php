<!doctype html>
<html lang="en">

<head>

    <meta charset="utf-8" />
    <title>Users | Docshr</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="Premium Multipurpose Admin & Dashboard Template" name="description" />
    <meta content="Themesdesign" name="author" />
    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ asset('public/admin/assets/images/favicon.ico') }}">

    <!-- Responsive Table css -->
    <link href="{{ asset('public/admin/assets/libs/admin-resources/rwd-table/rwd-table.min.css') }}" rel="stylesheet" type="text/css" />

    <!-- Bootstrap Css -->
    <link href="{{ asset('public/admin/assets/css/bootstrap.min.css') }}" id="bootstrap-style" rel="stylesheet" type="text/css" />
    <!-- Icons Css -->
    <link href="{{ asset('public/admin/assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- App Css-->
    <link href="{{ asset('public/admin/assets/css/app.min.css') }}" id="app-style" rel="stylesheet" type="text/css" />
    <style>
        .btn-toolbar {
            display: none !important;
        }

        .btn-primary {
            color: #fff;
            background-color: #0659fd;
            border-color: #0659fd;
        }

        .btn-primary:hover {
            color: #fff;
            background-color: #7ad0f1 !important;
            border-color: #7ad0f1;
        }

        .text-just {
            text-align: justify;
        }


        .tox .tox-notification--warn,
        .tox .tox-notification--warning {
            background-color: #fffaea;
            border-color: #ffe89d;
            color: #222f3e;
            display: none !important;
        }

        .tox-statusbar {
            display: none !important;
        }

        body[data-sidebar=dark] .navbar-brand-box {
            background: #0659fd !important;
        }

        body[data-sidebar=dark] .vertical-menu {
            background: #0659fd !important;
        }

        body[data-sidebar=dark] #sidebar-menu ul li a {
            color: #fff;
        }
    </style>
</head>

<body data-sidebar="dark">

    <!-- <body data-layout="horizontal" data-topbar="dark"> -->

    <!-- Begin page -->
    <div id="layout-wrapper">


        <header id="page-topbar">
            <div class="navbar-header">
                <div class="d-flex">
                    <!-- LOGO -->
                    <div class="navbar-brand-box text-center">
                        <a href="login.html" class="logo logo-dark">
                            <span class="logo-sm">
                                <img src="{{ asset('public/admin/assets/images/fav-icon.png') }}" alt="logo-sm-dark" height="30">
                            </span>
                            <span class="logo-lg">
                                <img src="{{ asset('public/admin/assets/images/logo-dark.png') }}" alt="logo-dark">
                            </span>
                        </a>

                        <a href="login.html" class="logo logo-light">
                            <span class="logo-sm">
                                <img src="{{ asset('public/admin/assets/images/fav-icon.png') }}" alt="logo-sm-light" height="30">
                            </span>
                            <span class="logo-lg">
                                <img src="{{ asset('public/admin/assets/images/logo-light.png') }}" alt="logo-light">
                            </span>
                        </a>
                    </div>

                    <button type="button" class="btn btn-sm px-3 font-size-24 header-item waves-effect"
                        id="vertical-menu-btn">
                        <i class="ri-menu-2-line align-middle"></i>
                    </button>

                </div>

                <div class="d-flex">



                </div>
            </div>
        </header>

        <!-- ========== Left Sidebar Start ========== -->
        <div class="vertical-menu">

            <div data-simplebar class="h-100">

                <!--- Sidemenu -->
                <div id="sidebar-menu">
                    <!-- Left Menu Start -->
                    <ul class="metismenu list-unstyled" id="side-menu">

                        <li>
                            <a href="users.html" class="">

                                <span>Users</span>
                            </a>
                        </li>
                        <li>
                            <a href="susbcription-plans.html" class="">

                                <span>Susbcription Plans</span>
                            </a>
                        </li>


                        <li>
                            <a href="payment.html" class="">

                                <span>Payment</span>
                            </a>
                        </li>

                        <li>
                            <a href="help.html" class="">

                                <span>Help</span>
                            </a>
                        </li>
                        <li>
                            <a href="terms-&-services.html" class="">
                                <span>Terms & Services</span>
                            </a>
                        </li>
                        <li>
                            <a href="privacy-policy.html" class="">
                                <span>Privacy Policy</span>
                            </a>
                        </li>
                        <li>
                            <a href="login.html" class="">
                                <span>LogOut</span>
                            </a>
                        </li>
                    </ul>
                </div>
                <!-- Sidebar -->
            </div>
        </div>
        <!-- Left Sidebar End -->





        @yield('content')



        <footer class="footer">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6">
                        <script>
                            document.write(new Date().getFullYear())
                        </script> ©Time
                    </div>
                    <div class="col-sm-6">
                        <div class="text-sm-end d-none d-sm-block">
                            All Rights Reserved
                        </div>
                    </div>
                </div>
            </div>
        </footer>

    </div>
    <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->



    </div> <!-- end slimscroll-menu-->
    </div>
    <!-- /Right-bar -->

    <!-- Right bar overlay-->
    <div class="rightbar-overlay"></div>

    <!-- JAVASCRIPT -->
    <script src="{{ asset('public/admin/assets/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('public/admin/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('public/admin/assets/libs/metismenu/metisMenu.min.js') }}"></script>
    <script src="{{ asset('public/admin/assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('public/admin/assets/libs/node-waves/waves.min.js') }}"></script>

    <!-- Responsive Table js -->
    <script src="{{ asset('public/admin/assets/libs/admin-resources/rwd-table/rwd-table.min.js') }}"></script>

    <!-- Init js -->
    <script src="{{ asset('public/admin/assets/js/pages/table-responsive.init.js') }}"></script>

    <script src="{{ asset('public/admin/assets/js/app.js') }}"></script>

</body>

</html>
