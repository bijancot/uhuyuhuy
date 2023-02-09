<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Formulir</h1>
    </div>



    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="d-sm-flex align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-warning mb-2">Daftar Formulir</h6>
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
                            <th>Nama Tabel</th>
                            <th>No Dokumen</th>
                            <th>Nama Formulir</th>
                            <th>Jabatan</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                       
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
                <h5 class="modal-title" id="mdlAdd">Tambah Formulir</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= site_url('form/store') ?>" enctype="multipart/form-data" method="post">
                <div class="modal-body">
                    <div class="col">
                        <select class="custom-select" name="NAMA_TABEL" required>
                            <option value="" selected>Nama Tabel</option>
                            <?php
                            foreach ($tables as $item) {
                                echo '
                                        <option value="' . $item->table_name . '">' . $item->table_name . '</option>
                                    ';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="col">
                        <input type="text" class="form-control" name="NO_DOC" placeholder="No Dokumen" required>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="col">
                        <input type="text" class="form-control" name="NAMA_FORM" placeholder="Nama Formulir" required>
                    </div>
                </div>
                <div class="modal-body">
                    <div class="col">
                        <select class="custom-select" name="SECTION_FORM" required>
                            <option value="" selected>Jabatan</option>
                            <option value="Staff">Staff</option>
                            <option value="Staff Catering">Staff Catering</option>
                            <option value="PICK">PIC Kendaraan</option>
                            <option value="PICG">PIC Gudang</option>
                            <option value="PICA">PIC Admin</option>
                            <option value="PICM">PIC Maintenance</option>
                            <option value="Section Head">Section Head</option>
                            <option value="Department Head">Department Head</option>
                            <option value="Division Head">Division Head</option>
                        </select>
                    </div>
                </div>                   
                <div class="modal-body">
                    <div class="col">
                        <input type="text" class="form-control" name="PATH_TEMPLATE_PDF" placeholder="Nama File Template" required>
                    </div>
                </div>       
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Simpan</button>
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
                <h5 class="modal-title" id="mdlDelete">Hapus Formulir?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    Anda akan menghapus item <span id="mdlDelete_item">asdfs</span>
                </p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <form action="<?= site_url('form/destroy') ?>" method="post">
                    <input type="hidden" id="mdlDelete_itemId" name="ID_MAPPING" />
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- CUSTOM JAVASCRIPT -->
<script src="<?= base_url('assets/vendor/jquery/jquery.min.js'); ?>"></script>
<script>
    $(document).ready(function() {
        $('#tableForm').DataTable({
            'processing': true,
            'serverSide': true,
            'ordering': false,
            'serverMethod': 'post',
            'ajax': {
                'url':'<?= site_url('form/ajxGetData')?>'
            },
            'columns': [
                { data: 'namaTable' },
                { data: 'noDoc' },
                { data: 'namaForm' },
                { data: 'sectForm' },
                { data: 'aksi' }
            ]
        });
    });
    $(".custom-file-input").on("change", function() {
        var fileName = $(this).val().split("\\").pop();
        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });
    $('#tableForm tbody').on('click', '.mdlDelete', function() {
        const id = $(this).data('id')
        const name = $(this).data('name')
        $('#mdlDelete_item').html(name)
        $('#mdlDelete_itemId').val(id)
    })
</script>