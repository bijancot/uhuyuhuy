<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>FORM INTERNAL WORK ORDER</title>
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
			margin: 5px 0px 5px;
			text-align: center;
			width: 66, 6%;
			font-size: 10px;
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
			padding-top: 2px;
			padding-bottom: 2px;
			padding-left: 6px;
			font-size: 10px;
			text-align: left;
			vertical-align: top
		}

		.tg .tg-5rbv {
			border: 1px solid black;
			font-size: 10px;
			border-color: black;
			padding-left: 6px;
			font-weight: bold;
			text-align: center;
			vertical-align: middle
		}

		.valign-middle {
			vertical-align: middle;
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

		.absolute {
			position: absolute;
		}

		.pos-right {
			right: 320px;
		}

		.pos-left {
			left: 0;
		}

		.container-item {
			height: 600px;
			width: 900px;
			margin-top: 10px;
		}

		.table-layout-fixed {
			table-layout: fixed;
		}

		.item {
			margin-right: 10px !important;
			margin-bottom: 10px !important;
		}
	</style>
</head>

<body style="padding-left:0.5cm; padding-right:0.5cm; font-family:Arial, sans-serif;">
	<div class="wrapper-page">
		<div class="watermark"></div>
		<div>
			<table class="tg" width="100%;">
				<thead>
					<tr>
						<th class="tg-ir4y">
							<img class="valign-middle" src="data:image/png;base64,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" height="25px" width="25px">
							&nbsp;<strong class="valign-middle">UNITED TRACTORS</strong>
						</th>
						<th class="tg-k27y" colspan="2" rowspan="2"><?= $title_pdf ?></th>
						<th class="tg-k27y" rowspan="2">INTEGRASI SISTEM</th>
					</tr>
					<tr>
						<td class="tg-w2dt" rowspan="4">Jl. Raya Bekasi Km. 22 Cakung<br>
							Jakarta Timur</td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="tg-z9od" width="13%">No. Dokumen</td>
						<td class="tg-z9od" width="33%"> : <?= $noDoc ?></td>
						<td class="tg-xsvg" rowspan="3">ISO 9001 : 2008 ; 1SO<br>
							14001 : 2004 ; OHSAS<br>
							18001 : 2007 &amp; SMK3</td>
					</tr>
					<tr>
						<td class="tg-z9ad">Revisi</td>
						<td class="tg-z9ad">: 0</td>
					</tr>
					<tr>
						<td class="tg-z9cd">Hal</td>
						<td class="tg-z9cd">: … Dari : …</td>
					</tr>
				</tbody>
			</table>
		</div>
		<br>
		<div>
			<table class="tg" width="100%" style="border-color: black;">
				<thead>
					<tr>
						<th class="tg-5rbv" style="text-align:left; width:10%">Trouble Ticket</th>
						<th class="tg-5rbv" style="text-align:left;width:35%">: <?= $list[0]->TT_IWO ?></th>
						<?php
						$date = date_create($list[0]->MULAI_IWO);
						$fullDate 	= date_format($date, 'j') . ' ' . $getMonth[date_format($date, 'n')] . ' ' . date_format($date, 'Y') . ' ' . date_format($date, 'H:i');
						echo '
							<th class="tg-5rbv" style="text-align:left; width:10%" rowspan="2">Tanggal & Waktu</th>
							<th class="tg-5rbv" style="text-align:left; width:10%">Mulai</th>
							<th class="tg-5rbv" style="text-align:left; width:35%">: ' . $fullDate . '</th>
						';
						?>
					</tr>
				</thead>
				<tbody>
					<tr>
						<?php
						$date1 		= date_create($list[0]->TGL_IWO);
						$fullDate1 	= date_format($date1, 'j') . ' ' . $getMonth[date_format($date1, 'n')] . ' ' . date_format($date1, 'Y');
						$date2 		= date_create($list[0]->SELESAI_IWO);
						$fullDate2 	= date_format($date2, 'j') . ' ' . $getMonth[date_format($date2, 'n')] . ' ' . date_format($date2, 'Y') . ' ' . date_format($date2, 'H:i');

						echo '
							<td class="tg-5rbv" style="text-align:left;">Tanggal</td>
							<td class="tg-5rbv" style="text-align:left;">: ' . $fullDate1 . '</td>
							<td class="tg-5rbv" style="text-align:left;">Selesai</td>
							<td class="tg-5rbv" style="text-align:left;">: ' . $fullDate2 . '</td>
						';
						?>
					</tr>
				</tbody>
			</table>
		</div>
		<br>
		<div>
			<table class="tg" width="100%" style="border-color: black;">
				<thead>
					<tr>
						<th class="tg-5rbv" style="text-align:left;" colspan="2">PEMOHON</th>
						<th class="tg-5rbv" style="text-align:left;" colspan="2">PENERIMA TUGAS</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="tg-5r9a" style="text-align:left; width:11%;">1. Nama</td>
						<td class="tg-5r9a" style="text-align:left; width:30%;">: <?= $list[0]->NAMA_PEMOHON ?></td>
						<td class="tg-5r9a" style="text-align:left; width:20%;">1. Nama</td>
						<td class="tg-5r9a" style="text-align:left; width:30%;">: <?= $list[0]->NAMA_PENERIMA ?></td>
					</tr>
					<tr>
						<td class="tg-5r9a" style="text-align:left;">2. Divisi</td>
						<td class="tg-5r9a" style="text-align:left;">: <?= $list[0]->DIVISI_PEMOHON ?></td>
						<td class="tg-5r9a" style="text-align:left;">2. Tanda Tangan</td>
						<td class="tg-5r9a" style="text-align:left;">:</td>
					</tr>
					<tr>
						<td class="tg-5r9a" style="text-align:left;">3. Ekst</td>
						<td class="tg-5r9a" style="text-align:left;">: <?= $list[0]->EKST_PEMOHON ?></td>
						<td class="tg-5r9a" style="text-align:left;"></td>
						<td class="tg-5r9a" style="text-align:left;">:</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div>
			<table class="tg" width="100%" style="border-color: black;">
				<thead>
					<tr>
						<th class="tg-5rbv" style="text-align:left;" colspan="2">Jenis Perbaikan:</th>
					</tr>
				</thead>
				<tbody>
					<?php
					$jenPerbaikan = explode(';', $list[0]->JENIS_PERBAIKAN);
					echo '
						<tr>
							<td class="tg-5r9a" style="text-align:left; width:49%;">
								<input type="checkbox" id="checkbox_status1" ' . ($jenPerbaikan[0] == '1' ? 'checked' : '') . '>
								<label for="checkbox_status1">Penggantian Baru</label>
							</td>
							<td class="tg-5r9a" style="text-align:left; width:60%;" rowspan="3">Keterangan : ' . $list[0]->KET_PERBAIKAN . '</td>
						</tr>
						<tr>
							<td class="tg-5r9a" style="text-align:left; width:49%;">
								<input type="checkbox" id="checkbox_status1" ' . ($jenPerbaikan[1] == '1' ? 'checked' : '') . '>
								<label for="checkbox_status1">Penggantian Sebagian</label>
							</td>
						</tr>
						<tr>
							<td class="tg-5r9a" style="text-align:left; width:49%;">
								<input type="checkbox" id="checkbox_status1" ' . ($jenPerbaikan[2] == '1' ? 'checked' : '') . '>
								<label for="checkbox_status1">Perbaikan / Modifikasi</label>
							</td>
						</tr>
					';
					?>
				</tbody>
			</table>
		</div>
		<div>
			<table class="tg" width="100%" style="border-color: black;">
				<thead>
					<tr>
						<th class="tg-5rbv" style="text-align:left;">Alasan Perbaikan:</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="tg-5r9a" style="text-align:left;"><?= $list[0]->ALASAN_PERBAIKAN ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div>
			<table class="tg" width="100%" style="border-color: black;">
				<thead>
					<tr>
						<th class="tg-5rbv" style="text-align:left;" colspan="4">Kebutuhan Material Maintenance</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="tg-5r9a" style="text-align:center; width: 5%;">No</td>
						<td class="tg-5r9a" style="text-align:center;">Nama Barang</td>
						<td class="tg-5r9a" style="text-align:center; width: 15%;">Qty</td>
						<td class="tg-5r9a" style="text-align:center; width: 15%;">MUS No</td>
					</tr>
					<?php
					$no = 1;
					foreach ($list as $item) {
						echo '
							<tr>
								<td class="tg-5r9a" style="text-align:center;">' . $no . '</td>
								<td class="tg-5r9a" style="text-align:left;">' . $item->BARANG_IWO . '</td>
								<td class="tg-5r9a" style="text-align:left;">' . $item->QTY_IWO . '</td>
								<td class="tg-5r9a" style="text-align:left;">' . $item->MUS_IWO . '</td>
							</tr>
						';
						$no++;
					}
					?>
				</tbody>
			</table>
		</div>
		<br>
		<div>
			<table class="thd" align="left">
				<tr>
					<td class="thd-tha" style="width: 33,3%;">Diketahui Oleh : </td>
					<td class="thd-tha" style="width: 33,3%;">Disetujui Oleh : </td>
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
				</tr>
				<tr>
					<td class="thd-tha"><b>PIC Maintenance</b></td>
					<td class="thd-tha"><b>Section Head</b></td>
				</tr>
				<tr>
					<td class="thd-td2">( <?= $user->NAMA_USERS ?> )</td>
					<?php
					if ($approvals[0]->ROLE_APP == "Section Head" && $approvals[0]->ISAPPROVE_APP == "1") {
						echo '
							<td class="thd-td2">( ' . $approvals[0]->NAMA_USERS . ' )</td>
						';
					} else {
						echo '
							<td class="thd-td2">(................................................)</td>
						';
					}
					?>
				</tr>
			</table>
		</div>
	</div>
	<div class="wrapper-page">
		<div>
			<table style="width:80%; text-align:left; font-size: 10px;">
				<tr>
					<td>
						<b><i>Di isi oleh Pemohon</i></b>
					</td>
				</tr>
			</table>
		</div>
		<div>
			<table class="tg" width="100%" style="border-color: black;">
				<thead>
					<tr>
						<th class="tg-5rbv" style="text-align:left;" colspan="2">STATUS PEKERJAAN</th>
						<th class="tg-5rbv" style="text-align:center;" colspan="3">Kualitas Kerja</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="tg-5r9a" style="text-align:left;" rowspan="2">
							<input type="checkbox" id="checkbox_status1" <?= $list[0]->STATUS_PEKERJAAN == "1" ? 'checked' : '' ?>>
							<label for="checkbox_status1">Selesai</label>
						</td>
						<td class="tg-5r9a" style="text-align:left;" rowspan="2">
							<input type="checkbox" id="checkbox_status1" <?= $list[0]->STATUS_PEKERJAAN == "2" ? 'checked' : '' ?>>
							<label for="checkbox_status1">Pending</label>
						</td>
						<td class="tg-5r9a" style="text-align:center; width: 15%">Puas</td>
						<td class="tg-5r9a" style="text-align:center; width: 15%">Cukup</td>
						<td class="tg-5r9a" style="text-align:center; width: 15%">Tidak Puas</td>
					</tr>
					<tr>
						<td class="tg-5r9a" style="text-align:center;"><?= $list[0]->KUALITAS_PEKERJAAN == "1" ? 'V' : '' ?></td>
						<td class="tg-5r9a" style="text-align:center;"><?= $list[0]->KUALITAS_PEKERJAAN == "2" ? 'V' : '' ?></td>
						<td class="tg-5r9a" style="text-align:center;"><?= $list[0]->KUALITAS_PEKERJAAN == "3" ? 'V' : '' ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div>
			<table class="tg" width="100%" style="border-color: black;">
				<thead>
					<tr>
						<th class="tg-5rbv" style="text-align:left; width: 61%;">Keterangan:</th>
						<th class="tg-5rbv" style="text-align:center; width: 25%;" rowspan="3">Tanda tangan user</th>
						<th class="tg-5rbv" style="text-align:left;  width: 25%;" rowspan="3"><?= $list[0]->STATUS_PEKERJAAN != null ? '<img src="' . $user->PATH_TTD . '" width="25px" height="25px" />' : '' ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="tg-5r9a" style="text-align:left; width:60%;"><?= $list[0]->KET_PEKERJAAN ?></td>
					</tr>
				</tbody>
			</table>
		</div>
	</div>
</body>

</html>