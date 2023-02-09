<a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    <i class="fas fa-bell fa-fw"></i>
    <!-- Counter - Alerts -->
    <span class="badge badge-danger badge-counter"><?= $notifCount?></span>
</a>
<!-- Dropdown - Alerts -->
<div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="alertsDropdown">
    <h6 class="dropdown-header">
        Alerts Center
    </h6>
    <?php
        foreach ($notifs as $item) {
            if($item->IS_SEEN == '0'){
                $date = date_create($item->TS_TRANS);
                echo '
                    <a class="dropdown-item d-flex align-items-center" href="'.site_url('notif/readTransactionAll').'">
                        <div class="mr-3">
                            <div class="icon-circle bg-primary">
                                <i class="fas fa-file-alt text-white"></i>
                            </div>
                        </div>
                        <div>
                            <div class="small text-gray-500">'.date_format($date, 'd-M-Y').'</div>
                            <span class="font-weight-bold">
                                '.$item->NAMA_USERS.' Mengajukan Form '.$item->NAMA_FORM.' Baru !
                            </span>
                        </div>
                    </a>
                ';
            }
        }
        if($notifCount != '0'){
            echo '
                <a class="dropdown-item text-center small text-gray-500" href="'.site_url('notif/readTransactionAll').'">Show All Alerts</a>
                ';
            }else{
                echo '
                    <a class="dropdown-item text-center small text-gray-500"> No Notification</a>
                ';
        }
    ?>
</div>