<!-- Footer -->
<footer class="sticky-footer bg-white">
    <div class="container my-auto">
        <div class="copyright text-center my-auto">
            <span>Copyright &copy; United Tractors</span>
            <?php
            $cReceived = 0;
            $cOverdue = 0;
            if (!empty($rcvtotal)) {
                $cReceived = (float) ($rcvtotal / $total * 100);
                $cReceived = round($cReceived, 0);
            };
            if (!empty($ovdtotal)) {
                $cOverdue = (float) ($ovdtotal / $total * 100);
                $cOverdue = round($cOverdue, 0);
            };

            $cRentCharge = 0;
            $cRentOverdue = 0;
            if (!empty($rentCharge)) {
                $cRentCharge = (float) ($rentCharge / ($rentCharge + $rentOverdue) * 100);
                $cRentCharge = round($cRentCharge, 0);
            };
            if (!empty($rentOverdue)) {
                $cRentOverdue = (float) ($rentOverdue / ($rentCharge + $rentOverdue) * 100);
                $cRentOverdue = round($cRentOverdue, 0);
            };

            $cUtilCharge = 0;
            $cUtilOverdue = 0;
            if (!empty($utilCharge)) {
                $cUtilCharge = (float) ($utilCharge / ($utilCharge + $utilOverdue) * 100);
                $cUtilCharge = round($cUtilCharge, 0);
            };
            if (!empty($utilOverdue)) {
                $cUtilOverdue = (float) ($utilOverdue / ($utilCharge + $utilOverdue) * 100);
                $cUtilOverdue = round($cUtilOverdue, 0);
            };

            $cOthersCharge = 0;
            $cOthersOverdue = 0;
            if (!empty($othersCharge)) {
                $cOthersCharge = (float) ($othersCharge / ($othersCharge + $othersOverdue) * 100);
                $cOthersCharge = round($cOthersCharge, 0);
            };
            if (!empty($othersOverdue)) {
                $cOthersOverdue = (float) ($othersOverdue / ($othersCharge + $othersOverdue) * 100);
                $cOthersOverdue = round($cOthersOverdue, 0);
            };

            $topTenantsData = [];
            foreach ($topTenants as $items) {
                $topTenantsData[] = (int) $items->TOTAL;
            };
            $topTenantsData = json_encode($topTenantsData);

            $topTenantsLabel = [];
            foreach ($topTenants as $items) {
                $topTenantsLabel[] = $items->NAMAPERUSAHAAN_DEBITNOTE;
            };
            $topTenantsLabel = json_encode($topTenantsLabel);

            $cAgingEnam = 0;
            $cAgingTigaEnam = 0;
            $cAgingTiga = 0;
            if (!empty($agingEnam)) {
                $cAgingEnam = $agingEnam;
            };
            if (!empty($agingTigaEnam)) {
                $cAgingTigaEnam = $agingTigaEnam;
            };
            if (!empty($agingTiga)) {
                $cAgingTiga = $agingTiga;
            };
            ?>
        </div>
    </div>
</footer>
<!-- End of Footer -->

</div>
<!-- End of Content Wrapper -->

</div>
<!-- End of Page Wrapper -->

<!-- Scroll to Top Button-->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<!-- Logout Modal-->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
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
                <a class="btn btn-primary" href="<?= site_url('logout') ?>">Logout</a>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap core JavaScript-->
<script src="<?= base_url('assets/vendor/jquery/jquery.min.js'); ?>"></script>
<script src="<?= base_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js'); ?>"></script>

<!-- Core plugin JavaScript-->
<script src="<?= base_url('assets/vendor/jquery-easing/jquery.easing.min.js'); ?>"></script>

<!-- Custom scripts for all pages-->
<script src="<?= base_url('assets/js/sb-admin-2.min.js'); ?>"></script>

<!-- Page level custom scripts -->
<script src="<?= base_url('assets/js/demo/chart-area-demo.js'); ?>"></script>
<script src="<?= base_url('assets/js/demo/chart-pie-demo.js'); ?>"></script>

