<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Debit Note (Finished)</h1>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <div class="d-sm-flex align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-warning mb-2">Daftar Debit Note (Finished)</h6>
                <div>
                    <button class="btn btn-sm btn-info shadow-sm" id="downloadMultiple" data-toggle="modal" data-target="#mdlDownloadMulti" disabled>
                        <i class="fas fa-download"></i>
                        Download Multiple
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
<!-- Modal Approve -->
<div class="modal fade" id="mdlApprove" tabindex="-1" aria-labelledby="mdlApprove" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mdlApprove">Verifikasi Transaksi?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>
                    Anda akan menyetujui item ?
                </p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <form action="<?= site_url('transaction/approve') ?>" method="post">
                    <input type="hidden" id="mdlApprove_id" name="ID_TRANS" />
                    <button type="submit" class="btn btn-warning">Verifikasi</button>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Modal Reject -->
<div class="modal fade" id="mdlReject" tabindex="-1" aria-labelledby="mdlReject" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mdlReject">Tolak Transaksi?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="<?= site_url('transaction/reject') ?>" method="post">
                    <div class="form-group">
                        <label for="inputKeterangan">Keterangan</label>
                        <textarea name="KETERANGAN_TRANS" class="form-control" required></textarea>
                    </div>
            </div>

            <div class="modal-footer">
                <input type="hidden" id="mdlReject_id" name="ID_TRANS" />
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-danger">Tolak</button>
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
                <h5 class="modal-title" id="mdlReject">Detail Transaksi</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <iframe id="mdlView_src" src="" frameborder="0" width="100%" height="500px"></iframe>
            </div>

            <div class="modal-footer">
                <input type="hidden" id="mdlReject_id" name="ID_TRANS" />
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
                'data': {status: '6'}
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
    $('#tableTransaction tbody').on('click', '.mdlReject', function() {
        const id = $(this).data("id")
        $('#mdlReject_id').val(id)
    })
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
    $('#checkAll').change(function() {
        const isChecked = $(this).prop('checked')
        if (isChecked) {
            $('.checkItem').prop('checked', true)
        } else {
            $('.checkItem').prop('checked', false)
        }
        buttonMultipleAvailable()
    })
    // $('.checkItem').change(function() {
    //     buttonMultipleAvailable()
    // })
    const buttonMultipleAvailable = () => {
        const isChecked = $('.checkItem:checkbox:checked').prop('checked')
        if (isChecked)
            $('#downloadMultiple').attr('disabled', false)
        else
            $('#downloadMultiple').attr('disabled', true)
    }
</script>