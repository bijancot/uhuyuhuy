<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>FORM DEKLARASI</title>
	<style>
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
		}

		.tg {
			border-style: solid;
			border-width: 2px;
			border-collapse: collapse;
			border-spacing: 0;
		}

		.tg td {
			font-family: Arial, sans-serif;
			font-size: 14px;
			overflow: hidden;
			word-break: normal;
		}

		.tg th {
			border-color: black;
			border-style: solid;
			border-width: 2px;
			font-family: Arial, sans-serif;
			font-size: 14px;
			font-weight: normal;
			overflow: hidden;
			padding: 10px 5px;
			word-break: normal;
		}

		.tg .tg-w2dt {
			font-size: 12px;
			text-align: center;
			vertical-align: middle;
			border-color: black;
			border-style: solid;
			border-width: 2px;
			width: 28%;
		}

		.tg .tg-z9od {
			font-size: 12px;
			text-align: left;
			vertical-align: center;
			padding-left: 5px;
			padding-top: 5px
		}

		.tg .tg-z9ad {
			font-size: 12px;
			text-align: left;
			vertical-align: center;
			padding-left: 5px
		}

		.tg .tg-z9cd {
			font-size: 12px;
			text-align: left;
			vertical-align: center;
			padding-left: 5px;
			padding-bottom: 5px
		}

		.tg .tg-xsvg {
			font-size: 12px;
			font-weight: bold;
			text-align: justify;
			padding-left: 25px;
			border-color: black;
			border-style: solid;
			border-width: 2px;
		}

		.tg .tg-ir4y {
			font-size: 12px;
			font-weight: bold;
			text-align: center;
		}

		.tg .tg-k27y {
			font-size: 12px;
			font-weight: bold;
			text-align: center;
			vertical-align: middle
		}

		.tp {
			border: 1px solid black;
			;
			border-collapse: collapse;
			width: 100%;
			text-align: left;
			font-size: 13px;
		}

		.tp th {
			border: 1px solid black;
			padding: 3px;
			border-collapse: collapse;
			width: 100%;
			text-align: left;
		}

		.tp td {
			padding: 3px;
			border: 1px solid black;
			border-collapse: collapse;
			width: 100%;
			text-align: left;
		}

		.tj {
			border: 1px solid black;
			border-collapse: collapse;
			width: 100%;
			text-align: center;
			font-size: 14px;
		}

		.tj th {
			border: 1px solid black;
			border-collapse: collapse;
			text-align: center;
		}

		.tj td {
			border: 1px solid black;
			border-collapse: collapse;
			text-align: center;
		}

		.thd {
			border: 0px solid black;
			border-collapse: collapse;
			table-layout: fixed;
			text-align: center;
			width: 100%;
			font-size: 12px;
		}

		.thd-tha {
			border: 0px solid black;
			border-collapse: collapse;
		}

		.thd-td2 {
			border-right: 0px solid black;
		}

		.tg .tg-5r9a {
			border: 1px solid black;
			padding-top: 4px;
			padding-bottom: 4px;
			padding-left: 6px;
			font-size: 12px;
			text-align: left;
			vertical-align: top
		}

		.tg .tg-5rbv {
			border: 1px solid black;
			font-size: 12px;
			border-color: black;
			font-weight: bold;
			text-align: center;
			vertical-align: middle
		}

		h1,
		h2,
		h3,
		h4,
		h5,
		h6,
		.h1,
		.h2,
		.h3,
		.h4,
		.h5,
		.h6 {
			margin-top: 2px;
			margin-bottom: 2px;
		}
	</style>
</head>

