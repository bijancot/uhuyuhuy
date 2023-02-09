<?php defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Approval extends RestController {
    
    public function __construct() {
        parent::__construct();
        $this->load->helper("url");
        $this->load->library(array('upload', 'image_lib'));
    }

    public function index_get(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->get();
            // Validation
            if(empty($param['limit'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            if(!empty($jwt->user_no)){
                $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
                if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
                if($jwt->user_role == 'Admin PICP'){
                    $notifs = $this->getNotifPICP($jwt->user_no, $param['limit'], $param['page']);
                    $notifs = $this->getNotifAdminPICP($param['limit'], $param['page']);
                }else{
                    $this->response(['status' => false, 'message' => 'Anda tidak memilik hak akses!']);
                }
            }else if(!empty($jwt->vendor_no)){
                $vendor = $DB2->get_where('master_vendor', ['vendor_no' => $jwt->vendor_no])->row();
                if($jwt->vendor_session != $vendor->vendor_session || $vendor->vendor_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
                $notifs = $this->getNotifVendor($jwt->vendor_no, $param['limit'], $param['page']);
            }else{
                $this->response(['status' => false, 'message' => 'Login terlebih dahulu!']);
            }

            
            if($notifs != null){
                $this->response(['status' => true, 'message' => 'Data berhasil ditemukan!', 'data' => $notifs], 200);
            }else{
                $this->response(['status' => false, 'message' => 'Data tidak ditemukan!'], 200);
            }
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
}