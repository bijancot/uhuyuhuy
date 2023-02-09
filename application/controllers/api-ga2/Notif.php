<?php defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Notif extends RestController {
    
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
                if($jwt->user_role != 'Admin PICP'){
                    $notifs = $this->getNotifPICP($jwt->user_no, $param['limit'], $param['page']);
                }else{
                    $notifs = $this->getNotifAdminPICP($param['limit'], $param['page']);
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
    public function read_put(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->put();
            // Validation
            if(!empty($jwt->user_no)){
                $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
                if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
                if($jwt->user_role != 'Admin PICP'){
                    $this->updateNotifPICP($jwt->user_no);
                }else{
                    $this->updateNotifAdminPICP();
                }
            }else if(!empty($jwt->vendor_no)){
                $vendor = $DB2->get_where('master_vendor', ['vendor_no' => $jwt->vendor_no])->row();
                if($jwt->vendor_session != $vendor->vendor_session || $vendor->vendor_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
                $this->updateNotifVendor($jwt->vendor_no);
            }else{
                $this->response(['status' => false, 'message' => 'Login terlebih dahulu!']);
            }

            
            $this->response(['status' => true, 'message' => 'Berhasil mengubah status notif!'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function getNotifVendor($vendorNo, $limit, $page){
        $DB2    = $this->load->database('gaSys2', true);
        $notifs = $DB2->query("
            SELECT 
                tn.notif_id ,
                tn.project_no ,
                tn.notif_type ,
                tn.notif_from_role ,
                tn.notif_from_name ,
                tn.notif_seen_vendor as is_seen ,
                tn.notif_date 
            FROM transaction_notif tn 
            WHERE 
                tn.notif_for = 'Vendor'
                AND tn.project_no IN (
                        SELECT project_no  
                        FROM master_project
                        WHERE vendor_no = '".$vendorNo."' 
                    ) 
            ORDER BY tn.notif_date DESC
            LIMIT ".$limit."
            OFFSET ".(($page-1) * $limit)."
        ")->result();
        
        $unReads = $DB2->query("
        SELECT 
            COUNT(tn.notif_id) as notif_total
            FROM transaction_notif tn 
            WHERE 
                tn.notif_for = 'Vendor'
                AND tn.project_no IN (
                        SELECT project_no  
                        FROM master_project
                        WHERE vendor_no = '".$vendorNo."' 
                    ) 
                AND tn.notif_seen_vendor = '0'
        ")->row()->notif_total;

        $allData = $DB2->query("
        SELECT 
            COUNT(tn.notif_id) as notif_total
            FROM transaction_notif tn 
            WHERE 
                tn.notif_for = 'Vendor'
                AND tn.project_no IN (
                        SELECT project_no  
                        FROM master_project
                        WHERE vendor_no = '".$vendorNo."' 
                    ) 
        ")->row()->notif_total;
        return ['pagination' => ['limit' => $limit, 'page' => $page, 'total_page' => (string)(ceil($allData / $limit)), 'total_data' => $allData, 'un_reads' => $unReads], 'notifs' => $notifs];
    }
    public function getNotifPICP($userNo, $limit, $page){
        $DB2    = $this->load->database('gaSys2', true);
        $notifs = $DB2->query("
            SELECT 
                tn.notif_id ,
                tn.project_no ,
                tn.notif_type ,
                tn.notif_from_role ,
                tn.notif_from_name ,
                tn.notif_seen_pic as is_seen ,
                tn.notif_date 
            FROM transaction_notif tn 
            WHERE 
                (
                    tn.notif_for = 'Admin/PICP'
                    OR tn.notif_for = 'PICP'
                )
                AND tn.project_no IN (
                        SELECT project_no  
                        FROM master_project
                        WHERE project_pic = '".$userNo."' 
                    ) 
            ORDER BY tn.notif_date DESC
            LIMIT ".$limit."
            OFFSET ".(($page-1) * $limit)."
        ")->result();
        
        $unReads = $DB2->query("
        SELECT 
            COUNT(tn.notif_id) as notif_total
            FROM transaction_notif tn 
            WHERE 
                (
                    tn.notif_for = 'Admin/PICP'
                    OR tn.notif_for = 'PICP'
                )
                AND tn.project_no IN (
                        SELECT project_no  
                        FROM master_project
                        WHERE project_pic = '".$userNo."' 
                    ) 
                AND tn.notif_seen_pic = '0'
        ")->row()->notif_total;

        $allData = $DB2->query("
        SELECT 
            COUNT(tn.notif_id) as notif_total
            FROM transaction_notif tn 
            WHERE 
                (
                    tn.notif_for = 'Admin/PICP'
                    OR tn.notif_for = 'PICP'
                )
                AND tn.project_no IN (
                        SELECT project_no  
                        FROM master_project
                        WHERE project_pic = '".$userNo."' 
                    ) 
        ")->row()->notif_total;

        return ['pagination' => ['limit' => $limit, 'page' => $page, 'total_page' => (string)(ceil($allData / $limit)), 'total_data' => $allData, 'un_reads' => $unReads], 'notifs' => $notifs];
    }
    public function getNotifAdminPICP($limit, $page){
        $DB2    = $this->load->database('gaSys2', true);
        $notifs = $DB2->query("
            SELECT 
                tn.notif_id ,
                tn.project_no ,
                tn.notif_type ,
                tn.notif_from_role ,
                tn.notif_from_name ,
                tn.notif_seen_admin as is_seen ,
                tn.notif_date 
            FROM transaction_notif tn 
            WHERE 
                tn.notif_for = 'Admin/PICP'
            ORDER BY tn.notif_date DESC
            LIMIT ".$limit."
            OFFSET ".(($page-1) * $limit)."
        ")->result();
        
        $unReads = $DB2->query("
        SELECT 
            COUNT(tn.notif_id) as notif_total
            FROM transaction_notif tn 
            WHERE 
                tn.notif_for = 'Admin/PICP'
                AND tn.notif_seen_admin = '0'
        ")->row()->notif_total;

        $allData = $DB2->query("
        SELECT 
            COUNT(tn.notif_id) as notif_total
            FROM transaction_notif tn 
            WHERE 
                tn.notif_for = 'Admin/PICP'
        ")->row()->notif_total;

        return ['pagination' => ['limit' => $limit, 'page' => $page, 'total_page' => (string)(ceil($allData / $limit)), 'total_data' => $allData, 'un_reads' => $unReads], 'notifs' => $notifs];
    }
    public function updateNotifVendor($vendorNo){
        $DB2    = $this->load->database('gaSys2', true);
        $DB2->query("
            UPDATE transaction_notif 
            SET notif_seen_vendor = '1'
            WHERE 
                notif_for = 'Vendor'
                AND project_no IN 
                    (
                        SELECT project_no  
                        FROM master_project
                        WHERE vendor_no = '".$vendorNo."' 
                    ) 
        ");
    }
    public function updateNotifPICP($userNo){
        $DB2    = $this->load->database('gaSys2', true);
        $DB2->query("
            UPDATE transaction_notif 
            SET notif_seen_pic = '1'
            WHERE 
                notif_for = 'Admin/PICP'
                AND project_no IN 
                    (
                        SELECT project_no  
                        FROM master_project
                        WHERE project_pic = '".$userNo."' 
                    ) 
        ");
    }
    public function updateNotifAdminPICP(){
        $DB2    = $this->load->database('gaSys2', true);
        $DB2->query("
            UPDATE transaction_notif 
            SET notif_seen_admin = '1'
            WHERE 
                notif_for = 'Admin/PICP'
        ");
    }
}