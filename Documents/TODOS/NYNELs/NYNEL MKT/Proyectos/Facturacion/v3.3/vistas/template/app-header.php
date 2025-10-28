<header class="app-header">
    <input type="hidden" name="iva" id="iva" value='<?php echo $_SESSION['iva']; ?>'>
    <!-- Start::main-header-container -->
    <div class="main-header-container container-fluid">

        <!-- Start::header-content-left -->
        <div class="header-content-left">

            <!-- Start::header-element -->
            <div class="header-element">
                <div class="horizontal-logo">
                    <a href="#" class="header-logo">
                        <img src="../assets/images/brand-logos/desktop-logo.png" alt="logo" class="desktop-logo">
                        <img src="../assets/images/brand-logos/toggle-logo.png" alt="logo" class="toggle-logo">
                        <img src="../assets/images/brand-logos/desktop-dark.png" alt="logo" class="desktop-dark">
                        <img src="../assets/images/brand-logos/toggle-dark.png" alt="logo" class="toggle-dark">
                        <img src="../assets/images/brand-logos/desktop-white.png" alt="logo" class="desktop-white">
                        <img src="../assets/images/brand-logos/toggle-white.png" alt="logo" class="toggle-white">
                    </a>
                </div>
            </div>
            <!-- End::header-element -->

            <!-- Start::header-element -->
            <div class="header-element">
                <!-- Start::header-link -->
                <a aria-label="Hide Sidebar"
                    class="sidemenu-toggle header-link animated-arrow hor-toggle horizontal-navtoggle"
                    data-bs-toggle="sidebar" href="javascript:void(0);"><span></span></a>
                <!-- End::header-link -->
            </div>
            <!-- End::header-element -->

        </div>
        <!-- End::header-content-left -->

        <!-- Start::header-content-right -->
        <div class="header-content-right">


            <!-- Start::header-element -->
            <div class="header-element country-selector">
                <!-- Start::header-link|dropdown-toggle -->
                <a href="javascript:void(0);" class="header-link dropdown-toggle" data-bs-auto-close="outside"
                    data-bs-toggle="dropdown">
                    <img src="../assets/images/flags/peru_flag.png" alt="img" class="rounded-circle header-link-icon">
                </a>
                <!-- End::header-link|dropdown-toggle -->
                <ul class="main-header-dropdown dropdown-menu dropdown-menu-end" data-popper-placement="none">
                    <li>
                        <a class="dropdown-item d-flex align-items-center" href="javascript:void(0);">
                            <span class="avatar avatar-xs lh-1 me-2">
                                <img src="../assets/images/flags/peru_flag.png" alt="img">
                            </span>
                            Perú
                        </a>
                    </li>

                </ul>
            </div>


            <!-- End::header-element -->

            <div class="header-element header-fullscreen">
                <!-- Start::header-link -->
                <a style="cursor:pointer;" data-bs-toggle="modal" data-bs-target="#modalTcambio" class="header-link">
                    <i class="bx bx-money full-screen-open header-link-icon"></i>
                </a>
                <!-- End::header-link -->
            </div>

            <!-- Start::header-element -->
            <div class="header-element header-theme-mode">
                <!-- Start::header-link|layout-setting -->
                <a href="javascript:void(0);" class="header-link layout-setting">
                    <span class="light-layout">
                        <!-- Start::header-link-icon -->
                        <i class="bx bx-moon header-link-icon"></i>
                        <!-- End::header-link-icon -->
                    </span>
                    <span class="dark-layout">
                        <!-- Start::header-link-icon -->
                        <i class="bx bx-sun header-link-icon"></i>
                        <!-- End::header-link-icon -->
                    </span>
                </a>
                <!-- End::header-link|layout-setting -->
            </div>
            <!-- End::header-element -->

            <div hidden>
                <div>
                    <hr class="dropdown-divider">
                </div>
                <ul class="list-unstyled mb-0" id="header-cart-items-scroll"></ul>
            </div>


            <div hidden class="header-element notifications-dropdown">
                <!-- Start::header-link|dropdown-toggle -->
                <a href="javascript:void(0);" class="header-link dropdown-toggle" data-bs-toggle="dropdown"
                    data-bs-auto-close="outside" id="messageDropdown" aria-expanded="false">
                    <i class="bx bx-bell header-link-icon"></i>
                    <span class="badge bg-secondary rounded-pill header-icon-badge pulse pulse-secondary"
                        id="notification-icon-badge">5</span>
                </a>
                <!-- End::header-link|dropdown-toggle -->
                <!-- Start::main-header-dropdown -->
                <div class="main-header-dropdown dropdown-menu dropdown-menu-end" data-popper-placement="none">
                    <div class="p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <p class="mb-0 fs-17 fw-semibold">Notifications</p>
                            <span class="badge bg-secondary-transparent" id="notifiation-data">5 Unread</span>
                        </div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <ul class="list-unstyled mb-0" id="header-notification-scroll">
                        <li class="dropdown-item">
                            <div class="d-flex align-items-start">
                                <div class="pe-2">
                                    <span class="avatar avatar-md bg-primary-transparent avatar-rounded"><i
                                            class="ti ti-gift fs-18"></i></span>
                                </div>
                                <div class="flex-grow-1 d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="mb-0 fw-semibold"><a href="notifications.html">Your Order Has
                                                Been Shipped</a></p>
                                        <span class="text-muted fw-normal fs-12 header-notification-text">Order
                                            No: 123456 Has Shipped To Your Delivery Address</span>
                                    </div>
                                    <div>
                                        <a href="javascript:void(0);"
                                            class="min-w-fit-content text-muted me-1 dropdown-item-close1"><i
                                                class="ti ti-x fs-16"></i></a>
                                    </div>
                                </div>
                            </div>
                        </li>
                        <li class="dropdown-item">
                            <div class="d-flex align-items-start">
                                <div class="pe-2">
                                    <span class="avatar avatar-md bg-secondary-transparent avatar-rounded"><i
                                            class="ti ti-discount-2 fs-18"></i></span>
                                </div>
                                <div class="flex-grow-1 d-flex align-items-center justify-content-between">
                                    <div>
                                        <p class="mb-0 fw-semibold"><a href="notifications.html">Discount
                                                Available</a></p>
                                        <span class="text-muted fw-normal fs-12 header-notification-text">Discount
                                            Available On Selected Products</span>
                                    </div>
                                    <div>
                                        <a href="javascript:void(0);"
                                            class="min-w-fit-content text-muted me-1 dropdown-item-close1"><i
                                                class="ti ti-x fs-16"></i></a>
                                    </div>
                                </div>
                            </div>
                        </li>



                    </ul>
                    <div class="p-3 empty-header-item1 border-top">
                        <div class="d-grid">
                            <a href="notifications.html" class="btn btn-primary">View All</a>
                        </div>
                    </div>
                    <div class="p-5 empty-item1 d-none">
                        <div class="text-center">
                            <span class="avatar avatar-xl avatar-rounded bg-secondary-transparent">
                                <i class="ri-notification-off-line fs-2"></i>
                            </span>
                            <h6 class="fw-semibold mt-3">No New Notifications</h6>
                        </div>
                    </div>
                </div>
                <!-- End::main-header-dropdown -->
            </div>
            <!-- End::header-element -->

            <!-- Start::header-element -->
            <div class="header-element header-fullscreen">
                <!-- Start::header-link -->
                <a onclick="openFullscreen();" href="javascript:void(0);" class="header-link">
                    <i class="bx bx-fullscreen full-screen-open header-link-icon"></i>
                    <i class="bx bx-exit-fullscreen full-screen-close header-link-icon d-none"></i>
                </a>
                <!-- End::header-link -->
            </div>

            <!-- Start::header-element -->
            <div hidden class="header-element header-shortcuts-dropdown">
                <!-- Start::header-link|dropdown-toggle -->
                <a href="javascript:void(0);" class="header-link dropdown-toggle" data-bs-toggle="dropdown"
                    data-bs-auto-close="outside" id="notificationDropdown" aria-expanded="false">
                    <i class="bx bx-grid-alt header-link-icon"></i>
                </a>
                <!-- End::header-link|dropdown-toggle -->
                <!-- Start::main-header-dropdown -->
                <div class="main-header-dropdown header-shortcuts-dropdown dropdown-menu pb-0 dropdown-menu-end"
                    aria-labelledby="notificationDropdown">
                    <div class="p-3">

                    </div>
                    <div class="dropdown-divider mb-0"></div>
                    <div class="main-header-shortcuts p-2" id="header-shortcut-scroll">

                    </div>

                </div>
                <!-- End::main-header-dropdown -->
            </div>


            <!-- End::header-element -->

            <!-- Start::header-element -->
            <div class="header-element">
                <!-- Start::header-link|dropdown-toggle -->
                <a href="javascript:void(0);" class="header-link dropdown-toggle" id="mainHeaderProfile"
                    data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                    <div class="d-flex align-items-center">
                        <div class="me-sm-2 me-0">
                            <img src="../files/usuarios/<?php echo $_SESSION['imagen']; ?>" alt="img" width="32"
                                height="32" class="rounded-circle">
                        </div>
                        <div class="d-sm-block d-none">
                            <p class="fw-semibold mb-0 lh-1">
                                <?php echo $_SESSION['nombre']; ?>
                            </p>
                            <span class="op-7 fw-normal d-block fs-11">
                                <?php echo $_SESSION['estado']; ?>
                            </span>
                        </div>
                    </div>
                </a>
                <!-- End::header-link|dropdown-toggle -->
                <ul class="main-header-dropdown dropdown-menu pt-0 overflow-hidden header-profile-dropdown dropdown-menu-end"
                    aria-labelledby="mainHeaderProfile">
                    <li><a class="dropdown-item d-flex" href="../vistas/usuario"><i
                                class="ti ti-user-circle fs-18 me-2 op-7"></i>Perfil</a></li>
                    <li><a class="dropdown-item d-flex" href="../vistas/empresa"><i
                                class="ti ti-adjustments-horizontal fs-18 me-2 op-7"></i>Ajustes</a></li>
                    <li><a class="dropdown-item d-flex" href="#"><i
                                class="ti ti-headset fs-18 me-2 op-7"></i>Soporte</a></li>
                    <li><a class="dropdown-item d-flex" href="../ajax/usuario?op=salir"><i
                                class="ti ti-logout fs-18 me-2 op-7"></i>Salir</a></li>
                </ul>
            </div>
            <!-- End::header-element -->

            <!-- Start::header-element -->
            <div class="header-element">
                <!-- Start::header-link|switcher-icon -->
                <a href="javascript:void(0);" class="header-link switcher-icon" data-bs-toggle="offcanvas"
                    data-bs-target="#switcher-canvas">
                    <i class="bx bx-cog header-link-icon"></i>
                </a>
                <!-- End::header-link|switcher-icon -->
            </div>
            <!-- End::header-element -->
        </div>
        <!-- End::header-content-right -->
    </div>
    <!-- End::main-header-container -->
</header>
<!-- /app-header -->