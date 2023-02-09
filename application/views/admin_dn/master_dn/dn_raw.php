<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Debit Note (RAW)</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="d-sm-flex align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-warning mb-2">Daftar Debit Note (RAW)</h6>
                <div>
                    <button class="btn btn-sm btn-warning shadow-sm" data-toggle="modal" data-target="#mdlAdd">
                        <i class="fas fa-plus fa-sm text-white-50"></i>
                        Tambah
                    </button>
                    <a href="<?= site_url('debitnote/downloadTemplate') ?>" class="btn btn-sm btn-info shadow-sm">
                        <i class="fas fa-file-download text-white-50"></i>
                        Unduh Template
                    </a>
                    <button class="btn btn-sm btn-success shadow-sm" id="downloadMultiple" data-toggle="modal" data-target="#mdlGenerateMulti" disabled>
                        <i class="fas fa-exchange-alt"></i>
                        Generate Multiple
                    </button>
                    <button class="btn btn-sm btn-danger shadow-sm" id="deleteMultiple" data-toggle="modal" data-target="#mdlDeleteMulti" disabled>
                        <i class="fas fa-trash"></i>
                        Delete Multiple
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php
                if(!empty($this->session->flashdata('success'))){
                    echo '
                        <div class="alert alert-success" role="alert">
                            Selamat debit note berhasil ditambahkan !
                        </div>
                    ';        
                }
                if(!empty($this->session->flashdata('error'))){
                    echo '
                        <div class="alert alert-danger" role="alert">
                           '.$this->session->flashdata('error').'
                        </div>
                    ';        
                }
                if(!empty($this->session->flashdata('errorDuplicate')) && $this->session->flashdata('errorDuplicate') != null){
                    echo '
                        <div class="alert alert-danger" role="alert">
                            Oops, debit note dengan no dn <b>'.implode(', ', $this->session->flashdata('errorDuplicate')).'</b> telah terdaftar.
                        </div>
                    ';        
                }
            ?>
            <div class="table-responsive">
                <table class="table table-bordered" id="tableDN" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>
                                <div class="custom-control custom-checkbox" style="text-align:center;">
                                    <input type="checkbox" class="custom-control-input" id="checkAll">
                                    <label class="custom-control-label" for="checkAll"></label>
                                </div>
                            </th>
                            <th>No. DN</th>
                            <th>Tanggal DN</th>
                            <th>Tanggal Jatuh Tempo</th>
                            <th>No. Faktur Pajak</th>
                            <th>Nama Perusahaan</th>
                            <th>Barang / Jasa Kena Pajak</th>
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
                <h5 class="modal-title" id="mdlAdd">Tambah Debit Note</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="<?= site_url('debitnote/store') ?>" enctype="multipart/form-data" method="post">
                <div class="modal-body" style="padding-left:6%;padding-right:6%;">
                    <div class="col">
                        <input type="file" name="FILEDN" class="custom-file-input" id="fileDN">
                        <label class="custom-file-label" for="fileDN">Unggah Debit Note</label>
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
<!-- Modal Generate -->
<div class="modal fade" id="mdlGenerate" tabindex="-1" aria-labelledby="mdlGenerate" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mdlGenerate">Generate Debit Note?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    Anda akan men-generate item dengan No. Faktur <span id="mdlGenerate_item"></span>
                </p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <form action="<?= site_url('debitnote/generateDN') ?>" method="post">
                    <input type="hidden" id="mdlGenerate_itemId" name="ID_DEBITNOTE" />
                    <input type="hidden" name="STAT_DEBITNOTE" value="1" />
                    <button type="submit" class="btn btn-success">Generate</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Modal Generate Multiple -->
<div class="modal fade" id="mdlGenerateMulti" tabindex="-1" aria-labelledby="mdlGenerate" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mdlGenerate">Generate Debit Note?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    Anda akan men-generate <span id="mdlGenerateMulti_count"></span> debitnote
                </p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <form action="<?= site_url('debitnote/generateMultiDN') ?>" method="post">
                    <input type="hidden" id="mdlGenerateMulti_itemId" name="ID_DEBITNOTE" />
                    <button type="submit" class="btn btn-success">Generate</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Modal Delete Multiple -->
