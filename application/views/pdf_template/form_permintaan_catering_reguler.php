<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FORM PERMINTAAN CATERING REGULER</title>

    <style>
        html,
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
        }

        .w-100 {
            min-width: 100%;
        }

        .w-50 {
            min-width: 50%;
            max-width: 50%;
        }

        .table-center {
            margin-right: auto;
            margin-left: auto;
        }

        .table-layout-fixed {
            table-layout: fixed;
        }

        .p-min {
            padding: .2em .3em;
        }

        .p-med {
            padding: .8em .3em;
        }

        .pl-min {
            padding-left: .3em;
        }

        .pl-med {
            padding-left: .6em;
        }

        .pt-min {
            padding-top: .5em;
        }

        .pb-min {
            padding-bottom: .4em;
        }

        .valign-middle {
            vertical-align: middle;
        }

        .p-max {
            padding: 2em;
        }

        .mb-max {
            margin-bottom: 3em;
        }

        .mt-max {
            margin-top: 3em;
        }

        .mb-min {
            margin-bottom: .7em;
        }

        .border-collapse {
            border-collapse: collapse;
        }

        .border-1 {
            border: 1px solid black;
        }

        .border-2 {
            border: 2px solid black;
        }

        .border-right {
            border-right: 1px solid black;
        }

        .border-left-bold {
            border-left: 3px solid black;
        }

        .border-bottom {
            border-bottom: 1px solid black;
        }

        .text-title {
            font-size: 24px;
        }

        .text-regular {
            font-size: 14px;
        }

        .text-regular-sm {
            font-size: 12px;
        }

        .text-regular-xs {
            font-size: 10px;
        }

        .font-weight-regular {
            font-weight: 400;
        }

        .font-weight-bold {
            font-weight: 800;
        }

        .text-align-left {
            text-align: left;
        }

        .text-align-right {
            text-align: right;
        }

        .text-align-center {
            text-align: center;
        }

        .text-align-justify {
            text-align: justify;
        }

        .text-vertical {
            writing-mode: vertical-rl;
        }

        .watermark::before {
            position: absolute;
            top: 35%;
            left: 10%;
            content: "General Affairs Department";
            z-index: 99;
            font-size: 80px;
            transform: rotate(-30deg);
            color: rgba(0, 0, 0, .15);
            text-align: center;
            line-height: 1.8em;
        }

        .wrapper-page {
            page-break-after: always;
        }

        .wrapper-page:last-child {
            page-break-after: avoid;
        }

        .bg-grey {
            background-color: #C0C0C0;
        }

        .bg-soft-blue {
            background-color: #CCFFFF;
        }

        .absolute {
            position: absolute;
        }

        .pos-right {
            right: 0;
        }

        .pos-right::after {
            clear: both;
        }

        .pos-left {
            left: 0;
        }
    </style>

</head>

