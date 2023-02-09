<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-start mb-4">
        <a href="<?= site_url('form') ?>" class="rounded border-0 btn btn-warning mr-3">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h1 class="h3 mb-0 text-gray-800">Daftar Persetujuan</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="d-sm-flex align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-warning mb-2">Formulir Snack</h6>
                <button class="btn btn-sm btn-warning shadow-sm" data-toggle="modal" data-target="#mdlAdd">
                    <i class="fas fa-plus fa-sm text-white-50"></i>
                    Tambah
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" id="tableForm" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Daftar Persetujuan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php   
                        $inApproval = "APP_1";              
                        for ($x = 1; $x <= 15; $x++) {
                            $approval = "APP_" . $x;                          
                            if (!empty($flows[0]->$approval)) {                                
                                $a = $x + 1;
                                $inApproval = "APP_". $a;
                                
                                echo '
                                        <tr>
                                        <td>APP_' . $x . '</td>
                                        <td>' . $flows[0]->$approval . '</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                            <button type="button" data-toggle="modal" data-app="'.$approval.'" data-id="' . $flows[0]->ID_FLOW . '" data-name="' . $flows[0]->$approval . '" data-target="#mdlEdit" class="btn btn-primary btn-sm rounded mr-1 mdlEdit">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            <button type="button" data-toggle="modal" data-app="'.$approval.'" data-id="' . $flows[0]->ID_FLOW . '" data-name="' . $flows[0]->$approval . '" data-target="#mdlDelete" class="btn btn-danger btn-sm rounded mdlDelete">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                            </div>
                                        </td>
                                    </tr>
                                ';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>



</div>
<!-- /.container-fluid -->

</div>
<!-- End of Main Content -->

<!-- Modal Add -->
<div class="modal fade" id="mdlAdd" tabindex="-1" aria-labelledby="mdlAdd" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mdlAdd">Tambah Persetujuan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= site_url('form/updateFlow') ?>" method="post">
            <div class="modal-body">
                <div class="col">
                    <select class="custom-select" name="<?= $inApproval ?>" required>
                        <option value="" selected>Jabatan</option>
                        <option value="PICK">PIC Kendaraan</option>
                        <option value="PICG">PIC Gudang</option>
                        <option value="PICA">PIC Admin</option>
                        <option value="PICM">PIC Maintenance</option>
                        <option value="PICC">PIC Catering</option>
                        <option value="Section Head">Section Head</option>
                        <option value="Department Head">Department Head</option>    
                        <option value="Division Head">Division Head</option>                    
                    </select>
                </div>
            </div>
            <input type="hidden" name="ID_FLOW" value="<?= $flows[0]->ID_FLOW ?>"/>            
            <input type="hidden" name="ID_MAPPING" value="<?= $flows[0]->ID_MAPPING ?>"/>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-warning">Save Changes</button>
            </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="mdlEdit" tabindex="-1" aria-labelledby="mdlEdit" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mdlEdit">Edit List Approval</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= site_url('form/editFlow') ?>" method="post"> 
            <div class="modal-body">
                <div class="col">
                    <select class="custom-select" name="ROLE" required>
                        <option value="" selected>Jabatan</option>
                        <option value="PICK">PIC Kendaraan</option>
                        <option value="PICG">PIC Gudang</option>
                        <option value="PICA">PIC Admin</option>
                        <option value="PICM">PIC Maintenance</option>
                        <option value="PICC">PIC Catering</option>
                        <option value="Section Head">Section Head</option>
                        <option value="Department Head">Department Head</option>
                        <option value="Division Head">Division Head</option>
                    </select>
                </div>
            </div>
                <input type="hidden" name="ID_FLOW" value="<?= $flows[0]->ID_FLOW ?>"/>            
                <input type="hidden" name="ID_MAPPING" value="<?= $flows[0]->ID_MAPPING ?>"/>
                <input type="hidden" id="mdlEdit_app" name="APP" />
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-warning">Save Changes</button>
            </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Delete -->
<div class="modal fade" id="mdlDelete" tabindex="-1" aria-labelledby="mdlDelete" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mdlDelete">Delete List Approval?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    Anda akan menghapus list Approval <span id="mdlDelete_item">
                </p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <form action="<?= site_url('form/deleteFlow') ?>" method="post">                        
                    <input type="hidden" name="ID_FLOW" value="<?= $flows[0]->ID_FLOW ?>"/>            
                    <input type="hidden" name="ID_MAPPING" value="<?= $flows[0]->ID_MAPPING ?>"/>
                    <input type="hidden" id="mdlDelete_app" name="APP" />
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="<?= base_url('assets/vendor/jquery/jquery.min.js'); ?>"></script>
<script>    
    $('#tableForm tbody').on('click', '.mdlDelete', function() {
        const id = $(this).data('id')
        const name = $(this).data('name')
        const app = $(this).data('app')
        $('#mdlDelete_item').html(name)
        $('#mdlDelete_app').val(app)
        $('#mdlDelete_itemId').val(id)
    })
    $('#tableForm tbody').on('click', '.mdlEdit', function() {
        const id = $(this).data('id')
        const name = $(this).data('name')
        const app = $(this).data('app')
        $('#mdlEdit_item').html(name)
        $('#mdlEdit_app').val(app)
        $('#mdlEdit_itemId').val(id)
    })
</script>