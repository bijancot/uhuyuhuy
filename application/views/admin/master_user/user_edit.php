<!-- Begin Page Content -->
<?php    
    if(empty($this->session->userdata('ROLE_USERS')) || $this->session->userdata('ROLE_USERS') != 'Admin GA'){
        redirect('login');
    }
?>
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Ubah Pengguna</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="d-sm-flex align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-warning"><?= $dataUser[0]->NAMA_USERS?></h6>
            </div>
        </div>
        <div class="card-body">
            <?php
                if(!empty($this->session->flashdata('error'))){
                    echo '
                        <div class="alert alert-danger" role="alert">
                            '.$this->session->flashdata('error').'
                        </div>
                    ';        
                }
            ?>
            <form action="<?= site_url('user/update')?>" method="post">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="inputNama">Nama</label>
                        <input type="text" class="form-control" value="<?= $dataUser[0]->NAMA_USERS ?>" placeholder="Nama" name="NAMA_USERS" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="inputTelp">Telepon</label>
                        <input type="tel" class="form-control" value="<?= $dataUser[0]->NOTELP_USERS ?>" placeholder="Telepon" name="NOTELP_USERS" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="inputRole">Jabatan</label>
                        <select class="custom-select" name="ROLE_USERS" required>
                            <option value="">Jabatan</option>
                            <option value="Staff" <?= $dataUser[0]->ROLE_USERS == 'Staff'?'selected' : '' ?> >Staff</option>
                            <option value="Staff Catering" <?= $dataUser[0]->ROLE_USERS == 'Staff Catering'?'selected' : '' ?> >Staff Catering</option>
                            <option value="PICK" <?= $dataUser[0]->ROLE_USERS == 'PICK'?'selected' : '' ?> >PIC Kendaraan</option>
                            <option value="PICG" <?= $dataUser[0]->ROLE_USERS == 'PICG'?'selected' : '' ?> >PIC Gudang</option>
                            <option value="PICA" <?= $dataUser[0]->ROLE_USERS == 'PICA'?'selected' : '' ?> >PIC Admin</option>
                            <option value="PICM" <?= $dataUser[0]->ROLE_USERS == 'PICM'?'selected' : '' ?> >PIC Maintenance</option>
                            <option value="PICC" <?= $dataUser[0]->ROLE_USERS == 'PICC'?'selected' : '' ?> >PIC Catering</option>
                            <option value="Section Head" <?= $dataUser[0]->ROLE_USERS == 'Section Head'?'selected' : '' ?> >Section Head</option>
                            <option value="Department Head" <?= $dataUser[0]->ROLE_USERS == 'Department Head'? 'selected' : '' ?> >Department Head</option>
                            <option value="Division Head" <?= $dataUser[0]->ROLE_USERS == 'Division Head'? 'selected' : '' ?> >Division Head</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="inputDepartemen">Departemen</label>
                        <select class="custom-select" name="DEPT_USERS" required>
                            <option value="" >Departement</option>
                            <option value="General Affairs" <?= $dataUser[0]->DEPT_USERS == 'General Affairs'? 'selected' : '' ?>>General Affairs</option>
                            <option value="Others" <?= $dataUser[0]->DEPT_USERS == 'Others'? 'selected' : '' ?>>Others</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="inputDivisi">Divisi</label>
                        <select class="custom-select" name="DIV_USERS" required>
                            <option value="">Divisi</option>                            
                            <option value="Corporate Finance" <?= $dataUser[0]->DIV_USERS == 'Corporate Finance'? 'selected' : '' ?>>Corporate Finance</option>
                            <option value="Corporate Human Capital & Corpu" <?= $dataUser[0]->DIV_USERS == 'Corporate Human Capital & Corpu'? 'selected' : '' ?>>Corporate Human Capital & Corpu</option>
                            <option value="Corporate Governance & Sustainability" <?= $dataUser[0]->DIV_USERS == 'Corporate Governance & Sustainability'? 'selected' : '' ?>>Corporate Governance & Sustainability</option>
                            <option value="Procurement and Investment" <?= $dataUser[0]->DIV_USERS == 'Procurement and Investment'? 'selected' : '' ?>>Procurement and Investment</option>
                            <option value="Corporate Audit" <?= $dataUser[0]->DIV_USERS == 'Corporate Audit'? 'selected' : '' ?>>Corporate Audit</option>
                            <option value="Group Legal" <?= $dataUser[0]->DIV_USERS == 'Group Legal'? 'selected' : '' ?>>Group Legal</option>
                            <option value="Digitalization and Differentation" <?= $dataUser[0]->DIV_USERS == 'Digitalization and Differentation'? 'selected' : '' ?>>Digitalization and Differentation</option>
                            <option value="Cosporate Strategy and Technology" <?= $dataUser[0]->DIV_USERS == 'Cosporate Strategy and Technology'? 'selected' : '' ?>>Cosporate Strategy and Technology</option>
                            <option value="Service Division" <?= $dataUser[0]->DIV_USERS == 'Service Division'? 'selected' : '' ?>>Service Division</option>
                            <option value="Parts Division" <?= $dataUser[0]->DIV_USERS == 'Parts Division'? 'selected' : '' ?>>Parts Division</option>
                            <option value="Truck Mining Operation" <?= $dataUser[0]->DIV_USERS == 'Truck Mining Operation'? 'selected' : '' ?>>Truck Mining Operation</option>
                            <option value="Sales Operation Division" <?= $dataUser[0]->DIV_USERS == 'Sales Operation Division'? 'selected' : '' ?>>Sales Operation Division</option>
                            <option value="Truck Sales Operation" <?= $dataUser[0]->DIV_USERS == 'Truck Sales Operation'? 'selected' : '' ?>>Truck Sales Operation</option>
                            <option value="Marketing Division" <?= $dataUser[0]->DIV_USERS == 'Marketing Division'? 'selected' : '' ?>>Marketing Division</option>
                            <option value="Board of Direction" <?= $dataUser[0]->DIV_USERS == 'Board of Direction'? 'selected' : '' ?>>Board of Direction</option>
                            <option value="Others" <?= $dataUser[0]->DIV_USERS == 'Others'? 'selected' : '' ?>>Others</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="inputUsername">NRP</label>
                        <input type="text" value="<?= $dataUser[0]->USER_USERS ?>" class="form-control" name="USER_USERS" placeholder="NRP" required disabled>
                    </div>
                </div>
                <!-- <div class="form-row">
                    <div class="form-group col-md-6">
                        <label for="inputPassword">Password</label>
                        <input type="text" clasIlham Sagita Putras="form-control" id="inputPassword">
                    </div>
                </div> -->
                <input type="hidden" value="<?= $dataUser[0]->ID_USERS?>" name="ID_USERS" />
                <a href="<?= site_url('user') ?>" class="btn btn-secondary">Batal</a>
                <button type="submit" class="btn btn-warning">Simpan</button>
            </form>
        </div>
    </div>

</div>
<!-- /.container-fluid -->

</div>
<!-- End of Main Content -->