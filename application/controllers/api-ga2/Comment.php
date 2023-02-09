<?php defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Comment extends RestController {
    
    public function __construct() {
        parent::__construct();
        $this->load->helper("url");
        $this->load->library(array('upload', 'image_lib'));
    }

    public function index_post(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->post();
            // Validation
            if(empty($param['projectNo']) || empty($param['userRole']) || empty($param['content'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            $project = $DB2->get_where('master_project', ['project_no' => $param['projectNo']])->result();
            if($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);

            if($param['userRole'] == '1'){
                $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
                if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
                if($jwt->user_role != 'Admin PICP'){
                    if($project[0]->project_pic != $jwt->user_no) $this->response(['status' => false, 'message' => 'Anda tidak memiliki akses pada project ini!'], 200);
                }
            }else if($param['userRole'] == '2'){
                $vendor = $DB2->get_where('master_vendor', ['vendor_no' => $jwt->vendor_no])->row();
                if($jwt->vendor_session != $vendor->vendor_session || $vendor->vendor_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
                if($project[0]->vendor_no != $jwt->vendor_no) $this->response(['status' => false, 'message' => 'Anda tidak memiliki akses pada project ini!'], 200);
            }
            
            
            // End Validation
            $currDate = date('Y-m-d H:i:s');
            $formData['project_no']         = $param['projectNo'];
            $formData['comment_content']    = $param['content'];
            $formData['comment_date']       = $currDate;
            $formData['comment_id_user']    = $param['userRole'] == '1' ? $jwt->user_no : $jwt->vendor_no;
            $formData['comment_role']       = $param['userRole'] == '1' ? $jwt->user_role : "Vendor";
            $formData['comment_name']       = $param['userRole'] == '1' ? $jwt->user_name : $jwt->vendor_name;
            $DB2->insert('transaction_comment', $formData);

            $formNotif['project_no']        = $param['projectNo'];
            $formNotif['notif_content']     = "Terdapat pesan baru pada project ".$param['projectNo'];
            $formNotif['notif_date']        = $currDate;
            $formNotif['notif_from_role']   = $param['userRole'] == '1' ? $jwt->user_role : "Vendor";
            $formNotif['notif_from_name']   = $param['userRole'] == '1' ? $jwt->user_name : $jwt->vendor_name;
            $formNotif['notif_for']         = $param['userRole'] == '1' ? "Vendor" : "Admin/PICP";
            $formNotif['notif_type']        = "COMMENT";
            $DB2->insert('transaction_notif', $formNotif);

            $this->response(['status' => true, 'message' => 'Berhasil mengrim comment!'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function index_get(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->get();
            // Validation
            if(empty($param['projectNo']) || empty($param['limit'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            $project = $DB2->get_where('master_project', ['project_no' => $param['projectNo']])->result();
            if($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);

            if(!empty($jwt->user_no)){
                $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
                if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
                if($jwt->user_role != 'Admin PICP'){
                    if($project[0]->project_pic != $jwt->user_no) $this->response(['status' => false, 'message' => 'Anda tidak memiliki akses pada project ini!'], 200);
                }
            }else if(!empty($jwt->vendor_no)){
                $vendor = $DB2->get_where('master_vendor', ['vendor_no' => $jwt->vendor_no])->row();
                if($jwt->vendor_session != $vendor->vendor_session || $vendor->vendor_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
                if($project[0]->vendor_no != $jwt->vendor_no) $this->response(['status' => false, 'message' => 'Anda tidak memiliki akses pada project ini!'], 200);
            }else{
                $this->response(['status' => false, 'message' => 'Login terlebih dahulu!']);
            }
            // End Validation
            
            $projects = $DB2->order_by('comment_date', 'DESC')->get_where('transaction_comment', ['project_no' => $param['projectNo']], $param['limit'])->result();
            if($projects != null){
                $this->response(['status' => true, 'message' => 'Data berhasil ditemukan!', 'data' => $projects], 200);
            }else{
                $this->response(['status' => false, 'message' => 'Data tidak ditemukan!'], 200);
            }
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
}