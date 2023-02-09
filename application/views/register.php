<div class="bg-image-ut">
    <div class="container">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center min-vh-100">
            <div class="my-4">
                <img src="<?= base_url('assets/img/landing/motto_ut.png') ?>" width="300" alt="Motto UT">
                <h5 class="text-white mt-4">
                    The strength of the company <br>
                    lies on the quality of its products and services, <br>
                    the best solutions offered, and good <br>
                    relationship with customers.
                </h5>
            </div>
            <div class="card p-3 mb-4">
                <div class="card-body">
                    <h2 class="card-title mb-3">Register</h2>
                    <?php
                        if ($this->session->flashdata('error') != '') {
                            echo '<div class="alert alert-danger" role="alert">';
                            echo $this->session->flashdata('error');
                            echo '</div>';
                        }
                    ?>
                    <?php
                        if ($this->session->flashdata('success_register') != '') {
                            echo '<div class="alert alert-info" role="alert">';
                            echo $this->session->flashdata('success_register');
                            echo '</div>';
                        }
                    ?>
                    <form action="<?= site_url('user/register') ?>" enctype="multipart/form-data" method="post">
                        <div class="form-group">
                            <input type="text" class="form-control inputanAlpha" placeholder="Nama Lengkap" name="NAMA_USERS" required onkeypress="return alpha(event)">
                        </div>
                        <div class="form-group">
                            <input type="text" class="form-control inputanNum" name="USER_USERS" placeholder="NRP" required onkeypress="return num(event)">
                        </div>
                        <div class="form-group">
                            <input type="tel" class="form-control inputanNum" name="NOTELP_USERS" placeholder="Telepon" required onkeypress="return num(event)">
                        </div>
                        <div class="form-group">
                            <select class="custom-select" name="DEPT_USERS" required>
                                <option value="" selected>Departement</option>
                                <option value="General Affairs">General Affairs</option>
                                <option value="Others">Others</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <select class="custom-select" name="DIV_USERS" required>
                                <option value="" selected>Divisi</option>
                                <option value="Corporate Finance">Corporate Finance</option>
                                <option value="Corporate Human Capital & Corpu">Corporate Human Capital & Corpu</option>
                                <option value="Corporate Governance & Sustainability">Corporate Governance & Sustainability</option>
                                <option value="Procurement and Investment">Procurement and Investment</option>
                                <option value="Corporate Audit">Corporate Audit</option>
                                <option value="Group Legal">Group Legal</option>
                                <option value="Digitalization and Differentation">Digitalization and Differentation</option>
                                <option value="Cosporate Strategy and Technology">Cosporate Strategy and Technology</option>
                                <option value="Service Division">Service Division</option>
                                <option value="Parts Division">Parts Division</option>
                                <option value="Truck Mining Operation">Truck Mining Operation</option>
                                <option value="Sales Operation Division">Sales Operation Division</option>
                                <option value="Truck Sales Operation">Truck Sales Operation</option>
                                <option value="Marketing Division">Marketing Division</option>
                                <option value="Board of Direction">Board of Direction</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <input type="password" class="form-control inputanAlphaNum" name="PASS_USERS" placeholder="Password" onkeypress="return alphaNum(event)">
                        </div>
                        <div class="form-group">
                            <div class="custom-file">
                                <input type="file" name="imageTtd" class="custom-file-input" allow id="image-source" onchange="previewImage();" accept=".jpg,.png,.jpeg,.bmp">
                                <label class="custom-file-label" for="image-source">Upload Signature</label>
                            </div>
                        </div>
                        <input type="hidden" name="STAT_USERS" value="0" />
                        <div class="mb-2">
                            <button class="btn btn-warning btn-block" type="submit">Register</button>
                        </div>
                        <a class="text-secondary" href="<?= base_url(); ?>">Kembali ke halaman utama</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/vendor/jquery/jquery.min.js'); ?>"></script>
<script>
    $(".custom-file-input").on("change", function() {
        var fileName = $(this).val().split("\\").pop();
        $(this).siblings(".custom-file-label").addClass("selected").html(fileName);
    });
    function num(evt) {
        evt = (evt) ? evt : window.event;
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            return false;
        }
        return true;
    }
    function alphaNum(e) {
        var k;
        document.all ? k = e.keyCode : k = e.which;
        return ((k > 64 && k < 91) || (k > 96 && k < 123) || k == 8 || k == 32 || (k >= 48 && k <= 57));
    }
    function alpha(e) {
        var k;
        document.all ? k = e.keyCode : k = e.which;
        return ((k > 64 && k < 91) || (k > 96 && k < 123) || k == 8 || k == 32);
    }
    $('.inputanAlphaNum').change(function(){
        let val = $(this).val();
        $(this).val(val.replace(/[^a-zA-Z 0-9]/g, ""));
    })
    $('.inputanAlpha').change(function(){
        let val = $(this).val();
        $(this).val(val.replace(/[^a-zA-Z]/g, ""));
    })
    $('.inputanNum').change(function(){
        let val = $(this).val();
        $(this).val(val.replace(/[^0-9]/g, ""));
    })
</script>