<body>
    <div class="wrapper-page">
        <div class="watermark"></div>
        <table class="border-2 border-collapse w-100 valign-middle mb-min">
            <tr>
                <td class="text-regular-sm font-family-arial text-align-center p-med border-right">
                    <img class="valign-middle" src="data:image/png;base64,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" height="25px" width="25px">
                    &nbsp;<strong class="valign-middle">UNITED TRACTORS</strong>
                </td>
                <td class="text-align-center border-1" colspan="2"><strong><?= $title_pdf?></strong></td>
                <td class="text-align-center border-bottom"><strong>INTEGRASI SISTEM</strong></td>
            </tr>
            <tr>
                <td class="text-regular-sm text-align-center border-right" rowspan="3">Jl. Raya Bekasi KM 22 Cakung <br> Jakarta Timur</td>
                <td class="text-regular-sm pl-min pt-min" style="width: 15%;">
                    <strong>Nomor Dokumen</strong>
                </td>
                <td class="text-regular-sm border-right pt-min" style="width: 30%;">: <?= $noDoc?></td>
                <td class="text-regular-sm text-align-center pt-min pb-min" rowspan="3">
                    <strong>
                        ISO 9001 : 2008 ; 1SO <br>
                        14001 : 2004 ; OHSAS <br>
                        18001 : 2007 &amp; SMK3
                    </strong>
                </td>
            </tr>
            <tr>
                <td class="text-regular-sm pl-min" style="width: 15%;">
                    <strong>No. Revisi</strong>
                </td>
                <td class="text-regular-sm border-right">: 0</td>
            </tr>
            <tr>
                <td class="text-regular-sm pl-min pb-min" style="width: 15%;">
                    <strong>Hal</strong>
                </td>
                <td class="text-regular-sm pb-min border-right">: ... Dari ...</td>
            </tr>
        </table>
        <p class="text-align-center mb-min">
            <strong>FORM PERMINTAAN CATERING REGULER</strong>
        </p>
        <table class="mb-min">
            <tr>
                <td><strong>Dept / Divisi</strong></td>
                <td>: General Affairs</td>
            </tr>
        </table>
        <table class="border-1 border-collapse valign-middle text-align-center w-100 table-layout-fixed mb-min">
            <tr>
                <th class="border-1 p-med" width="5%">No</th>
                <th class="border-1 p-med" width="35%">Tanggal</th>
                <th class="border-1 p-med">Jumlah Orang</th>
            </tr>
            <?php
                $no = 1;
                foreach ($list as $item) {
                    $date = date_create($item->TGL_REGULER);
                    $date = date_format($date, 'j').' '.$getMonth[date_format($date, 'n')].' '.date_format($date, 'Y');
                    echo '
                        <tr>
                            <td class="border-1 p-med">'.$no.'</td>
                            <td class="border-1 p-med">'.$date.'</td>
                            <td class="border-1 p-med">'.$item->JML_ORANG.' Orang</td>
                        </tr>
                    ';
                    $no++;
                }
            ?>
        </table>
        <table class="border-1 border-collapse absolute pos-right table-layout-fixed" style="width: 600px;">
            <tr>
                <th class="text-align-center border-1" width="33.33%">Staff / User</th>
                <th class="text-align-center border-1" width="33.33%">Section Head</th>
                <th class="text-align-center border-1" width="33.33%">Dept Head</th>
            </tr>
            <tr>
                <td class="text-align-center border-1 p-min"><img src="<?= $user->PATH_TTD ?>" width="100px" height="100px" /></td>
                <td class="text-align-center border-1 p-min">
                    <?php
                        if ($approvals[0]->ROLE_APP == "Section Head" && $approvals[0]->ISAPPROVE_APP == "1") {
                            echo '
                                        <img src="' . $approvals[0]->PATH_TTD . '" width="100px" height="100px" />
                                    ';
                        }
					?>
                </td>
                <td class="text-align-center border-1 p-min">
                    <?php
                        if ($approvals[1]->ROLE_APP == "Department Head" && $approvals[1]->ISAPPROVE_APP == "1") {
                            echo '
                                        <img src="' . $approvals[1]->PATH_TTD . '" width="100px" height="100px" />
                                    ';
                        }
					?>
                </td>
            </tr>
            <tr>
                <td class="text-align-center border-1">( <?= $user->NAMA_USERS?> )</td>
                <?php
					if ($approvals[0]->ROLE_APP == "Section Head" && $approvals[0]->ISAPPROVE_APP == "1") {
						echo '
                            <td class="text-align-center border-1">( ' . $approvals[0]->NAMA_USERS . ' )</td>
                        ';
					} else {
						echo '
                            <td class="text-align-center border-1"></td>
                        ';
					}

					if ($approvals[1]->ROLE_APP == "Department Head" && $approvals[1]->ISAPPROVE_APP == "1") {
						echo '
                            <td class="text-align-center border-1">( ' . $approvals[1]->NAMA_USERS . ' )</td>
                        ';
					} else {
						echo '
                            <td class="text-align-center border-1"></td>
                        ';
					}
				?>
            </tr>
        </table>
        <p style="margin-top: 180px;">
            * Mohon agar pemesanan dapat dilakukan setiap hari Jumat
        </p>
    </div>
</body>

</html>