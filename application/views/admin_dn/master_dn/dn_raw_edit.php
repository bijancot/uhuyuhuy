<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Ubah Debit Note</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="d-sm-flex align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-warning">Nama Debit Note</h6>
            </div>
        </div>
        <div class="card-body">
            <form action="<?= site_url('debitnote/update') ?>" method="post">
                <div class="form-row">
                    <div class="form-group col-md-5">
                        <label for="inputNoFaktur">Tipe</label>
                        <select class="form-control" name="TIPE_DEBITNOTE">
                            <option value="Listrik" <?= $debitnote->TIPE_DEBITNOTE == 'Listrik' ? 'selected' : '' ?>>Listrik</option>
                            <option value="Rent" <?= $debitnote->TIPE_DEBITNOTE == 'Rent' ? 'selected' : '' ?>>Rent</option>
                            <option value="Service" <?= $debitnote->TIPE_DEBITNOTE == 'Service' ? 'selected' : '' ?>>Service</option>
                            <option value="Air" <?= $debitnote->TIPE_DEBITNOTE == 'Air' ? 'selected' : '' ?>>Air</option>
                            <option value="Telefon" <?= $debitnote->TIPE_DEBITNOTE == 'Telefon' ? 'selected' : '' ?>>Telefon</option>
                            <option value="Others" <?= $debitnote->TIPE_DEBITNOTE == 'Others' ? 'selected' : '' ?>>Others</option>
                        </select>
                    </div>
                    <div class="form-group col-md-5 ml-3">
                        <label for="inputNoFaktur">No. Faktur</label>
                        <input type="text" class="form-control" name="NOFAKTUR_DEBITNOTE" value="<?= $debitnote->NOFAKTUR_DEBITNOTE ?>" id="inputNoFaktur" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-5">
                        <label for="inputNoFaktur">Tanggal Faktur</label>
                        <input type="date" class="form-control" name="TGLFAKTUR_DEBITNOTE" value="<?= $debitnote->TGLFAKTUR_DEBITNOTE ?>" id="inputNoFaktur" required>
                    </div>
                    <div class="form-group col-md-5 ml-3">
                        <label for="inputNoFaktur">Tanggal Jatuh Tempo</label>
                        <input type="date" class="form-control" name="TGLJATUH_DEBITNOTE" value="<?= $debitnote->TGLJATUH_DEBITNOTE ?>" id="inputNoFaktur" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-5">
                        <label for="inputNoFaktur">No. Faktur Pajak</label>
                        <input type="text" class="form-control" name="NOFAKTURPAJAK_DEBITNOTE" value="<?= $debitnote->NOFAKTURPAJAK_DEBITNOTE ?>" id="inputNoFaktur" required>
                    </div>
                    <div class="form-group col-md-5 ml-3">
                        <label for="inputNoFaktur">Kurs Pajak</label>
                        <input type="text" class="form-control" name="KURSPAJAK_DEBITNOTE" value="<?= $debitnote->KURSPAJAK_DEBITNOTE ?>" id="inputNoFaktur" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-5">
                        <label for="inputNoFaktur">No. Pelanggan</label>
                        <input type="text" class="form-control" name="NOPELANGGAN_DEBITNOTE" value="<?= $debitnote->NOPELANGGAN_DEBITNOTE ?>" id="inputNoFaktur" required>
                    </div>
                    <div class="form-group col-md-5 ml-3">
                        <label for="inputNoFaktur">Email</label>
                        <input type="text" class="form-control" name="EMAIL_DEBITNOTE" value="<?= $debitnote->EMAIL_DEBITNOTE ?>" id="inputNoFaktur" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-5">
                        <label for="inputNoFaktur">No. Pesanan</label>
                        <input type="text" class="form-control" name="NOPESANAN_DEBITNOTE" value="<?= $debitnote->NOPESANAN_DEBITNOTE ?>" id="inputNoFaktur" required>
                    </div>
                    <div class="form-group col-md-5 ml-3">
                        <label for="inputNoFaktur">Tanggal Pesanan</label>
                        <input type="date" class="form-control" name="TGLPESANAN_DEBITNOTE" value="<?= $debitnote->TGLPESANAN_DEBITNOTE ?>" id="inputNoFaktur" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-5">
                        <label for="inputNoFaktur">Mata Uang</label>
                        <input type="text" class="form-control" name="MATAUANG" value="<?= $debitnote->MATAUANG ?>" id="inputNoFaktur" required>
                    </div>
                    <div class="form-group col-md-5 ml-3">
                        <label for="inputNoFaktur">Nama Perusahaan</label>
                        <input type="text" class="form-control" name="NAMAPERUSAHAAN_DEBITNOTE" value="<?= $debitnote->NAMAPERUSAHAAN_DEBITNOTE ?>" id="inputNoFaktur" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-5">
                        <label for="inputNoFaktur">Alamat Perusahaan</label>
                        <input type="text" class="form-control" name="ALAMATPERUSAHAAN_DEBITNOTE" value="<?= $debitnote->ALAMATPERUSAHAAN_DEBITNOTE ?>" id="inputNoFaktur" required>
                    </div>
                    <div class="form-group col-md-5 ml-3">
                        <label for="inputNoFaktur">NPWP</label>
                        <input type="text" class="form-control" name="NPWP_DEBITNOTE" value="<?= $debitnote->NPWP_DEBITNOTE ?>" id="inputNoFaktur" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-5">
                        <label for="inputNoFaktur">Barang / Jasa Kena Pajak</label>
                        <input type="text" class="form-control" name="BARANGJASA_DEBITNOTE" value="<?= $debitnote->BARANGJASA_DEBITNOTE ?>" id="inputNoFaktur" required>
                    </div>
                    <div class="form-group col-md-5 ml-3">
                        <label for="inputNoFaktur">Harga Jual</label>
                        <input type="text" class="form-control" name="HARGAJUAL_DEBITNOTE" value="<?= $debitnote->HARGAJUAL_DEBITNOTE ?>" id="inputNoFaktur" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-5">
                        <label for="inputNoFaktur">Total Harga Jual</label>
                        <input type="text" class="form-control" name="TOTHARGAJUAL_DEBITNOTE" value="<?= $debitnote->TOTHARGAJUAL_DEBITNOTE ?>" id="inputNoFaktur" required>
                    </div>
                    <div class="form-group col-md-5 ml-3">
                        <label for="inputNoFaktur">Potongan Harga</label>
                        <input type="text" class="form-control" name="POTHARGA_DEBITNOTE" value="<?= $debitnote->POTHARGA_DEBITNOTE ?>" id="inputNoFaktur" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-5">
                        <label for="inputNoFaktur">Uang Muka Diterima</label>
                        <input type="text" class="form-control" name="UANGMUKA_DEBITNOTE" value="<?= $debitnote->UANGMUKA_DEBITNOTE ?>" id="inputNoFaktur" required>
                    </div>
                    <div class="form-group col-md-5 ml-3">
                        <label for="inputNoFaktur">Harga Setelah Potongan & Uang Muka</label>
                        <input type="text" class="form-control" name="HARGAPOTONGAN_DEBITNOTE" value="<?= $debitnote->HARGAPOTONGAN_DEBITNOTE ?>" id="inputNoFaktur" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-5">
                        <label for="inputNoFaktur">Dasar Pengenaan Pajak</label>
                        <input type="text" class="form-control" name="DPP_DEBITNOTE" value="<?= $debitnote->DPP_DEBITNOTE ?>" id="inputNoFaktur" required>
                    </div>
                    <div class="form-group col-md-5 ml-3">
                        <label for="inputNoFaktur">PPN</label>
                        <input type="text" class="form-control" name="PPN_DEBITNOTE" value="<?= $debitnote->PPN_DEBITNOTE ?>" id="inputNoFaktur" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-5">
                        <label for="inputNoFaktur">Grand Total</label>
                        <input type="text" class="form-control" name="GRANDTOTAL_DEBITNOTE" value="<?= $debitnote->GRANDTOTAL_DEBITNOTE ?>" id="inputNoFaktur" required>
                    </div>
                </div>
                <input type="hidden" name="ID_DEBITNOTE" value="<?= $debitnote->ID_DEBITNOTE ?>" />
                <a href="<?= site_url('debitnote') ?>" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-warning">Simpan</button>
            </form>
        </div>
    </div>

</div>
<!-- /.container-fluid -->

</div>
<!-- End of Main Content -->