<!-- Page level plugins -->
<script src="<?= base_url('assets/vendor/datatables/jquery.dataTables.min.js'); ?>"></script>
<script src="<?= base_url('assets/vendor/datatables/dataTables.bootstrap4.min.js'); ?>"></script>

<!-- Page level custom scripts -->
<script src="<?= base_url('assets/js/demo/datatables.js'); ?>"></script>
<script src="<?= base_url('assets/js/app.js'); ?>"></script>
<script src="https://js.pusher.com/7.0/pusher.min.js"></script>
<script>
    // Enable pusher logging - don't include this in production
    Pusher.logToConsole = true;

    var pusher = new Pusher('29bc2e8a44dd757e9b8b', {
        cluster: 'ap1'
    });

    var channel = pusher.subscribe('my-channel');
    channel.bind('my-event', function(data) {
        xhr = $.ajax({
            method: 'POST',
            url: "<?= site_url('notif/debitnote') ?>",
            success: function(response) {
                $('.notifs').html(response);
            }
        })
    });
    $('.notifs').on('click', '.notifikasi', function(e) {
        console.log("Clicked");
    });

    var overdueChart = document.getElementById("overdueChart");
    var overdue = new Chart(overdueChart, {
        plugins: [ChartDataLabels],
        type: 'pie',
        data: {
            labels: ["Overdue", "Received"],
            datasets: [{
                data: [<?= $cOverdue ?>, <?= $cReceived ?>],
                backgroundColor: ['rgba(237, 42, 33, 1)', 'rgba(49, 176, 87, 1)'],
                datalabels: {
                    anchor: 'center'
                }
            }],
        },
        options: {
            plugins: {
                datalabels: {
                    backgroundColor: function(context) {
                        return context.dataset.backgroundColor;
                    },
                    borderColor: 'white',
                    borderRadius: 25,
                    borderWidth: 2,
                    color: 'white',
                    display: function(context) {
                        var dataset = context.dataset;
                        var count = dataset.data.length;
                        var value = dataset.data[context.dataIndex];
                        return value > count * 1.5;
                    },
                    font: {
                        weight: 'bold'
                    },
                    padding: 6,
                    formatter: (val) => {
                        return val + ' %';
                    }
                },
                tooltip: {
                    enabled: false
                }
            },

            // Core options
            aspectRatio: 3.5,
            cutoutPercentage: 32,
            layout: {
                padding: 10
            },
            elements: {
                line: {
                    fill: false
                },
                point: {
                    hoverRadius: 7,
                    radius: 5
                }
            },
        }
    });

    var sewaBangunanChart = document.getElementById("sewaBangunanChart");
    var sewaBangunan = new Chart(sewaBangunanChart, {
        plugins: [ChartDataLabels],
        type: 'pie',
        data: {
            labels: ["Not yet", "Done"],
            datasets: [{
                data: [<?= $cRentOverdue ?>, <?= $cRentCharge ?>],
                backgroundColor: ['rgba(229, 42, 51, 1)', 'rgba(49, 176, 87, 1)'],
                hoverBackgroundColor: ['rgba(229, 42, 51, 1)', 'rgba(49, 176, 87, 1)'],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }],
        },
        options: {
            plugins: {
                datalabels: {
                    backgroundColor: function(context) {
                        return context.dataset.backgroundColor;
                    },
                    borderColor: 'white',
                    borderRadius: 25,
                    borderWidth: 2,
                    color: 'white',
                    display: function(context) {
                        var dataset = context.dataset;
                        var count = dataset.data.length;
                        var value = dataset.data[context.dataIndex];
                        return value > count * 1.5;
                    },
                    font: {
                        weight: 'bold'
                    },
                    padding: 6,
                    formatter: (val) => {
                        return val + ' %';
                    }
                },
                title: {
                    display: true,
                    text: 'Sewa Bangunan'
                },
                tooltip: {
                    enabled: false
                }
            },

            // Core options
            aspectRatio: 1,
            cutoutPercentage: 32,
            layout: {
                padding: 10
            },
            elements: {
                line: {
                    fill: false
                },
                point: {
                    hoverRadius: 7,
                    radius: 5
                }
            },
        }
    });

    var utilityChart = document.getElementById("utilityChart");
    var utility = new Chart(utilityChart, {
        plugins: [ChartDataLabels],
        type: 'pie',
        data: {
            labels: ["Not yet", "Done"],
            datasets: [{
                data: [<?= $cUtilOverdue ?>, <?= $cUtilCharge ?>],
                backgroundColor: ['rgba(229, 42, 51, 1)', 'rgba(56, 139, 242, 1)'],
                hoverBackgroundColor: ['rgba(229, 42, 51, 1)', 'rgba(56, 139, 242, 1)'],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }],
        },
        options: {
            plugins: {
                datalabels: {
                    backgroundColor: function(context) {
                        return context.dataset.backgroundColor;
                    },
                    borderColor: 'white',
                    borderRadius: 25,
                    borderWidth: 2,
                    color: 'white',
                    display: function(context) {
                        var dataset = context.dataset;
                        var count = dataset.data.length;
                        var value = dataset.data[context.dataIndex];
                        return value > count * 1.5;
                    },
                    font: {
                        weight: 'bold'
                    },
                    padding: 6,
                    formatter: (val) => {
                        return val + ' %';
                    }
                },
                title: {
                    display: true,
                    text: 'Utility'
                },
                tooltip: {
                    enabled: false
                }
            },

            // Core options
            aspectRatio: 1,
            cutoutPercentage: 32,
            layout: {
                padding: 10
            },
            elements: {
                line: {
                    fill: false
                },
                point: {
                    hoverRadius: 7,
                    radius: 5
                }
            }
        }
    });

    var othersChart = document.getElementById("othersChart");
    var others = new Chart(othersChart, {
        plugins: [ChartDataLabels],
        type: 'pie',
        data: {
            labels: ["Not yet", "Done"],
            datasets: [{
                data: [<?= $cOthersOverdue ?>, <?= $cOthersCharge ?>],
                backgroundColor: ['rgba(229, 42, 51, 1)', 'rgba(252, 131, 56, 1)'],
                hoverBackgroundColor: ['rgba(229, 42, 51, 1)', 'rgba(252, 131, 56, 1)'],
                hoverBorderColor: "rgba(234, 236, 244, 1)",
            }],
        },
        options: {
            plugins: {
                datalabels: {
                    backgroundColor: function(context) {
                        return context.dataset.backgroundColor;
                    },
                    borderColor: 'white',
                    borderRadius: 25,
                    borderWidth: 2,
                    color: 'white',
                    display: function(context) {
                        var dataset = context.dataset;
                        var count = dataset.data.length;
                        var value = dataset.data[context.dataIndex];
                        return value > count * 1.5;
                    },
                    font: {
                        weight: 'bold'
                    },
                    padding: 6,
                    formatter: (val) => {
                        return val + ' %';
                    }
                },
                title: {
                    display: true,
                    text: 'Others'
                },
                tooltip: {
                    enabled: false
                }
            },

            // Core options
            aspectRatio: 1,
            cutoutPercentage: 32,
            layout: {
                padding: 10
            },
            elements: {
                line: {
                    fill: false
                },
                point: {
                    hoverRadius: 7,
                    radius: 5
                }
            },
        }
    });

    var topTenantChart = document.getElementById("topTenantChart");
    var topTenant = new Chart(topTenantChart, {
        type: 'bar',
        data: {
            labels: <?= $topTenantsLabel ?>,
            datasets: [{
                label: 'Total',
                data: <?= $topTenantsData ?>,
                backgroundColor: 'rgba(41, 128, 185,1.0)',
                borderColor: 'rgba(41, 128, 185,1.0)',
                borderWidth: 1
            }],
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            tooltips: {
                mode: 'label',
                label: 'mylabel',
                callbacks: {
                    label: function(tooltipItem, data) {
                        return tooltipItem.yLabel.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                    }
                }
            },
            scales: {
                yAxes: [{
                    ticks: {
                        callback: function(label, index, labels) {
                            return label.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                        },
                        beginAtZero: true,
                        fontSize: 12,
                    }
                }]
            },
            title: {
                display: true,
                text: 'Top Tenants'
            }
        }
    });

    var dnAgingChart = document.getElementById("dnAgingChart");
    var dnAging = new Chart(dnAgingChart, {
        type: 'bar',
        data: {
            labels: ["<30 Hari", "30-60 Hari", ">60 Hari"],
            datasets: [{
                label: 'Total',
                data: [<?= $cAgingTiga ?>, <?= $cAgingTigaEnam ?>, <?= $cAgingEnam ?>],
                backgroundColor: 'rgba(41, 128, 185,1.0)',
                borderColor: 'rgba(41, 128, 185,1.0)',
                borderWidth: 1
            }],
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                }
            },
            tooltips: {
                mode: 'label',
                label: 'mylabel',
                callbacks: {
                    label: function(tooltipItem, data) {
                        return tooltipItem.yLabel.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                    }
                }
            },
            scales: {
                yAxes: [{
                    ticks: {
                        callback: function(label, index, labels) {
                            return label.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
                        },
                        beginAtZero: true,
                        fontSize: 12,
                    }
                }]
            },
            title: {
                display: true,
                text: 'DN Aging'
            }
        }
    });

    $(document).ready(function() {
        $.ajax({
            url: "<?= site_url('debitnote/monthlyDNChart') ?>",
            type: "post",
            success: function(bar_graph) {
                $("#divGraph").html(bar_graph);
                var monGraphData = JSON.parse($("#graph").data("settings"));
                $("#graph").chart = new Chart($("#graph"), {
                    type: "bar",
                    data: monGraphData,
                    options: {
                        plugins: {
                            legend: {
                                display: true
                            }
                        }
                    }
                });
            }
        });

        $("#pilYear").change(function() {
            $.ajax({
                url: "<?= site_url('debitnote/monthlyDNChart') ?>",
                type: "post",
                data: {
                    year: $(this).val()
                },
                success: function(bar_graph) {
                    $("#divGraph").html(bar_graph);
                    var monGraphData = JSON.parse($("#graph").data("settings"));
                    $("#graph").chart = new Chart($("#graph"), {
                        type: "bar",
                        data: monGraphData,
                        options: {
                            plugins: {
                                legend: {
                                    display: true
                                }
                            }
                        }
                    });
                }
            });
        });

        $.ajax({
            url: "<?= site_url('debitnote/paymentDNChart') ?>",
            type: "post",
            success: function(pay_graph) {
                $("#paymentRecGraph").html(pay_graph);
                var graphData = JSON.parse($("#payGraph").data("settings"));
                $("#payGraph").chart = new Chart($("#payGraph"), {
                    type: "bar",
                    data: graphData,
                    options: {
                        plugins: {
                            legend: {
                                labels: {
                                    filter: function(item, chart) {
                                        return !item.text.includes('Listrik Belum Terbayar') && !item.text.includes('Rent Belum Terbayar') && !item.text.includes('Service Belum Terbayar') && !item.text.includes('Air Belum Terbayar') && !item.text.includes('Telepon Belum Terbayar') && !item.text.includes('Others Belum Terbayar');
                                    }
                                }
                            },
                            tooltip: {
                                mode: 'x'
                            }
                        }
                    }
                });
            }
        });

        $("#pilYear").change(function() {
            $.ajax({
                url: "<?= site_url('debitnote/paymentDNChart') ?>",
                type: "post",
                data: {
                    year: $(this).val()
                },
                success: function(pay_graph) {
                    $("#paymentRecGraph").html(pay_graph);
                    var graphData = JSON.parse($("#payGraph").data("settings"));
                    $("#payGraph").chart = new Chart($("#payGraph"), {
                        type: "bar",
                        data: graphData,
                        options: {
                            plugins: {
                                legend: {
                                    labels: {
                                        filter: function(item, chart) {
                                            return !item.text.includes('Listrik Belum Terbayar') && !item.text.includes('Rent Belum Terbayar') && !item.text.includes('Service Belum Terbayar') && !item.text.includes('Air Belum Terbayar') && !item.text.includes('Telepon Belum Terbayar') && !item.text.includes('Others Belum Terbayar');
                                        }
                                    }
                                },
                                tooltip: {
                                    mode: 'x'
                                }
                            }
                        }
                    });
                }
            });
        });
        function numberWithPoints(x) {
            return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        var table;

        table = $('#tabelBulanan').DataTable({
            "ajax": {
                "url": '<?= site_url('debitnote/monthlyTable') ?>'
            },
            "aoColumns": [{
                    sWidth: '7%'
                },
                {
                    sWidth: '7%'
                },
                {
                    sWidth: '7%'
                },
                {
                    sWidth: '7%'
                },
                {
                    sWidth: '7%'
                },
                {
                    sWidth: '7%'
                },
                {
                    sWidth: '7%'
                },
                {
                    sWidth: '7%'
                },
                {
                    sWidth: '7%'
                },
                {
                    sWidth: '7%'
                },
                {
                    sWidth: '7%'
                },
                {
                    sWidth: '7%'
                },
                {
                    sWidth: '7%'
                }
            ],
            "sScrollX": "100%",
            "sScrollXInner": "210%",
            "searching": false,
            "info": false,
            "bPaginate": false
        });

        var tableYearly
        tableYearly =  $('#tableTahunan').DataTable({
            "ajax": {
                "url": '<?= site_url('debitnote/yearlyTable') ?>'
            },
            "bPaginate": false,
            "info": false,
            "searching": false
        })

        var tableYearlyDetail 
        tableYearlyDetail = $('#tableTahunanDetail').DataTable({
            "ajax": {
                "url": '<?= site_url('debitnote/yearlyDetailTable') ?>'
            },
            "bPaginate": false,
            "info": false,
            "searching": false,
            "footerCallback": function( tfoot, data, start, end, display ) {
                var api = this.api();

                $( api.column( 1 ).footer() ).html(
                    api.column( 1 ).data().reduce( function ( a, b ) {
                        const valA  = a.toString().replace('Rp. ', '').replace('.', '').replace('.', '');
                        const valB  = b.toString().replace('Rp. ', '').replace('.', '').replace('.', '');
                        const total = parseInt(valA) + parseInt(valB)
                        return `Rp. ${numberWithPoints(total)}` ;
                    }, 0 )
                );
                $( api.column( 2 ).footer() ).html(
                    api.column( 2 ).data().reduce( function ( a, b ) {
                        const valA  = a.toString().replace('Rp. ', '').replace('.', '').replace('.', '');
                        const valB  = b.toString().replace('Rp. ', '').replace('.', '').replace('.', '');
                        const total = parseInt(valA) + parseInt(valB)
                        return `Rp. ${numberWithPoints(total)}` ;
                    }, 0 )
                );
                $( api.column( 3 ).footer() ).html(
                    api.column( 3 ).data().reduce( function ( a, b ) {
                        const valA  = a.toString().replace('Rp. ', '').replace('.', '').replace('.', '');
                        const valB  = b.toString().replace('Rp. ', '').replace('.', '').replace('.', '');
                        const total = parseInt(valA) + parseInt(valB)
                        return `Rp. ${numberWithPoints(total)}` ;
                    }, 0 )
                );
                $( api.column( 4 ).footer() ).html(
                    api.column( 4 ).data().reduce( function ( a, b ) {
                        const valA  = a.toString().replace('Rp. ', '').replace('.', '').replace('.', '');
                        const valB  = b.toString().replace('Rp. ', '').replace('.', '').replace('.', '');
                        const total = parseInt(valA) + parseInt(valB)
                        return `Rp. ${numberWithPoints(total)}` ;
                    }, 0 )
                );
                $( api.column( 5 ).footer() ).html(
                    api.column( 5 ).data().reduce( function ( a, b ) {
                        const valA  = a.toString().replace('Rp. ', '').replace('.', '').replace('.', '');
                        const valB  = b.toString().replace('Rp. ', '').replace('.', '').replace('.', '');
                        const total = parseInt(valA) + parseInt(valB)
                        return `Rp. ${numberWithPoints(total)}` ;
                    }, 0 )
                );
                $( api.column( 6 ).footer() ).html(
                    api.column( 6 ).data().reduce( function ( a, b ) {
                        const valA  = a.toString().replace('Rp. ', '').replace('.', '').replace('.', '');
                        const valB  = b.toString().replace('Rp. ', '').replace('.', '').replace('.', '');
                        const total = parseInt(valA) + parseInt(valB)
                        return `Rp. ${numberWithPoints(total)}` ;
                    }, 0 )
                );
                $( api.column( 7 ).footer() ).html(
                    api.column( 7 ).data().reduce( function ( a, b ) {
                        const valA  = a.toString().replace('Rp. ', '').replace('.', '').replace('.', '');
                        const valB  = b.toString().replace('Rp. ', '').replace('.', '').replace('.', '');
                        const total = parseInt(valA) + parseInt(valB)
                        return `Rp. ${numberWithPoints(total)}` ;
                    }, 0 )
                );
            }
        })

        $("#pilYear").change(function() {
            $('#yearReport').val($(this).val())
            table.destroy();
            tableYearly.destroy()
            tableYearlyDetail.destroy()
            table = $('#tabelBulanan').DataTable({
                "ajax": {
                    "url": '<?= site_url('debitnote/monthlyTable') ?>',
                    "type": "POST",
                    "data": {
                        year: $(this).val()
                    }
                },
                "aoColumns": [{
                        sWidth: '7%'
                    },
                    {
                        sWidth: '7%'
                    },
                    {
                        sWidth: '7%'
                    },
                    {
                        sWidth: '7%'
                    },
                    {
                        sWidth: '7%'
                    },
                    {
                        sWidth: '7%'
                    },
                    {
                        sWidth: '7%'
                    },
                    {
                        sWidth: '7%'
                    },
                    {
                        sWidth: '7%'
                    },
                    {
                        sWidth: '7%'
                    },
                    {
                        sWidth: '7%'
                    },
                    {
                        sWidth: '7%'
                    },
                    {
                        sWidth: '7%'
                    }
                ],
                "sScrollX": "100%",
                "sScrollXInner": "210%",
                "bPaginate": false,
                "info": false,
                "searching": false
            });

            tableYearly =  $('#tableTahunan').DataTable({
                "ajax": {
                    "url": '<?= site_url('debitnote/yearlyTable') ?>',
                    "type": "POST",
                    "data": {
                        year: $(this).val()
                    }
                },
                "bPaginate": false,
                "info": false,
                "searching": false
            })
            
            tableYearlyDetail = $('#tableTahunanDetail').DataTable({
                "ajax": {
                    "url": '<?= site_url('debitnote/yearlyDetailTable') ?>',
                    "type": "POST",
                    "data": {
                        year: $(this).val()
                    }
                },
                "bPaginate": false,
                "info": false,
                "searching": false,
                "footerCallback": function( tfoot, data, start, end, display ) {
                    var api = this.api();

                    $( api.column( 1 ).footer() ).html(
                        api.column( 1 ).data().reduce( function ( a, b ) {
                            const valA  = a.toString().replace('Rp. ', '').replace('.', '').replace('.', '');
                            const valB  = b.toString().replace('Rp. ', '').replace('.', '').replace('.', '');
                            const total = parseInt(valA) + parseInt(valB)

                            
                            return `Rp. ${numberWithPoints(total)}` ;
                        }, 0 )
                    );
                    $( api.column( 2 ).footer() ).html(
                        api.column( 2 ).data().reduce( function ( a, b ) {
                            const valA  = a.toString().replace('Rp. ', '').replace('.', '').replace('.', '');
                            const valB  = b.toString().replace('Rp. ', '').replace('.', '').replace('.', '');
                            const total = parseInt(valA) + parseInt(valB)
                            return `Rp. ${numberWithPoints(total)}` ;
                        }, 0 )
                    );
                    $( api.column( 3 ).footer() ).html(
                        api.column( 3 ).data().reduce( function ( a, b ) {
                            const valA  = a.toString().replace('Rp. ', '').replace('.', '').replace('.', '');
                            const valB  = b.toString().replace('Rp. ', '').replace('.', '').replace('.', '');
                            const total = parseInt(valA) + parseInt(valB)
                            return `Rp. ${numberWithPoints(total)}` ;
                        }, 0 )
                    );
                    $( api.column( 4 ).footer() ).html(
                        api.column( 4 ).data().reduce( function ( a, b ) {
                            const valA  = a.toString().replace('Rp. ', '').replace('.', '').replace('.', '');
                            const valB  = b.toString().replace('Rp. ', '').replace('.', '').replace('.', '');
                            const total = parseInt(valA) + parseInt(valB)
                            return `Rp. ${numberWithPoints(total)}` ;
                        }, 0 )
                    );
                    $( api.column( 5 ).footer() ).html(
                        api.column( 5 ).data().reduce( function ( a, b ) {
                            const valA  = a.toString().replace('Rp. ', '').replace('.', '').replace('.', '');
                            const valB  = b.toString().replace('Rp. ', '').replace('.', '').replace('.', '');
                            const total = parseInt(valA) + parseInt(valB)
                            return `Rp. ${numberWithPoints(total)}` ;
                        }, 0 )
                    );
                    $( api.column( 6 ).footer() ).html(
                        api.column( 6 ).data().reduce( function ( a, b ) {
                            const valA  = a.toString().replace('Rp. ', '').replace('.', '').replace('.', '');
                            const valB  = b.toString().replace('Rp. ', '').replace('.', '').replace('.', '');
                            const total = parseInt(valA) + parseInt(valB)
                            return `Rp. ${numberWithPoints(total)}` ;
                        }, 0 )
                    );
                    $( api.column( 7 ).footer() ).html(
                        api.column( 7 ).data().reduce( function ( a, b ) {
                            const valA  = a.toString().replace('Rp. ', '').replace('.', '').replace('.', '');
                            const valB  = b.toString().replace('Rp. ', '').replace('.', '').replace('.', '');
                            const total = parseInt(valA) + parseInt(valB)
                            return `Rp. ${numberWithPoints(total)}` ;
                        }, 0 )
                    );
                }
            })
            reload_table();
        });

        function reload_table() {
            table.ajax.reload(null, false);
        }

        $("#btnCollapse").click(function() {
            if ($(this).hasClass("collapsed"))
                $(this).text("Hide All");
            else
                $(this).text("Show All");
        });

        // export pdf
        // $('#printPdf').click(() => {
        //     // printDiv()
        //     javascript:window.print();
        // })
        // function printDiv() {
        //     var divToPrint = $('#paymentReceived');
        //     var newWin=window.open('','Print-Window');

        //     newWin.document.open();
        //     newWin.document.write(`
        //         <html>
        //             <body onload="window.print()">
        //                 ${divToPrint.html()}

                        
        //             </body>
                    
        //         </html>
        //     `);
        //     newWin.document.close();
        //     setTimeout(function(){newWin.close();},10);
        // }
    });
</script>
</body>

</html>