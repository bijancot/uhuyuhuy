<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Monitoring Debit Note</h1>
        <div>
            <form action="<?= site_url('debitnote/downloadExcel') ?>" method="post">
                <button class="btn btn-danger" type="button" id="btnPdf" onclick="makePDF()"><i class="fas fa-file-download"></i> PDF</button>
                <button type="submit" class="btn btn-success" type="button"><i class="fas fa-file-download"></i> Excel</button>
                <button class="btn btn-warning" type="button" data-toggle="collapse" data-target=".multi-collapse" aria-expanded="false" id="btnCollapse">Hide All</button>
                <input type="hidden" name="year" value="<?= date('Y') ?>" id="yearReport" />
            </form>
        </div>
    </div>

    <!-- Content Row -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">Account Receivables</h6>
                </div>
                <div class="card-body">
                    <div class="row" hidden>
                        <div id="headerCapture" class="col-12" style="margin-bottom: 1.5rem;text-align: center;">
                            <img src="<?= base_url('assets/img/debitnote/header.png'); ?>">
                        </div>
                    </div>
                    <div class="row">
                        <div id="capture" class="col-12">
                            <div class="row">
                                <div class="col-6 col-md-6 mb-4 mr-0">
                                    <div class="card border-left-primary shadow h-100 py-2">
                                        <div class="card-body">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                        Total DN</div>
                                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= 'Rp. ' . number_format($total, 0, ',', '.') ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-6 mb-4 mr-0">
                                    <div class="card border-left-success shadow h-100 py-2">
                                        <div class="card-body">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                        Total Received DN</div>
                                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= 'Rp. ' . number_format($rcvtotal, 0, ',', '.') ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-6 mb-4 mr-0">
                                    <div class="card border-left-danger shadow h-100 py-2">
                                        <div class="card-body">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                        Total Overdue DN</div>
                                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= 'Rp. ' . number_format($ovdtotal, 0, ',', '.') ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6 col-md-6 mb-4 mr-0">
                                    <div class="card border-left-danger shadow h-100 py-2">
                                        <div class="card-body">
                                            <div class="row no-gutters align-items-center">
                                                <div class="col mr-2">
                                                    <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                        Total Overdue pass due 2 years</div>
                                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?= 'Rp. ' . number_format($ovdTwoYear, 0, ',', '.') ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="capture2" class="col-12">
                            <div class="card shadow mb-4">
                                <a href="#collapseReceivedOverdue" class="card-header py-3 d-flex flex-row align-items-center justify-content-between" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="collapseReceivedOverdue">
                                    <h6 class="m-0 font-weight-bold text-gray-800">Received &amp; Unreceived |
                                        <?php
                                        $cReceived = 0;
                                        if (!empty($rcvtotal)) {
                                            $cReceived = (float) ($rcvtotal / $total * 100);
                                            $cReceived = round($cReceived, 0);
                                        };

                                        echo $cReceived . '%';
                                        ?></h6>
                                </a>
                                <!-- Card Body -->
                                <div class="collapse multi-collapse show" id="collapseReceivedOverdue">
                                    <div class="card-body">
                                        <div class="chart-pie pt-4 pb-2 mb-5">
                                            <canvas id="overdueChart"></canvas>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div id="capture3" class="col-12">
                            <div class="card shadow mb-4">
                                <a href="#collapseAchievementPaymentReceived" class="card-header py-3 d-flex flex-row align-items-center justify-content-between" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="collapseAchievementPaymentReceived">
                                    <h6 class="m-0 font-weight-bold text-gray-800">Achievement Payment Received</h6>
                                </a>
                                <!-- Card Body -->
                                <div class="collapse multi-collapse show" id="collapseAchievementPaymentReceived">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-12 col-md-4">
                                                <div class="chart-pie pt-4 pb-2 mb-5">
                                                    <canvas id="sewaBangunanChart"></canvas>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <div class="chart-pie pt-4 pb-2 mb-5">
                                                    <canvas id="utilityChart"></canvas>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <div class="chart-pie pt-4 pb-2 mb-5">
                                                    <canvas id="othersChart"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div id="capture4" class="col-12">
                            <div class="card shadow mb-4" id="paymentReceived">
                                <!-- Card Header - Dropdown -->
                                <a href="#collapsePaymentReceived" class="card-header py-3 d-flex flex-row align-items-center justify-content-between" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="collapsePaymentReceived">
                                    <h6 class="m-0 font-weight-bold text-gray-800">Payment Received</h6>
                                </a>
                                <!-- Card Body -->
                                <div class="collapse multi-collapse show" id="collapsePaymentReceived">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-2">
                                                <select class="custom-select" id="pilYear">
                                                    <?php
                                                    foreach ($year_list as $items) {
                                                        echo '<option value="' . $items->YEAR_REYEACTIVE . '">' . $items->YEAR_REYEACTIVE . '</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="chart-area mb-5 p-5">
                                                    <div class="mb-5" id="paymentRecGraph">
                                                        <!-- Bar Graph Goes Here -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div><br><br>
                                        <hr class="invisible my-5 ">
                                        <div class="row">
                                            <div class="col-12">
                                                <h6 class="mb-0 mt-1 font-weight-bold">DN Payment</h6>
                                                <div class="table-responsive mt-1">
                                                    <table class="table table-bordered" id="tableTahunan" width="100%" cellspacing="0">
                                                        <thead>
                                                            <tr>
                                                                <th>Target</th>
                                                                <th>Listrik</th>
                                                                <th>Rent</th>
                                                                <th>Service</th>
                                                                <th>Air</th>
                                                                <th>Telefon</th>
                                                                <th>Others</th>
                                                                <th>Grand Total</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <h6 class="mb-0 mt-3 font-weight-bold">DN Received</h6>
                                                <div class="table-responsive mt-1">
                                                    <table class="table table-bordered" id="tableTahunanDetail" width="100%" cellspacing="0">
                                                        <thead>
                                                            <tr>
                                                                <th>Tahun</th>
                                                                <th>Listrik</th>
                                                                <th>Rent</th>
                                                                <th>Service</th>
                                                                <th>Air</th>
                                                                <th>Telefon</th>
                                                                <th>Others</th>
                                                                <th>Grand Total</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                        </tbody>
                                                        <tfoot>
                                                            <th>Total</th>
                                                            <th></th>
                                                            <th></th>
                                                            <th></th>
                                                            <th></th>
                                                            <th></th>
                                                            <th></th>
                                                            <th></th>
                                                        </tfoot>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">Detail Summary</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div id="capture5" class="col-12">
                            <div class="card shadow mb-4">
                                <!-- Card Header - Dropdown -->
                                <a href="#collapseMonthlyDetail" class="card-header py-3 d-flex flex-row align-items-center justify-content-between" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="collapseMonthlyDetail">
                                    <h6 class="m-0 font-weight-bold text-gray-800">Monthly Detail</h6>
                                </a>
                                <!-- Card Body -->
                                <div class="collapse multi-collapse show" id="collapseMonthlyDetail">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="chart-area mb-5 p-5">
                                                    <div class="mb-5" id="divGraph">
                                                        <!-- Bar Graph Goes Here -->
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <hr class="invisible my-5 ">
                                        <br><br>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="mt-5">
                                                    <table class="table table-bordered" id="tabelBulanan" width="100%" cellspacing="0">
                                                        <thead>
                                                            <tr>
                                                                <th></th>
                                                                <th>Jan</th>
                                                                <th>Feb</th>
                                                                <th>Mar</th>
                                                                <th>Apr</th>
                                                                <th>May</th>
                                                                <th>Jun</th>
                                                                <th>Juli</th>
                                                                <th>Aug</th>
                                                                <th>Sep</th>
                                                                <th>Oct</th>
                                                                <th>Nov</th>
                                                                <th>Des</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="row">
                        <div id="capture6" class="col-12">
                            <div class="card shadow mb-4">
                                <a href="#collapseDNAging" class="card-header py-3 d-flex flex-row align-items-center justify-content-between" data-toggle="collapse" role="button" aria-expanded="true" aria-controls="collapseDNAging">
                                    <h6 class="m-0 font-weight-bold text-gray-800">DN Aging and Top Tenants</h6>
                                </a>
                                <!-- Card Body -->
                                <div class="collapse multi-collapse show" id="collapseDNAging">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-8 mx-auto">
                                                <div class="chart-bar">
                                                    <canvas class="mb-5" id="dnAgingChart"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="col-8 mx-auto">
                                                <div class="chart-bar">
                                                    <canvas class="mb-5" id="topTenantChart"></canvas>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /.container-fluid -->

</div>
<!-- End of Main Content -->