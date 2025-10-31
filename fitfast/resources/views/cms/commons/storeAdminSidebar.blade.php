<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('store-admin.dashboard') }}">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-store"></i>
        </div>
        <div class="sidebar-brand-text mx-3">FitFast</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item">
        <a class="nav-link" href="{{ route('store-admin.dashboard') }}">
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
            <span>Store Management</span>
        </a>
        <div id="collapseManagement" class="collapse" aria-labelledby="headingManagement"
            data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Store Operations:</h6>
                <a class="collapse-item" href="{{ route('store-admin.stores.index') }}">
                    <i class="fas fa-store fa-sm mr-2"></i>My Stores
                </a>
                <a class="collapse-item" href="{{ route('store-admin.items.index') }}">
                    <i class="fas fa-box fa-sm mr-2"></i>Items
                </a>
                <a class="collapse-item" href="{{ route('store-admin.orders.index') }}">
                    <i class="fas fa-shopping-cart fa-sm mr-2"></i>Orders
                </a>
                <a class="collapse-item" href="{{ route('store-admin.deliveries.index') }}">
                    <i class="fas fa-shipping-fast fa-sm mr-2"></i>Deliveries
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
        <a class="nav-link" href="{{ route('store-admin.orders.index', ['status' => 'pending']) }}">
            <i class="fas fa-fw fa-clock"></i>
            <span>Pending Orders</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="{{ route('store-admin.items.index', ['low_stock' => true]) }}">
            <i class="fas fa-fw fa-exclamation-triangle"></i>
            <span>Low Stock Items</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="{{ route('store-admin.deliveries.index', ['status' => 'pending']) }}">
            <i class="fas fa-fw fa-truck"></i>
            <span>Pending Deliveries</span>
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="{{ route('store-admin.orders.index', ['status' => 'completed']) }}">
            <i class="fas fa-fw fa-chart-line"></i>
            <span>Sales Analytics</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">


    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>