<body style="padding-left:0.5cm; font-family:Arial, sans-serif;">
	<div class="watermark"></div>
	<div style="text-align:left">
		<h2><b>PT UNITED TRACTORS</b></h2>
	</div>
	<div style="text-align:left">
		<h5>GENERAL AFFAIRS DEPARTEMENT</h5>
	</div>
	<div>
		<table style="width:80%; text-align:left; font-size: 12px;">
			<tr>
				<td style="width: 20%">TANGGAL</td>
				<td style="width: 5%">:</td>
				<td>
					<?php
					$date 	= date_create($list[0]->TGLOUT_DEKLARASI);
					echo date_format($date, 'j') . ' ' . $getMonth[date_format($date, 'n')] . ' ' . date_format($date, 'Y');
					?>
				</td>
			</tr>
			<tr>
				<td>DIV / DEPT</td>
				<td>:</td>
				<td>
					<?= $list[0]->DD_DEKLARASI ?>
				</td>
			</tr>
			<tr>
				<td>NO POLISI</td>
				<td>:</td>
				<td>
					<?= $list[0]->NOPOL_DEKLARASI ?>
				</td>
			</tr>
		</table>
	</div>
	<div style="text-align:left">
		<h4>KEPERLUAN DEKLARASI :</h4>
	</div>
	<div>
		<table class="tg" width="100%" style="border-color: black;">
			<thead>
				<tr>
					<th class="tg-5rbv" width="5%">No</th>
					<th class="tg-5rbv" style="text-align:center;">BBM</th>
					<th class="tg-5rbv" style="text-align:center;">TOL / PARKIR</th>
					<th class="tg-5rbv" style="text-align:center;">GRAB</th>
					<th class="tg-5rbv" style="text-align:center;">LAIN-LAIN</th>
					<th class="tg-5rbv" style="text-align:center;">JUMLAH</th>
				</tr>
			</thead>
			<tbody>
				<?php
				$no = 1;
				foreach ($list as $item) {
					echo '<tr>
							<td class="tg-5r9a" style="text-align:center;">' . $no . '</td>
							<td class="tg-5r9a">' . $item->BBM_DEKLARASI . '</td>
							<td class="tg-5r9a">' . $item->TOL_DEKLARASI . '</td>
							<td class="tg-5r9a">' . $item->GRAB_DEKLARASI . '</td>
							<td class="tg-5r9a">' . $item->LAIN_DEKLARASI . '</td>
							<td class="tg-5r9a" style="text-align:center;">' . $item->JML_DEKLARASI . '</td>
						</tr>';
					$no++;
				}
				?>
			</tbody>
		</table>
	</div>
	<br>
	<div>
		<table style="width:100%; text-align:left; font-size: 12px;">
			<tr>
				<td style="width: 5%"><i><b>NOTE</b></i></td>
				<td style="width: 1%"><i><b>:</b></i></td>
				<td style="width: 100%">
					<i><b>UNTUK SETIAP PENUKARAN / PENYERAHAN BUKTI PEMAKAIAN BBM / TOL / PARKIR / GRAB DLL</b></i>
				</td>
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td>
					<i><b>PALING LAMBAT 3 MINGGU TERHITUNG MULAI TANGGAL PEMAKAIAN HARUS DI SERAHKAN</b></i>
				</td>
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td>
					<i><b>KEPADA GAD / SERVICES. BILA MANA PENYERAHAN LEWAT DARI 3 MINGGU KAMI NYATAKAN</b></i>
				</td>
			</tr>
			<tr>
				<td></td>
				<td></td>
				<td>
					<i><b>TIDAK BERLAKU / KAMI TOLAK.</b></i>
				</td>
			</tr>
		</table>
	</div>
	<br>
	<br>
	<div>
		<table class="thd">
			<tr>
				<td class="thd-tha">Diketahui Oleh : </td>
				<td class="thd-tha">Diketahui Oleh : </td>
				<td class="thd-tha">Disetujui Oleh : </td>
			</tr>
			<tr style="height:300px; min-height:300px;">
				<td class="thd-td2">
					<img src="<?= $user->PATH_TTD ?>" width="100px" height="100px" />
				</td>
				<td class="thd-td2">
					<?php
					if ($approvals[0]->ROLE_APP == "Section Head" && $approvals[0]->ISAPPROVE_APP == "1") {
						echo '
									<img src="' . $approvals[0]->PATH_TTD . '" width="100px" height="100px" />
								';
					}
					?>
				</td>
				<td class="thd-td2">
					<?php
					if ($approvals[1]->ROLE_APP == "Department Head" && $approvals[0]->ISAPPROVE_APP == "1") {
						echo '
									<img src="' . $approvals[1]->PATH_TTD . '" width="100px" height="100px" />
								';
					}
					?>
				</td>
			</tr>
			<tr>
				<td class="thd-tha"><b>PIC Admin</b></td>
				<td class="thd-tha"><b>Section Head</b></td>
				<td class="thd-tha"><b>Departement Head</b></td>
			</tr>
			<tr>
				<td class="thd-td2">( <?= $user->NAMA_USERS ?> )</td>
				<td class="thd-td2">
					<?php
					if ($approvals[0]->ROLE_APP == "Section Head" && $approvals[0]->ISAPPROVE_APP == "1") {
						echo '
									( ' . $approvals[0]->NAMA_USERS . ' )
								';
					} else {
						echo '
									(................................................)
								';
					}
					?>
				</td>
				<td>
					<?php
					if ($approvals[1]->ROLE_APP == "Department Head" && $approvals[1]->ISAPPROVE_APP == "1") {
						echo '
									( ' . $approvals[1]->NAMA_USERS . ' )
								';
					} else {
						echo '
									(................................................)
								';
					}
					?>
				</td>
			</tr>
		</table>
	</div>
</body>

</html>