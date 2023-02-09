<?php defined('BASEPATH') or exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;

class TransactionGa2 extends RestController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper("url");
        $this->load->model('TransactionApproval', 'M_TransApproval');
    }

    public function index_get()
    {
        $param = $this->get();
        if (!empty($param['username'])) {
            $user = $this->M_TransApproval->getUser($param['username']);            
            $dataContract = $this->M_TransApproval->get_v_trans($user->ROLE_USERS, $param['limit']);

            $this->response([
                'status' => true,
                'message' => 'Success get List Contract',
                'data' => $dataContract
            ], 200);
        } else {
            $this->response(['status' => false, 'message' => 'Parameter tidak cocok'], 200);
        }
    }

    public function detail_get()
    {
        $param = $this->get();
        if (!empty($param['id_trans'])) {
            $user = $this->M_TransApproval->getUser($param['username']);
            $dataContract = $this->M_TransApproval->get_detail_trans($param['id_trans']);
            $dataProject = $this->M_TransApproval->get_master_project($dataContract->PROJECT_NO);
            $fileTor = $this->M_TransApproval->get_tor_file($dataContract->PROJECT_NO);
            $fileTender = $this->M_TransApproval->get_tender_file($dataContract->PROJECT_NO);

            $posisi = 0;
            if ($dataProject->project_stat == 1) {
                $posisi++;
                if ($dataProject->project_stat_app == 1) {
                    $posisi++;
                    if ($dataProject->project_stat_submit == 1) {
                        $posisi++;
                        if ($dataProject->project_stat_monitoring == 1) {
                            $posisi++;
                        } else {
                            $posisi += 0;
                        }
                    } else {
                        $posisi += 0;
                    }
                } else {
                    $posisi += 0;
                }
            } else {
                $posisi += 0;
            }
            $status = $this->M_TransApproval->get_status_approve($param['id_trans'], $user->ROLE_USERS, $user->ID_USERS);
            $detailTrans = array(
                "POSISI_STEP_BAR" => $posisi,
                "AREA" => $dataProject->AREA,
                "PROJECT_NAME" => $dataProject->NAME_PROJ,
                "PROJECT_STATUS" => $dataProject->STATUS_PROJ,
                "PROJECT_TIPE" => $dataProject->TYPE_PROJ,
                "KATEGORI" => $dataProject->CATEGORY_PROJ,
                "VENDOR" => $dataProject->VENDOR,
                "NILAI_PROJECT" => $dataProject->PROJECT_VALUE,
                "NILAI_SI" => $dataProject->SI_VALUE,
                "FILES" => [
                    [
                        "KATEGORI" => "design & development",
                        "DETAIL_FILES" => $fileTor,
                    ],
                    [
                        "KATEGORI" => "tender",
                        "DETAIL_FILES" => $fileTender
                    ]
                ],
                "URL_KONTRAK" => $dataContract->PATH_FILE,
                "STATUS_TRANS" => $status->STATUS_TRANS
            );

            $this->response([
                'status' => true,
                'message' => 'Success',
                'data' => $detailTrans
            ], 200);
        } else {
            $this->response(['status' => false, 'message' => 'Parameter tidak cocok'], 200);
        }
    }

    public function confirm_put()
    {
        $param = $this->put();
        if (!empty($param['username']) && !empty($param['id_trans'])) {
            $transApproval = $this->M_TransApproval->get_trans_approval($param['id_trans']);
            $user = $this->M_TransApproval->getUser($param['username']);
            $detailApproval = $this->M_TransApproval->get_detail_approval($param['id_trans']);

            $confirmState = "";
            $CompleteApprove = count($detailApproval);
            foreach ($detailApproval as $item) {
                if (empty($item->approval_detail_date)) {
                    $confirmState = $item->approval_detail_role;
                    $CompleteApprove--;
                }
            }

            if ($transApproval->approval_status != 2) {

                if ($transApproval->approval_stat_propose == 1) {
                    if ($transApproval->approval_status != 3) {
                        if ($confirmState == $user->ROLE_USERS) {
                            $dataDetail = array(
                                'approval_id' => $param['id_trans'],
                                'approval_detail_userid' => $user->ID_USERS,
                                'approval_detail_stat_approve' => $param['is_approve'],
                                'approval_detail_date' => date('Y-m-d H:i:s'),
                                'approval_detail_desc' => $param['keterangan']
                            );
                            $this->M_TransApproval->update_detail_approval($dataDetail, $user->ROLE_USERS);

                            if ($param['is_approve'] != 1) {
                                $this->M_TransApproval->update_trans_approval([
                                    'approval_id' => $param['id_trans'],
                                    'approval_status' => 3,
                                    'approval_desc' => $param['keterangan']
                                ]);
                                $this->response(['status' => true, 'message' => 'Reject project success'], 200);
                            } else {
                                if ($CompleteApprove == (count($detailApproval) - 1)) {
                                    $dataTransApprove = array(
                                        'approval_id' => $param['id_trans'],
                                        'approval_flag' => $transApproval->approval_flag + 1,
                                        'approval_status' => 2
                                    );

                                    
                                } else {
                                    $dataTransApprove = array(
                                        'approval_id' => $param['id_trans'],
                                        'approval_flag' => $transApproval->approval_flag + 1
                                    );
                                }
                                $this->M_TransApproval->update_trans_approval($dataTransApprove);
                                $this->response(['status' => true, 'message' => 'Approve project success'], 200);
                            }
                        } else {
                            $this->response(['status' => false, 'message' => 'Anda memerlukan approve ' . $confirmState . ' terlebih dahulu'], 200);
                        }
                    } else {
                        (empty($transApproval->approval_desc)) ? $this->response(['status' => false, 'message' => 'Project ditolak'], 200) : $this->response(['status' => false, 'message' => 'Project ditolak dengan alasan ' . $transApproval->approval_desc], 200);
                    }
                } else {
                    $this->response(['status' => false, 'message' => 'Project belum diajukan'], 200);
                }
            } else {
                $this->response(['status' => false, 'message' => 'Project sudah di approve semua'], 200);
            }
        } else {
            $this->response(['status' => false, 'message' => 'Parameter tidak cocok'], 200);
        }
    }
}
