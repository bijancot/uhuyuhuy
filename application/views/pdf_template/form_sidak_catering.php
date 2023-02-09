<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FORM SIDAK CATERING</title>

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

        html,
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 14px;
        }

        .w-100 {
            min-width: 100vw;
        }

        .p-min {
            padding: .7em .3em;
        }

        .pl-min {
            padding-left: .3em;
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

        .mb-min {
            margin-bottom: 1.5em;
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

        .font-weight-regular {
            font-weight: 400;
        }

        .font-weight-bold {
            font-weight: 800;
        }

        .text-align-left {
            text-align: left;
        }

        .text-align-center {
            text-align: center;
        }

        .text-align-justify {
            text-align: justify;
        }

        .wrapper-page {
            page-break-after: always;
        }

        .wrapper-page:last-child {
            page-break-after: avoid;
        }
    </style>

</head>

<body>
    <div class="wrapper-page">
        <div class="watermark"></div>
        <table class="border-2 border-collapse w-100 valign-middle mb-min">
            <tr>
                <td class="text-regular-sm font-family-arial text-align-center p-min border-right">
                    <img class="valign-middle" src="data:image/png;base64,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" height="25px" width="25px">
                    &nbsp;<strong class="valign-middle">UNITED TRACTORS</strong>
                </td>
                <td class="text-align-center border-1" colspan="2"><strong><?= $title_pdf ?></strong></td>
                <td class="text-align-center border-bottom"><strong>INTEGRASI SISTEM</strong></td>
            </tr>
            <tr>
                <td class="text-regular-sm text-align-center border-right" rowspan="3">Jl. Raya Bekasi KM 22 Cakung <br> Jakarta Timur</td>
                <td class="text-regular-sm pl-min pt-min" style="width: 15%;">
                    <strong>Nomor Dokumen</strong>
                </td>
                <td class="text-regular-sm border-right pt-min" style="width: 30%;">: <?= $noDoc ?></td>
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
                <td class="text-regular-sm border-right">: 1</td>
            </tr>
            <tr>
                <td class="text-regular-sm pl-min pb-min" style="width: 15%;">
                    <strong>Hal</strong>
                </td>
                <td class="text-regular-sm pb-min border-right">: ... Dari ...</td>
            </tr>
        </table>
        <p class="text-align-center text-regular mb-min">
            <strong>
                FORM SIDAK CATERING <br>
                NO.INST.03/M/BM/99
            </strong>
        </p>
        <table class="w-100 mb-min">
            <tr>
                <th colspan="3" class="text-align-left">Identitas Perusahaan Catering</th>
            </tr>
            <tr>
                <td style="width: 5%;">1.</td>
                <td style="width: 30%;">Nama Perusahaan</td>
                <td>: <?= $list[0]->PERUSHAAN_CATERING ?></td>
            </tr>
            <tr>
                <td>2.</td>
                <td>Nama Pemilik</td>
                <td>: <?= $list[0]->PEMILIK_CATERING ?></td>
            </tr>
            <tr>
                <td>3.</td>
                <td>Nama Pengurus</td>
                <td>: <?= $list[0]->PENGURUS_CATERING ?></td>
            </tr>
            <tr>
                <td>4.</td>
                <td>Alamat</td>
                <td>: <?= $list[0]->ALAMAT_CATERING ?></td>
            </tr>
            <tr>
                <td></td>
                <td>Telepon</td>
                <td>: <?= $list[0]->TELEPON_CATERING ?></td>
            </tr>
            <tr>
                <td></td>
                <td>Fax</td>
                <td>: <?= $list[0]->FAX_CATERING ?></td>
            </tr>
            <tr>
                <td>5.</td>
                <td>Jumlah Tenaga Kerja</td>
                <td>: <?= $list[0]->JMLKERJA_CATERING ?></td>
            </tr>
            <tr>
                <td>6.</td>
                <td>Perusahaan Yang Di Layani</td>
                <td>: <?= $list[0]->LAYANI_CATERING ?></td>
            </tr>
            <tr>
                <td>7.</td>
                <td>Kandepnaker</td>
                <td>: <?= $list[0]->KANDEPNAKER_CATERING ?></td>
            </tr>
            <tr>
                <td>8.</td>
                <td>Kanwil</td>
                <td>: <?= $list[0]->KANWIL_CATERING ?></td>
            </tr>
        </table>
        <table class="w-100 mb-min">
            <tr>
                <td colspan="2">I Persyaratan Tenaga Kerja</td>
                <td>Ya</td>
                <td>Tidak</td>
            </tr>
            <?php
            $ptks = explode(';', $list[0]->PTK);
            echo '
                <tr>
                    <td style="width: 5%;">1.</td>
                    <td style="width: 80%;">Apakah semua pegawai perusahaan catering yang mengerjakan dan melayani<br>makanan dan minuman telah bebas dari penyakit menular (Surat Keterangan Dokter)</td>
                    <td style="width: 5%;"><input type="checkbox" ' . ($ptks[0] == "true" ? "checked" : "") . '></td>
                    <td style="width: 5%;"><input type="checkbox" ' . ($ptks[0] == "false" ? "checked" : "") . '></td>
                </tr>
                <tr>
                    <td>2.</td>
                    <td>Apakah sudah dilakukan pemeriksaan paru-paru dengan rontgen</td>
                    <td><input type="checkbox" ' . ($ptks[1] == "true" ? "checked" : "") . '></td>
                    <td><input type="checkbox" ' . ($ptks[1] == "false" ? "checked" : "") . '></td>
                </tr>
                <tr>
                    <td>3.</td>
                    <td>Apakah dilakukan pemeriksaan jasa boga secara berkala</td>
                    <td><input type="checkbox" ' . ($ptks[2] == "true" ? "checked" : "") . '></td>
                    <td><input type="checkbox" ' . ($ptks[2] == "false" ? "checked" : "") . '></td>
                </tr>
                <tr>
                    <td>4.</td>
                    <td>Apakah petugas yang melayani makanan tidak menderita penyakit menular</td>
                    <td><input type="checkbox" ' . ($ptks[3] == "true" ? "checked" : "") . '></td>
                    <td><input type="checkbox" ' . ($ptks[3] == "false" ? "checked" : "") . '></td>
                </tr>
                <tr>
                    <td>5.</td>
                    <td>Apakah pegawai sudah mengikuti pelatihan gizi kerja hygiene dan sanitasi serta<br>penanggulangan keracunanan</td>
                    <td><input type="checkbox" ' . ($ptks[4] == "true" ? "checked" : "") . '></td>
                    <td><input type="checkbox" ' . ($ptks[4] == "false" ? "checked" : "") . '></td>
                </tr>
                <tr>
                    <td>6.</td>
                    <td>Apakah tenaga kerja memakai pakaian kerja dan alat pelindung diri yang sesuai</td>
                    <td><input type="checkbox" ' . ($ptks[5] == "true" ? "checked" : "") . '></td>
                    <td><input type="checkbox" ' . ($ptks[5] == "false" ? "checked" : "") . '></td>
                </tr>
            ';
            ?>
        </table>
        <table class="w-100 mb-min">
            <tr>
                <td colspan="2">II Persyaratan Kesehatan Bahan dan Penyimpanan makanan</td>
                <td>Ya</td>
                <td>Tidak</td>
            </tr>
            <?php
            $pkb_pms = explode(';', $list[0]->PKB_PM);
            echo '
                <tr>
                    <td style="width: 5%;">1.</td>
                    <td style="width: 80%;">Apakah daging dan ikan dibeli dalam keadaan segar dan dibeli di tempat resmi</td>
                    <td style="width: 5%;"><input type="checkbox" ' . ($pkb_pms[0] == "true" ? "checked" : "") . '></td>
                    <td style="width: 5%;"><input type="checkbox" ' . ($pkb_pms[0] == "false" ? "checked" : "") . '></td>
                </tr>
                <tr>
                    <td>2.</td>
                    <td>Apakah bahan makanan yang akan diolah dalam keadaan baik, segar, dan tidak rusak <br> (Berubah Warna dan Rasa)</td>
                    <td><input type="checkbox" ' . ($pkb_pms[1] == "true" ? "checked" : "") . '></td>
                    <td><input type="checkbox" ' . ($pkb_pms[1] == "false" ? "checked" : "") . '></td>
                </tr>
                <tr>
                    <td>3.</td>
                    <td>Apakah makanan kering atau kaleng yang di beli terdaftar di DEPKES</td>
                    <td><input type="checkbox" ' . ($pkb_pms[2] == "true" ? "checked" : "") . '></td>
                    <td><input type="checkbox" ' . ($pkb_pms[2] == "false" ? "checked" : "") . '></td>
                </tr>
                <tr>
                    <td>4.</td>
                    <td>Untuk bahan yang di kemas apakah memenuhi syarat kemasan tidak rusak atau<br>kembung dan belum kadaluarsa.</td>
                    <td><input type="checkbox" ' . ($pkb_pms[3] == "true" ? "checked" : "") . '></td>
                    <td><input type="checkbox" ' . ($pkb_pms[3] == "false" ? "checked" : "") . '></td>
                </tr>
                <tr>
                    <td>5.</td>
                    <td>Apakah bahan makanan yang di pergunakan dalam keadaan bersih bebas bakteri dan<br>bahan beracun serta bebas dari lembab.</td>
                    <td><input type="checkbox" ' . ($pkb_pms[4] == "true" ? "checked" : "") . '></td>
                    <td><input type="checkbox" ' . ($pkb_pms[4] == "false" ? "checked" : "") . '></td>
                </tr>
                <tr>
                    <td>6.</td>
                    <td>Apakah air yang untuk minum dan cuci peralatan sudah di periksa di labolatirium.</td>
                    <td><input type="checkbox" ' . ($pkb_pms[5] == "true" ? "checked" : "") . '></td>
                    <td><input type="checkbox" ' . ($pkb_pms[5] == "false" ? "checked" : "") . '></td>
                </tr>
                <tr>
                    <td>7.</td>
                    <td>Makanan jadi harus dalam kondisi baik dan sehat dan tempat penyimpanan harus<br>terlindung dan memenuhi standart kesehatan.</td>
                    <td><input type="checkbox" ' . ($pkb_pms[6] == "true" ? "checked" : "") . '></td>
                    <td><input type="checkbox" ' . ($pkb_pms[6] == "false" ? "checked" : "") . '></td>
                </tr>
                <tr>
                    <td>8.</td>
                    <td>Harus ada pemisahan tempat penyimpanan makanan kering dan basah.</td>
                    <td><input type="checkbox" ' . ($pkb_pms[7] == "true" ? "checked" : "") . '></td>
                    <td><input type="checkbox" ' . ($pkb_pms[7] == "false" ? "checked" : "") . '></td>
                </tr>
                <tr>
                    <td>9.</td>
                    <td>Apakah penyimpanan bahan mentah terpisah dengan penyimpanan makanan jadi.</td>
                    <td><input type="checkbox" ' . ($pkb_pms[8] == "true" ? "checked" : "") . '></td>
                    <td><input type="checkbox" ' . ($pkb_pms[8] == "false" ? "checked" : "") . '></td>
                </tr>
            ';
            ?>
        </table>
    </div>
    <div class="wrapper-page">
        <table class="w-100 mb-min">
            <tr>
                <td colspan="2">III. Persyaratan Sanitasi Lingkungan dan Fasilitas Pengolah Makanan</td>
                <td>Ya</td>
                <td>Tidak</td>
            </tr>
            <?php
            $psl_fpms = explode(';', $list[0]->PSL_FPM);
            echo '
                <tr>
                    <td style="width: 5%;">1.</td>
                    <td style="width: 80%;">Apakah alat-alat makan atau masak sesudah dipakai selalu dibersihkan dengan sabun<br>dan air panas kemudian dikeringkan</td>
                    <td style="width: 5%;"><input type="checkbox" ' . ($psl_fpms[0] == "true" ? "checked" : "") . '></td>
                    <td style="width: 5%;"><input type="checkbox" ' . ($psl_fpms[0] == "false" ? "checked" : "") . '></td>
                </tr>
                <tr>
                    <td>2.</td>
                    <td>Apakah alat makan dan masak bebas dari karat</td>
                    <td>
                        <input type="checkbox" ' . ($psl_fpms[1] == "true" ? "checked" : "") . '>
                    </td>
                    <td>
                        <input type="checkbox" ' . ($psl_fpms[1] == "false" ? "checked" : "") . '>
                    </td>
                </tr>
                <tr>
                    <td>3.</td>
                    <td>Apakah dapur kamar makan dan alat keperluan makan selalu dalam keadaan bersih<br>dan rapih</td>
                    <td>
                        <input type="checkbox" ' . ($psl_fpms[2] == "true" ? "checked" : "") . '>
                    </td>
                    <td>
                        <input type="checkbox" ' . ($psl_fpms[2] == "false" ? "checked" : "") . '>
                    </td>
                </tr>
                <tr>
                    <td>4.</td>
                    <td>Apakah tersedia tempat sampah bak sampah tertutup yang di letakkan sedekat<br>mungkin dengan sumber produksi sampah namun dapat menghindari kemungkinan<br>tercemar makanan oleh sampah</td>
                    <td>
                        <input type="checkbox" ' . ($psl_fpms[3] == "true" ? "checked" : "") . '>
                    </td>
                    <td>
                        <input type="checkbox" ' . ($psl_fpms[3] == "false" ? "checked" : "") . '>
                    </td>
                </tr>
                <tr>
                    <td>5.</td>
                    <td>Apakah telah tersedia lemari es untuk menyimpan makanan dan bahan makanan<br>yang cepat busuk</td>
                    <td>
                        <input type="checkbox" ' . ($psl_fpms[4] == "true" ? "checked" : "") . '>
                    </td>
                    <td>
                        <input type="checkbox" ' . ($psl_fpms[4] == "false" ? "checked" : "") . '>
                    </td>
                </tr>
                <tr>
                    <td>6.</td>
                    <td>Apakah tersedia lemari pendingin yang dapat mencapai suhu -5°C dengan kapasitas<br>cukup memadai sesuai dengan jenis makanan yang di gunakan.</td>
                    <td>
                        <input type="checkbox" ' . ($psl_fpms[5] == "true" ? "checked" : "") . '>
                    </td>
                    <td>
                        <input type="checkbox" ' . ($psl_fpms[5] == "false" ? "checked" : "") . '>
                    </td>
                </tr>
                <tr>
                    <td>7.</td>
                    <td>Apakah telah tersedia pembuangan asap dapur yang di lengkapi dengan cerobong<br>pembuang asap dapur yang dilengkapi dengan penangkap asap (Hood)</td>
                    <td>
                        <input type="checkbox" ' . ($psl_fpms[6] == "true" ? "checked" : "") . '>
                    </td>
                    <td>
                        <input type="checkbox" ' . ($psl_fpms[6] == "false" ? "checked" : "") . '>
                    </td>
                </tr>
                <tr>
                    <td>8.</td>
                    <td>Apakah telah tersedia fasilitas pencucian dari bahan yang kuat, permukaan halus dan<br>mudah dibersihkan.</td>
                    <td>
                        <input type="checkbox" ' . ($psl_fpms[7] == "true" ? "checked" : "") . '>
                    </td>
                    <td>
                        <input type="checkbox" ' . ($psl_fpms[7] == "false" ? "checked" : "") . '>
                    </td>
                </tr>
                <tr>
                    <td>9.</td>
                    <td>Apakah tata ruang dan lingkungan kerja dalam kondisi baik dan terpelihara (Saluran<br>air, Penerangan, Sirkulasi udara, Kamar mandi)</td>
                    <td>
                        <input type="checkbox" ' . ($psl_fpms[8] == "true" ? "checked" : "") . '>
                    </td>
                    <td>
                        <input type="checkbox" ' . ($psl_fpms[8] == "false" ? "checked" : "") . '>
                    </td>
                </tr>
                <tr>
                    <td>10.</td>
                    <td>Apakah telah tersedia alat keselamatan kerja seperti untuk kompor gas dan alat<br>pemadam api ringan</td>
                    <td>
                        <input type="checkbox" ' . ($psl_fpms[9] == "true" ? "checked" : "") . '>
                    </td>
                    <td>
                        <input type="checkbox" ' . ($psl_fpms[9] == "false" ? "checked" : "") . '>
                    </td>
                </tr>
                <tr>
                    <td>11.</td>
                    <td>Apakah alat listrik, oven, toaster, blender, dan hand mixer dll dalam keadaan baik dan<br>aman.</td>
                    <td>
                        <input type="checkbox" ' . ($psl_fpms[10] == "true" ? "checked" : "") . '>
                    </td>
                    <td>
                        <input type="checkbox" ' . ($psl_fpms[10] == "false" ? "checked" : "") . '>
                    </td>
                </tr>
            ';
            ?>
        </table>
        <p class="border-1 text-align-center p-min mb-min">
            Rekomendasi diberikan kepada perusahaan jasa boga yang memenuhi
            syarat – syarat keselamatan kesehatan dan lingkungan
        </p>
        <p class="mb-min">
            <strong>Catatan Team Sidak :</strong>
            <?= $list[0]->CATATAN_CATERING ?>
        </p>
        <table class="w-100">
            <tr class="text-align-center">
                <td><img src="<?= $user->PATH_TTD ?>" width="100px" height="100px" /></td>
                <td>
                    <?php
                    if ($approvals[0]->ROLE_APP == "Section Head" && $approvals[0]->ISAPPROVE_APP == "1") {
                        echo '
								<img src="' . $approvals[0]->PATH_TTD . '" width="100px" height="100px" />
							';
                    }
                    ?>
                </td>
            </tr>
            <tr class="text-align-center">
                <td><strong>PIC Admin</strong></td>
                <td><strong>Section Head</strong></td>
            </tr>
        </table>
    </div>
</body>

</html>