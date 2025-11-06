<!-- Topbar -->
<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

    <!-- Sidebar Toggle (Topbar) -->
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>

    <!-- Dynamic Page Title -->
    <div class="d-none d-sm-inline-block mr-auto ml-md-3 my-2">
        <h4 class="text-gray-800 mb-0">
            @hasSection('page-title')
                @yield('page-title')
            @else
                {{ $pageTitle ?? 'Dashboard' }}
            @endif
        </h4>
        <small class="text-muted">
            @hasSection('page-subtitle')
                @yield('page-subtitle')
            @else
                {{ $pageSubtitle ?? 'Welcome to FitFast Admin' }}
            @endif
        </small>
    </div>

    <!-- Topbar Navbar -->
    <ul class="navbar-nav ml-auto">

        <!-- Quick Stats (Visible on larger screens) -->
        <li class="nav-item dropdown no-arrow mx-1 d-none d-lg-block">
            <div class="nav-link">
                <div class="d-flex align-items-center">
                    <div class="px-3 border-right">
                        <small class="text-muted">Active Users</small>
                        <div class="text-center font-weight-bold text-success">{{ $topbarStats['active_users'] ?? 0 }}</div>
                    </div>
                    <div class="px-3 border-right">
                        <small class="text-muted">Pending Support</small>
                        <div class="text-center font-weight-bold text-warning">{{ $topbarStats['pending_support'] ?? 0 }}</div>
                    </div>
                    <div class="px-3">
                        <small class="text-muted">Today's Revenue</small>
                        <div class="text-center font-weight-bold text-primary">${{ number_format($topbarStats['today_revenue'] ?? 0, 2) }}</div>
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
                <span class="badge badge-danger badge-counter">{{ $topbarStats['pending_support'] ?? 0 }}</span>
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
                        <span class="font-weight-bold">{{ $topbarStats['pending_support'] ?? 0 }} pending support tickets</span>
                    </div>
                </a>
                <a class="dropdown-item d-flex align-items-center" href="{{ route('cms.orders.index') }}">
                    <div class="mr-3">
                        <div class="icon-circle bg-success">
                            <i class="fas fa-shopping-cart text-white"></i>
                        </div>
                    </div>
                    <div>
                        <div class="small text-gray-500">Today</div>
                        ${{ number_format($topbarStats['today_revenue'] ?? 0, 2) }} revenue generated
                    </div>
                </a>
                <a class="dropdown-item d-flex align-items-center" href="{{ route('cms.users.index') }}">
                    <div class="mr-3">
                        <div class="icon-circle bg-info">
                            <i class="fas fa-user-plus text-white"></i>
                        </div>
                    </div>
                    <div>
                        <div class="small text-gray-500">Active</div>
                        {{ $topbarStats['active_users'] ?? 0 }} users with orders
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
                <span class="badge badge-warning badge-counter">{{ $topbarStats['pending_support'] ?? 0 }}</span>
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
                        <div class="text-truncate">You have {{ $topbarStats['pending_support'] ?? 0 }} pending support tickets</div>
                        <div class="small text-gray-500">Click to view</div>
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
                <a class="dropdown-item" href="{{ route('cms.dashboard') }}">
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
                <form method="POST" action="{{ route('cms.logout') }}">
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
.status-indicator {
    height: 0.8rem;
    width: 0.8rem;
    border-radius: 50%;
    position: absolute;
    bottom: 0;
    right: 0;
    border: 2px solid #fff;
}
.dropdown-list-image {
    position: relative;
}
.border-right {
    border-right: 1px solid #e3e6f0 !important;
}
</style>
