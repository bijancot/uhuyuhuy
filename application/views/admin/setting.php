<!-- Begin Page Content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Setting</h1>
    </div>

    <!-- Content Row -->
    <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
        <li class="nav-item col-sm-4" role="presentation">
            <a class="nav-link active" id="pills-role-tab" data-toggle="pill" href="#pills-role" role="tab" aria-controls="pills-role" aria-selected="true" style="text-align: center;">Role</a>
        </li>
        <li class="nav-item col-sm-4" role="presentation">
            <a class="nav-link" id="pills-user-role-tab" data-toggle="pill" href="#pills-user-role" role="tab" aria-controls="pills-user-role" aria-selected="false" style="text-align: center;">User Role</a>
        </li>
        <li class="nav-item col-sm-4" role="presentation">
            <a class="nav-link" id="pills-master-flow-tab" data-toggle="pill" href="#pills-master-flow" role="tab" aria-controls="pills-master-flow" aria-selected="false" style="text-align: center;">Master Flow</a>
        </li>
    </ul>
    <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active" id="pills-role" role="tabpanel" aria-labelledby="pills-role-tab">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <div class="d-sm-flex align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-warning">Role Table</h6>
                        <button class="d-none d-sm-inline-block btn btn-sm btn-warning shadow-sm" data-toggle="modal" data-target="#roleTableModal">
                            <i class="fas fa-plus fa-sm text-white-50"></i>
                            Add
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="table-role" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Role Name</th>
                                    <th>Parent Role</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>Id</th>
                                    <th>Role Name</th>
                                    <th>Parent Role</th>
                                    <th>Action</th>
                                </tr>
                            </tfoot>
                            <tbody>
                                <tr>
                                    <td>...</td>
                                    <td>...</td>
                                    <td>...</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-primary">Edit</button>
                                            <button type="button" class="btn btn-danger">Hapus</button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="pills-user-role" role="tabpanel" aria-labelledby="pills-user-role-tab">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <div class="d-sm-flex align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-warning">User Role Table</h6>
                        <button class="d-none d-sm-inline-block btn btn-sm btn-warning shadow-sm" data-toggle="modal" data-target="#userRoleTableModal">
                            <i class="fas fa-plus fa-sm text-white-50"></i>
                            Add
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="table-user-role" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>User</th>
                                    <th>Role Name</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>Id</th>
                                    <th>User</th>
                                    <th>Role Name</th>
                                </tr>
                            </tfoot>
                            <tbody>
                                <tr>
                                    <td>...</td>
                                    <td>...</td>
                                    <td>...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="pills-master-flow" role="tabpanel" aria-labelledby="pills-master-flow-tab">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <div class="d-sm-flex align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-warning">Master Flow Table</h6>
                        <button class="d-none d-sm-inline-block btn btn-sm btn-warning shadow-sm" data-toggle="modal" data-target="#masterFlowTableModal">
                            <i class="fas fa-plus fa-sm text-white-50"></i>
                            Add
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="table-master-flow" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Master Approval Name</th>
                                    <th>Next Role</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tfoot>
                                <tr>
                                    <th>Id</th>
                                    <th>Master Approval Name</th>
                                    <th>Next Role</th>
                                    <th>Action</th>
                                </tr>
                            </tfoot>
                            <tbody>
                                <tr>
                                    <td>...</td>
                                    <td>...</td>
                                    <td>...</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <button type="button" class="btn btn-primary">Edit</button>
                                            <button type="button" class="btn btn-danger">Hapus</button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="tab-pane fade" id="pills-user-role" role="tabpanel" aria-labelledby="pills-user-role-tab">...</div>
        <div class="tab-pane fade" id="pills-master-flow" role="tabpanel" aria-labelledby="pills-master-flow-tab">...</div>
    </div>

</div>
<!-- /.container-fluid -->

</div>
<!-- End of Main Content -->