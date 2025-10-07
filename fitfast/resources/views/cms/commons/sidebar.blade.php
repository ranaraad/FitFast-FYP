<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('cms.dashboard') }}">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-laugh-wink"></i>
        </div>
        <div class="sidebar-brand-text mx-3">FitFast</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item">
        <a class="nav-link" href="{{ route('cms.dashboard') }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Main Navigation - Single Collapsible Menu -->
    <li class="nav-item">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseManagement"
            aria-expanded="true" aria-controls="collapseManagement">
            <i class="fas fa-fw fa-cog"></i>
            <span>Management</span>
        </a>
        <div id="collapseManagement" class="collapse" aria-labelledby="headingManagement" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">User Management:</h6>
                <a class="collapse-item" href="{{ route('cms.users.index') }}">
                    <i class="fas fa-users fa-sm mr-2"></i>Users
                </a>

                <h6 class="collapse-header mt-3">Store Operations:</h6>
                <a class="collapse-item" href="{{ route('cms.stores.index') }}">
                    <i class="fas fa-store fa-sm mr-2"></i>Stores
                </a>
                <a class="collapse-item" href="{{ route('cms.items.index') }}">
                    <i class="fas fa-box fa-sm mr-2"></i>Items
                </a>
                <a class="collapse-item" href="{{ route('cms.orders.index') }}">
                    <i class="fas fa-shopping-cart fa-sm mr-2"></i>Orders
                </a>
                <a class="collapse-item" href="{{ route('cms.deliveries.index') }}">
                    <i class="fas fa-shipping-fast fa-sm mr-2"></i>Deliveries
                </a>

                <h6 class="collapse-header mt-3">Financial:</h6>
                <a class="collapse-item" href="{{ route('cms.payments.index') }}">
                    <i class="fas fa-credit-card fa-sm mr-2"></i>Payments
                </a>
                <a class="collapse-item" href="{{ route('cms.payment-methods.index') }}">
                    <i class="fas fa-wallet fa-sm mr-2"></i>Payment Methods
                </a>

                <h6 class="collapse-header mt-3">Customer Experience:</h6>
                <a class="collapse-item" href="{{ route('cms.carts.index') }}">
                    <i class="fas fa-shopping-basket fa-sm mr-2"></i>Carts
                </a>
                <a class="collapse-item" href="{{ route('cms.reviews.index') }}">
                    <i class="fas fa-star fa-sm mr-2"></i>Reviews
                </a>

                <h6 class="collapse-header mt-3">Support & Content:</h6>
                <a class="collapse-item" href="{{ route('cms.chat-support.index') }}">
                    <i class="fas fa-comments fa-sm mr-2"></i>Chat Support
                </a>
                <a class="collapse-item" href="{{ route('cms.faqs.index') }}">
                    <i class="fas fa-question-circle fa-sm mr-2"></i>FAQs
                </a>
                <a class="collapse-item" href="{{ route('cms.tips.index') }}">
                    <i class="fas fa-lightbulb fa-sm mr-2"></i>Tips
                </a>
            </div>
        </div>
    </li>

    <!-- Quick Access Section -->
    <hr class="sidebar-divider">

    <div class="sidebar-heading">
        Quick Access
    </div>

    <!-- Quick Stats -->
    <li class="nav-item">
        <a class="nav-link" href="{{ route('cms.orders.index') }}">
            <i class="fas fa-fw fa-chart-line"></i>
            <span>Order Analytics</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="{{ route('cms.chat-support.index') }}">
            <i class="fas fa-fw fa-headset"></i>
            <span>Support Tickets</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="{{ route('cms.payments.index', ['status' => 'pending']) }}">
            <i class="fas fa-fw fa-clock"></i>
            <span>Pending Payments</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="{{ route('cms.deliveries.index', ['status' => 'pending']) }}">
            <i class="fas fa-fw fa-truck"></i>
            <span>Pending Deliveries</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
