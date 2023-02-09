<!-- Main Content -->
<div id="content">
    <?php
    $notifs         = $this->db->where_in('STAT_DEBITNOTE', ['2', '3'])->order_by('TSUPDATE_APP', 'desc')->get_where('V_DEBITNOTE_APPROVAL_GET', ['IS_SEEN' => '0'], 5)->result();
    $notifCount     = $this->db->where_in('STAT_DEBITNOTE', ['2', '3'])->get_where('V_DEBITNOTE_APPROVAL_GET', ['IS_SEEN' => '0'])->num_rows();
    ?>
    <!-- Topbar -->
    <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

        <!-- Sidebar Toggle (Topbar) -->
        <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
            <i class="fa fa-bars"></i>
        </button>

        <!-- Topbar Navbar -->
        <ul class="navbar-nav ml-auto">

            <!-- Nav Item - Alerts -->
            <li class="nav-item dropdown no-arrow mx-1 notifs">
                <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <i class="fas fa-bell fa-fw"></i>
                    <!-- Counter - Alerts -->
                    <span class="badge badge-danger badge-counter"><?= $notifCount ?></span>
                </a>
                <!-- Dropdown - Alerts -->
                <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="alertsDropdown">
                    <h6 class="dropdown-header">
                        Alerts Center
                    </h6>
                    <?php
                    foreach ($notifs as $item) {
                        $date = date_create($item->TSUPDATE_APP);
                        if ($item->STAT_DEBITNOTE == '2') {
                            $labelStatus = 'No DN ' . $item->NOFAKTUR_DEBITNOTE . ' Disetujui!';
                        } else if ($item->STAT_DEBITNOTE == '3') {
                            $labelStatus = 'No DN ' . $item->NOFAKTUR_DEBITNOTE . ' Ditolak!';
                        }

                        echo '
                                <a class="dropdown-item d-flex align-items-center" href="' . site_url('notif/readDebitnoteAll/' . $item->STAT_DEBITNOTE) . '">
                                    <div class="mr-3">
                                        <div class="icon-circle bg-primary">
                                            <i class="fas fa-file-alt text-white"></i>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="small text-gray-500">' . date_format($date, 'd-M-Y') . '</div>
                                        <span class="font-weight-bold">
                                            ' . $labelStatus . '
                                        </span>
                                    </div>
                                </a>
                            ';
                    }
                    if ($notifCount != '0') {
                        echo '
                                <a class="dropdown-item text-center small text-gray-500" href="' . site_url('notif/readDebitnoteAll/' . $notifs[0]->STAT_DEBITNOTE) . '">Show All Alerts</a>
                                ';
                    } else {
                        echo '
                                    <a class="dropdown-item text-center small text-gray-500"> No Notification</a>
                                ';
                    }
                    ?>
                </div>
            </li>

            <div class="topbar-divider d-none d-sm-block"></div>

            <!-- Nav Item - User Information -->
            <li class="nav-item dropdown no-arrow">
                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="mr-2 d-none d-lg-inline text-gray-600 small">Administrator</span>
                    <img class="img-profile rounded-circle" src="<?= base_url('assets/img/undraw_profile.svg'); ?>">
                </a>
                <!-- Dropdown - User Information -->
                <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                        <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                        Logout
                    </a>
                </div>
            </li>

        </ul>

    </nav>
    <!-- End of Topbar -->