<div class="modal fade" id="mdlDeleteMulti" tabindex="-1" aria-labelledby="mdlGenerate" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mdlDeleteMulti">Hapus Debit Note?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    Anda akan menghapus <span id="mdlDeleteMulti_count"></span> debitnote
                </p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <form action="<?= site_url('debitnote/destroyMultiDN') ?>" method="post">
                    <input type="hidden" id="mdlDeleteMulti_itemId" name="ID_DEBITNOTE" />
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Modal Delete -->
<div class="modal fade" id="mdlDelete" tabindex="-1" aria-labelledby="mdlDelete" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mdlDelete">Hapus Debit Note?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    Anda akan menghapus item dengan No. Faktur <span id="mdlDelete_item">13213213</span>
                </p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <form action="<?= site_url('debitnote/destroyDN') ?>" method="post">
                    <input type="hidden" id="mdlDelete_itemId" name="ID_DEBITNOTE" />
                    <button type="submit" class="btn btn-danger">Hapus</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/vendor/jquery/jquery.min.js'); ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js" defer></script>
<script>
    $(document).ready(function() {
        $('#tableDN').DataTable({
            'processing': true,
            'serverSide': true,
            'ordering': false,
            'serverMethod': 'post',
            'ajax': {
                'url':'<?= site_url('debitnote/ajxGetData')?>',
                'data': {status: '0'}
            },
            'columns': [
                { data: 'no' },
                { data: 'cek' },
                { data: 'noDN' },
                { data: 'tglFaktur' },
                { data: 'tglJatuh' },
                { data: 'noFaktur' },
                { data: 'namaPer' },
                { data: 'barangJasa' },
                { data: 'aksi' }
            ]
        });
        $('.select2').select2({
            width: 'resolve'
        });
    });
    $(".custom-file-input").on("change", function() {
        var fileName = $(this).val().split("\\").pop();
        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });
    $('#tableTransaction tbody').on('click', '.mdlApprove', function() {
        const id = $(this).data("id")
        $('#mdlApprove_id').val(id)
    })
    const hapus = (id, noFaktur) => {
        $('#mdlDelete_item').html(noFaktur)
        $('#mdlDelete_itemId').val(id)
    }
    $('#tableTransaction tbody').on('click', '.mdlView', function() {
        const src = $(this).data("src")
        $('#mdlView_src').attr('src', src);
    })
    const generate = (id, noFaktur) => {
        $('#mdlGenerate_item').html(noFaktur)
        $('#mdlGenerate_itemId').val(id)
    }
    $('#downloadMultiple').click(function() {
        const dnIds = $('.checkItem:checkbox:checked').map((_, elm) => elm.value).get()
        $('#mdlGenerateMulti_count').html(dnIds.length)
        $('#mdlGenerateMulti_itemId').val(dnIds.toString())

    })
    $('#deleteMultiple').click(function() {
        const dnIds = $('.checkItem:checkbox:checked').map((_, elm) => elm.value).get()
        $('#mdlDeleteMulti_count').html(dnIds.length)
        $('#mdlDeleteMulti_itemId').val(dnIds.toString())

    })
    $('#checkAll').change(function() {
        const isChecked = $(this).prop('checked')
        if (isChecked) {
            $('.checkItem').prop('checked', true)
        } else {
            $('.checkItem').prop('checked', false)
        }
        buttonMultipleAvailable()
    })
    $('.checkItem').change(function() {
        buttonMultipleAvailable()
    })
    const buttonMultipleAvailable = () => {
        const isChecked = $('.checkItem:checkbox:checked').prop('checked')
        if (isChecked) {
            $('#downloadMultiple').attr('disabled', false)
            $('#deleteMultiple').attr('disabled', false)
        } else {
            $('#downloadMultiple').attr('disabled', true)
            $('#deleteMultiple').attr('disabled', true)
        }
    }
</script>