<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Debit Note (Overdue)</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="d-sm-flex align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-warning mb-2">Daftar Debit Note (Overdue)</h6>
                <div>
                    <button class="btn btn-sm btn-success shadow-sm" id="finishMultiple" data-toggle="modal" data-target="#mdlFinishMulti" disabled>
                        <i class="fas fa-check"></i>
                        Finish Multiple
                    </button>
                    <button class="btn btn-sm btn-info shadow-sm" id="downloadMultiple" data-toggle="modal" data-target="#mdlDownloadMulti" disabled>
                        <i class="fas fa-download"></i>
                        Download Multiple
                    </button>
                    <button class="btn btn-sm btn-danger shadow-sm" id="reverseMultiple" data-toggle="modal" data-target="#mdlReverseMulti" disabled>
                        <i class="fas fa-undo"></i>
                        Reverse Multiple
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
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
<!-- Modal Finish -->
<div class="modal fade" id="mdlFinish" tabindex="-1" aria-labelledby="mdlFinish" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mdlApprove">Finish Debitnote?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    Anda akan mevalidasi item ?
                </p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <form action="<?= site_url('debitnote/finish') ?>" method="post">
                    <input type="hidden" id="mdlFinish_id" name="ID_DEBITNOTE" />
                    <input type="hidden" id="mdlFinish_id" name="STAT_DEBITNOTE" value="6" />
                    <input type="hidden" name="page" value="overdue" />
                    <button type="submit" class="btn btn-warning">Selesai</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Modal Download -->
<div class="modal fade" id="mdlDownload" tabindex="-1" aria-labelledby="mdlDownload" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mdlDownload">Download Debit Note?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    Anda akan mengunduh Debit Note dengan No. Faktur <span id="mdlDownload_item"></span> ?
                </p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <form action="<?= site_url('debitnote/downloadPdf') ?>" id="formDownload" method="post">
                    <input type="hidden" id="mdlDownload_id" name="PATH_DEBITNOTE" />
                    <button type="submit" class="btn btn-success" onclick="submit_form();", data-dismiss="modal">Download</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Modal Download Multiple -->
<div class="modal fade" id="mdlDownloadMulti" tabindex="-1" aria-labelledby="mdlGenerate" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mdlGenerate">Download Debit Note?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    Anda akan men-download <span id="mdlDownloadMulti_count"></span> debitnote
                </p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <form action="<?= site_url('debitnote/downloadMultiDN') ?>" method="post">
                    <input type="hidden" id="mdlDownloadMulti_itemId" name="ID_DEBITNOTE" />
                    <button type="submit" class="btn btn-info">Download</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Modal Finish Multiple -->
<div class="modal fade" id="mdlFinishMulti" tabindex="-1" aria-labelledby="mdlGenerate" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mdlGenerate">Finish Debit Note?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    Anda akan mevalidasi <span id="mdlFinishMulti_count"></span> debitnote
                </p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <form action="<?= site_url('debitnote/finishMulti') ?>" method="post">
                    <input type="hidden" id="mdlFinishMulti_itemId" name="ID_DEBITNOTE" />
                    <input type="hidden" id="" name="page" value="overdue" />
                    <button type="submit" class="btn btn-success">Finish</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Modal Reverse -->
<div class="modal fade" id="mdlReverse" tabindex="-1" aria-labelledby="mdlReverse" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mdlReject">Reverse Debit Note?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    Anda akan me reverse Debit Note dengan No. Faktur <span id="mdlReverse_item"></span> ?
                </p>
                <form action="<?= site_url('debitnote/reverseDN') ?>" method="post">
                    <div class="form-group">
                        <label>Catatan</label>
                        <textarea class="form-control" name="CATATANREVERSE_DEBITNOTE" required></textarea>
                    </div>
            </div>
            <div class="modal-footer">
                <input type="hidden" id="mdlReverse_itemId" name="ID_DEBITNOTE" />
                <input type="hidden" id="" name="page" value="overdue" />
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger">Reverse</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Modal Reverse Multiple -->
<div class="modal fade" id="mdlReverseMulti" tabindex="-1" aria-labelledby="mdlReverse" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mdlGenerate">Reverse Debit Note?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    Anda akan men-reverse <span id="mdlReverseMulti_count"></span> debitnote
                </p>
                <form action="<?= site_url('debitnote/reverseMultiDN') ?>" method="post">
                    <div class="form-group">
                        <label>Catatan</label>
                        <textarea class="form-control" name="CATATANREVERSE_DEBITNOTE" required></textarea>
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <input type="hidden" id="mdlReverseMulti_itemId" name="ID_DEBITNOTE" />
                <input type="hidden" id="" name="page" value="overdue" />
                <button type="submit" class="btn btn-danger">Reverse</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Modal View PDF -->
<div class="modal fade" id="mdlView" tabindex="-1" aria-labelledby="mdlView" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mdlReject">Detail Debit Note</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <iframe id="mdlView_src" src="" frameborder="0" width="100%" height="500px"></iframe>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/vendor/jquery/jquery.min.js'); ?>"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js" defer></script>
<script>
    function submit_form() {
      document.getElementById('formDownload').submit();
    }
</script>
<script>
    $(document).ready(function() {
        $('#tableDN').DataTable({
            'processing': true,
            'serverSide': true,
            'ordering': false,
            'serverMethod': 'post',
            'ajax': {
                'url':'<?= site_url('debitnote/ajxGetData')?>',
                'data': {status: '5'}
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
    const finish = id => {
        $('#mdlFinish_id').val(id)
    }
    const reverse = (id, noFaktur) => {
        $('#mdlReverse_item').html(noFaktur)
        $('#mdlReverse_itemId').val(id)
    }
    const download = (src, noFaktur) => {
        $('#mdlDownload_item').html(noFaktur)
        $('#mdlDownload_id').val(src)
    }
    const view = src => {
        $('#mdlView_src').attr('src', src);
    }
    
    $('#downloadMultiple').click(function() {
        const dnIds = $('.checkItem:checkbox:checked').map((_, elm) => elm.value).get()
        $('#mdlDownloadMulti_count').html(dnIds.length)
        $('#mdlDownloadMulti_itemId').val(dnIds.toString())

    })
    $('#finishMultiple').click(function() {
        const dnIds = $('.checkItem:checkbox:checked').map((_, elm) => elm.value).get()
        $('#mdlFinishMulti_count').html(dnIds.length)
        $('#mdlFinishMulti_itemId').val(dnIds.toString())

    })
    $('#reverseMultiple').click(function() {
        const dnIds = $('.checkItem:checkbox:checked').map((_, elm) => elm.value).get()
        $('#mdlReverseMulti_count').html(dnIds.length)
        $('#mdlReverseMulti_itemId').val(dnIds.toString())

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
            $('#finishMultiple').attr('disabled', false)
            $('#reverseMultiple').attr('disabled', false)
        } else {
            $('#downloadMultiple').attr('disabled', true)
            $('#finishMultiple').attr('disabled', true)
            $('#reverseMultiple').attr('disabled', true)
        }
    }
</script>