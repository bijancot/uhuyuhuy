<!-- Sidebar -->
<ul class="navbar-nav bg-white sidebar sidebar-light accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="<?= site_url('dashboard'); ?>">
        <img src="<?= base_url('assets/img/utbm_asli.png'); ?>" class="rounded mx-auto d-block" width="250">
        <!-- <div class="sidebar-brand-text mx-1" style="font-size: 10px !important; text-align: left;">United&nbsp;Tractors Building&nbsp;Management</div> -->
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item active">
        <a class="nav-link" href="<?= site_url('debitnote/dashboard'); ?>">
            <i class="fas fa-tachometer-alt"></i>
            <span>Monitoring DN</span>
        </a>
    </li>

    <!-- Nav Item - Dashboard -->
    <li class="nav-item active">
        <a class="nav-link" href="<?= site_url('debitnote'); ?>">
            <i class="fas fa-book-open"></i>
            <span>DN - Raw</span>
        </a>
    </li>

    <!-- Nav Item - Dashboard -->
    <li class="nav-item active">
        <a class="nav-link" href="<?= site_url('debitnote/generated'); ?>">
            <i class="fas fa-book-open"></i>
            <span>DN - Generate</span>
        </a>
    </li>

    <!-- Nav Item - Dashboard -->
    <li class="nav-item active">
        <a class="nav-link" href="<?= site_url('debitnote/approved'); ?>">
            <i class="fas fa-book-open"></i>
            <span>DN - Approved</span>
        </a>
    </li>

    <!-- Nav Item - Dashboard -->
    <li class="nav-item active">
        <a class="nav-link" href="<?= site_url('debitnote/rejected'); ?>">
            <i class="fas fa-book-open"></i>
            <span>DN - Rejected</span>
        </a>
    </li>
    <!-- Nav Item - Dashboard -->
    <li class="nav-item active">
        <a class="nav-link" href="<?= site_url('debitnote/progress'); ?>">
            <i class="fas fa-book-open"></i>
            <span>DN - Progress</span>
        </a>
    </li>
    <!-- Nav Item - Dashboard -->
    <li class="nav-item active">
        <a class="nav-link" href="<?= site_url('debitnote/overdue'); ?>">
            <i class="fas fa-book-open"></i>
            <span>DN - Overdue</span>
        </a>
    </li>
    <!-- Nav Item - Dashboard -->
    <li class="nav-item active">
        <a class="nav-link" href="<?= site_url('debitnote/finished'); ?>">
            <i class="fas fa-book-open"></i>
            <span>DN - Finished</span>
        </a>
    </li>

    <!-- Nav Item - Dashboard -->
    <li class="nav-item active">
        <a class="nav-link" href="<?= site_url('debitnote/reversed'); ?>">
            <i class="fas fa-book-open"></i>
            <span>DN - Reversed</span>
        </a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0 " id="sidebarToggle"></button>
    </div>

</ul>
<!-- End of Sidebar -->

<!-- Content Wrapper -->
<div id="content-wrapper" class="d-flex flex-column">