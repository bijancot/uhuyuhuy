<body>
<div class="container-fluid">
<div class="wrapper-page">
    <table class="border-collapse w-100 valign-middle mb-med table-layout-fixed border-2">
    <tr>
    <td style="padding: 10px 30px 10px">
        <br>
        <table class="border-collapse w-100 valign-middle mb-med table-layout-fixed">
				<tr>
					<th>
						<img class="valign-middle" src="<?= base_url('assets/img/tenant/logo.png'); ?>" height="80px">
					</th>
                    <th width="100%">
                    </th>
					<th>
						<img class="valign-middle" src="<?= base_url('assets/img/tenant/logo-utbm.png'); ?>" height="100px">
					</th>
				</tr>
        </table>

    <div class="text-dark" style="text-align:center;font-size:40px;font-color:black;">
		<strong>DEBIT NOTE MONITORING DISPLAY FOR <BR> TENANT</strong>
	</div>
    <br>
    <table class="text-dark">
            <tr>
                <td width="12%" style="font-size:20px;">PERUSAHAAN</td>
                <td width="2%" style="font-size:20px;">:</td>
                <td width="86%" style="font-size:20px;"><?= $company->NAMAPERUSAHAAN_DEBITNOTE ?></td>
            </tr>
            <tr>
                <td style="font-size:20px;">STATUS</td>
                <td style="font-size:20px;">:</td>
                <td style="font-size:20px;"> 
                    <div class="dropdown">
                        <button class="btn btn-warning dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <?php echo ($stat == '1' ? 'Outstanding Payment' : 'Finished Payment') ?>
                        </button>
                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <a href="<?= site_url('tenant/'.$company->ID_TENANT.'/1') ?>" class="dropdown-item" value="1">Outstanding Payment</a>
                            <a href="<?= site_url('tenant/'.$company->ID_TENANT.'/2') ?>" class="dropdown-item" value="2">Finished Payment</a>
                        </div>
                    </div>
                </td>
            </tr>
    </table>

    <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="tableDisplayfortenant" data-toggle="tableDisplayfortenant" width="100%" cellspacing="0">
                    <thead>
                        <tr class="bg-warning text-light">
                            <th>Tipe DN</th>
                            <th>Tahun DN</th>
                            <th>No. DN</th>
                            <th>Tanggal DN</th>
                            <th>Tanggal Jatuh Tempo</th>
                            <th>AR Aging</th>
                            <th>Kategori AR Aging</th>
                            <th>No. Faktur Pajak</th>
                            <th>Tanggal DN</th>
                            <th>Mata Uang</th>
                            <th>Nama Perusahaan</th>
                            <th>Barang/Jasa Kena Pajak</th>
                            <th>Grand Total</th>
                        </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($debitnotes as $item) {
                                $tglNow         = date_create(date('Y-m-d'));
                                $tglPesanan     = date_create($item->TGLPESANAN_DEBITNOTE);
                                $tglFaktur      = date_create($item->TGLFAKTUR_DEBITNOTE);
                                $tglJatuh       = date_create($item->TGLJATUH_DEBITNOTE);
                                $tglPublished   = date_create($item->TGLPUBLISHED_DEBITNOTE);

                                $tglSisa = date_diff($tglNow, $tglPublished);

                                if($tglSisa->format('%a') < 30){
                                    $kategoriAr = '<30 hari';
                                }else if($tglSisa->format('%a') < 60){
                                    $kategoriAr = '30 - 60 hari';
                                }else{
                                    $kategoriAr = '>60 hari';
                                }

                                echo '
                                    <tr>
                                        <td>'.$item->TIPE_DEBITNOTE.'</td>
                                        <td>'.date_format($tglFaktur, 'Y').'</td>
                                        <td>'.$item->NOFAKTUR_DEBITNOTE.'</td>
                                        <td>'.date_format($tglFaktur, 'd M Y').'</td>
                                        <td>'.date_format($tglJatuh, 'd M Y').'</td>
                                        <td>'.$tglSisa->format('%a').'</td>
                                        <td>'.$kategoriAr.'</td>
                                        <td>'.$item->NOFAKTURPAJAK_DEBITNOTE.'</td>
                                        <td>'.date_format($tglPesanan, 'd M Y').'</td>
                                        <td>'.$item->MATAUANG.'</td>
                                        <td>'.$item->NAMAPERUSAHAAN_DEBITNOTE.'</td>
                                        <td>'.$item->BARANGJASA_DEBITNOTE.'</td>
                                        <td>'.number_format($item->GRANDTOTAL_DEBITNOTE).'</td>
                                    </tr>
                                ';
                                }
                            ?>
                        </tbody>
                </table>
            </div>
    </div>

    <br>

    <p class="text-dark" style="font-size:20px;">Shall you have any question or further information, please contact us at +62 21 24579999 ext. 16053 or by email to <br> <u>admgeneralaffairs@unitedtractors.com</u>.</p>
    <p class="text-dark" style="font-size:20px;">Thank you for your kind attention and cooperation.</p>
    <p class="text-dark" style="font-size:20px;">Sincerely,</p>
    <p class="text-dark" style="font-size:24px;"><strong>PT United Tractors Tbk</strong></p>
    </td>
    </tr>
    <table>
</div>
<script>
    $('#filterStatus').change( function(){
        const basePage = '<?= site_url('tenant/')?>'
        const idTenant = '<?= $company->ID_TENANT?>'
        const stat     = $('#filterStatus').val()

        window.location.replace(`${basePage}/${idTenant}/${stat}`)
    });
    
    $(document).ready(function() {
        $('#tableDisplayfortenant').dataTable({
            pageLength : 5,
            lengthMenu: [[5, 10, 20, -1], [5, 10, 20, 'All']]
        });
    } );
</script>
</div>
</body>
</html> 