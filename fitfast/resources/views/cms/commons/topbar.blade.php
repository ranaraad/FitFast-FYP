<!-- Topbar -->
<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

    <!-- Sidebar Toggle (Topbar) -->
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>

    <!-- Page Title -->
    <div class="d-none d-sm-inline-block mr-auto ml-md-3 my-2">
        <h4 class="text-gray-800 mb-0">@yield('page-title', 'Dashboard')</h4>
        <small class="text-muted">@yield('page-subtitle', 'Welcome to FitFast Admin')</small>
    </div>

    <!-- Topbar Navbar -->
    <ul class="navbar-nav ml-auto">

        <!-- Quick Stats (Visible on larger screens) -->
        <li class="nav-item dropdown no-arrow mx-1 d-none d-lg-block">
            <div class="nav-link">
                <div class="d-flex align-items-center">
                    <div class="px-3 border-right">
                        <small class="text-muted">Online Users</small>
                        <div class="text-center font-weight-bold text-success">24</div>
                    </div>
                    <div class="px-3 border-right">
                        <small class="text-muted">Pending Support</small>
                        <div class="text-center font-weight-bold text-warning">5</div>
                    </div>
                    <div class="px-3">
                        <small class="text-muted">New Orders</small>
                        <div class="text-center font-weight-bold text-primary">12</div>
                    </div>
                </div>
            </div>
        </li>

        <!-- Nav Item - Alerts -->
        <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-bell fa-fw"></i>
                <!-- Counter - Alerts -->
                <span class="badge badge-danger badge-counter">3</span>
            </a>
            <!-- Dropdown - Alerts -->
            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="alertsDropdown">
                <h6 class="dropdown-header bg-primary text-white">
                    <i class="fas fa-bell mr-2"></i>Notifications
                </h6>
                <a class="dropdown-item d-flex align-items-center" href="{{ route('cms.chat-support.index') }}">
                    <div class="mr-3">
                        <div class="icon-circle bg-warning">
                            <i class="fas fa-comments text-white"></i>
                        </div>
                    </div>
                    <div>
                        <div class="small text-gray-500">Just now</div>
                        <span class="font-weight-bold">New support ticket received</span>
                    </div>
                </a>
                <a class="dropdown-item d-flex align-items-center" href="#">
                    <div class="mr-3">
                        <div class="icon-circle bg-success">
                            <i class="fas fa-shopping-cart text-white"></i>
                        </div>
                    </div>
                    <div>
                        <div class="small text-gray-500">10 minutes ago</div>
                        New order placed #ORD-2876
                    </div>
                </a>
                <a class="dropdown-item d-flex align-items-center" href="{{ route('cms.users.index') }}">
                    <div class="mr-3">
                        <div class="icon-circle bg-info">
                            <i class="fas fa-user-plus text-white"></i>
                        </div>
                    </div>
                    <div>
                        <div class="small text-gray-500">1 hour ago</div>
                        3 new users registered today
                    </div>
                </a>
                <a class="dropdown-item text-center small text-gray-500" href="#">View All Notifications</a>
            </div>
        </li>

        <!-- Nav Item - Messages -->
        <li class="nav-item dropdown no-arrow mx-1">
            <a class="nav-link dropdown-toggle" href="#" id="messagesDropdown" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-envelope fa-fw"></i>
                <!-- Counter - Messages -->
                <span class="badge badge-warning badge-counter">2</span>
            </a>
            <!-- Dropdown - Messages -->
            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="messagesDropdown">
                <h6 class="dropdown-header bg-info text-white">
                    <i class="fas fa-envelope mr-2"></i>Support Messages
                </h6>
                <a class="dropdown-item d-flex align-items-center" href="{{ route('cms.chat-support.index') }}">
                    <div class="dropdown-list-image mr-3">
                        <div class="status-indicator bg-success"></div>
                        <div class="icon-circle bg-light">
                            <i class="fas fa-user text-gray-600"></i>
                        </div>
                    </div>
                    <div>
                        <div class="text-truncate">Hi, I need help with my recent order tracking</div>
                        <div class="small text-gray-500">Customer · 15m ago</div>
                    </div>
                </a>
                <a class="dropdown-item d-flex align-items-center" href="{{ route('cms.chat-support.index') }}">
                    <div class="dropdown-list-image mr-3">
                        <div class="status-indicator bg-warning"></div>
                        <div class="icon-circle bg-light">
                            <i class="fas fa-user text-gray-600"></i>
                        </div>
                    </div>
                    <div>
                        <div class="text-truncate">Question about size exchange policy</div>
                        <div class="small text-gray-500">Customer · 1h ago</div>
                    </div>
                </a>
                <a class="dropdown-item text-center small text-gray-500" href="{{ route('cms.chat-support.index') }}">View All Messages</a>
            </div>
        </li>

        <div class="topbar-divider d-none d-sm-block"></div>

        <!-- Nav Item - User Information -->
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-800 small font-weight-bold">
                    {{ Auth::user()->name ?? 'Admin' }}
                </span>
                <div class="img-profile rounded-circle bg-primary d-flex align-items-center justify-content-center">
                    <span class="text-white font-weight-bold">
                        {{ substr(Auth::user()->name ?? 'A', 0, 1) }}
                    </span>
                </div>
            </a>
            <!-- Dropdown - User Information -->
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <div class="dropdown-header bg-gradient-primary text-white">
                    <i class="fas fa-user-circle mr-2"></i>
                    <strong>{{ Auth::user()->name ?? 'Admin' }}</strong>
                    <div class="small">{{ Auth::user()->email ?? 'admin@fitfast.com' }}</div>
                </div>
                <a class="dropdown-item" href="#">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    My Profile
                </a>
                <a class="dropdown-item" href="#">
                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                    Account Settings
                </a>
                <a class="dropdown-item" href="#">
                    <i class="fas fa-chart-line fa-sm fa-fw mr-2 text-gray-400"></i>
                    Analytics
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    Logout
                </a>
            </div>
        </li>

    </ul>

</nav>

<!-- Logout Modal-->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Ready to Leave?</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">Select "Logout" below if you are ready to end your current session.</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancel</button>
                <form method="POST">
                    @csrf
                    <button class="btn btn-primary" type="submit">Logout</button>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.img-profile {
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}
.icon-circle {
    width: 2.5rem;
    height: 2.5rem;
    border-radius: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.dropdown-header.bg-primary {
    border-radius: 0.35rem 0.35rem 0 0;
}
</style>
