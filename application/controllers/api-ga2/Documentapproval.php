<?php defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
require_once(FCPATH . '/vendor/autoload.php');

class Documentapproval extends RestController {
    
    public function __construct() {
        parent::__construct();
        $this->load->helper("url");
        $this->load->library(array('upload', 'image_lib'));
    }
    public function list_post(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->post();
            $querySendVendor = null;

            // Validation
            if(empty($param['projectNo']) || empty($param['mappingId'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            $project = $DB2->get_where('master_project', ['project_no' => $param['projectNo']])->result();
            if($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);

            if(!empty($jwt->user_no)){
                $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
                if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
                if($jwt->user_role == 'PICP'){
                    if($project[0]->project_pic != $user->user_no){
                        $this->response(['status' => false, 'message' => 'Project bukan otoritas anda!'], 200);
                    }
                }
            }else if(!empty($jwt->vendor_no)){
                $vendor = $DB2->get_where('master_vendor', ['vendor_no' => $jwt->vendor_no])->row();
                if($jwt->vendor_session != $vendor->vendor_session || $vendor->vendor_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
                if($project[0]->vendor_no != $vendor->vendor_no){
                    $this->response(['status' => false, 'message' => 'Project bukan otoritas anda!'], 200);
                }
                $querySendVendor = " AND ta.approval_send_vendor = 2";
            }else{
                $this->response(['status' => false, 'message' => 'Login terlebih dahulu!']);
            }
            

            foreach ($param['mappingId'] as $mappingId) {
                $mapping = $DB2->get_where('master_mapping', ['mapping_id' => $mappingId])->result_array();
                if($mapping == null) $this->response(['status' => false, 'message' => 'Form mapping tidak ditemukan!'], 200);
            }
            // End Validation
            $listApprovals = $this->queryGetListApproval($param['projectNo'], $param['mappingId'], $querySendVendor);

            $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $listApprovals], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function confirm_post(){
        try {
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->post();
            // Validation
            if(empty($param['approvalId']) || empty($param['note'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            if(!empty($jwt->user_no)){
                $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
                if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
                if($jwt->user_role == 'Admin PICP'){
                    $role   = 'Section Head';
                    $idUser = $user->user_no;
                }else if($jwt->user_role == 'SPV'){
                    $role   = 'SPV';
                    $idUser = $user->user_no;
                }else if($jwt->user_role == 'PICP'){
                    $role   = 'PICP';
                    $idUser = $user->user_no;
                }else{
                    $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses untuk validasi dokumen!']);
                }
            }else if(!empty($jwt->vendor_no)){
                $vendor = $DB2->get_where('master_vendor', ['vendor_no' => $jwt->vendor_no])->row();
                if($jwt->vendor_session != $vendor->vendor_session || $vendor->vendor_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
                $projects = $this->queryGetCheckVendor($param['approvalId'], $vendor->vendor_no);
                if($projects == null) $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses untuk project ini'], 200);
                $role   = 'Vendor';
                $idUser = $vendor->vendor_no;
            }else{
                $this->response(['status' => false, 'message' => 'Login terlebih dahulu!']);
            }

            $approval = $this->queryGetApproval($param['approvalId'], $role);
            if($approval == null) $this->response(['status' => false, 'message' => 'Approval tidak tersedia'], 200);
            if($approval[0]->approval_stat_propose == "0") $this->response(['status' => false, 'message' => 'Dokumen belum terpropose!'], 200);

            if($approval[0]->confirm_state == $role){
                $DB2->where(['approval_id' => $param['approvalId'], 'approval_detail_role' => $role])->update('transaction_approval_detail', ['approval_detail_stat_approve' => $param['status'], 'approval_detail_userid' => $idUser, 'approval_detail_desc' => $param['note'], 'approval_detail_date' => date('Y-m-d H:i:s')]);
                if($param['status'] == '1'){
                    $flow   = $DB2->get_where('master_mapping', ['mapping_id' => $approval[0]->mapping_id])->result_array();
                    $flowWillApprove    = $approval[0]->approval_flag + 2; 

                    if(!empty($flow[0]['mapping_app_'.$flowWillApprove]) && $flow[0]['mapping_app_'.$flowWillApprove] != null){
                        $DB2->where('approval_id', $param['approvalId'])->update('transaction_approval', ['approval_flag' => (int)$approval[0]->approval_flag + 1]);
                    }else{
                        $DB2->where('approval_id', $param['approvalId'])->update('transaction_approval', ['approval_flag' => (int)$approval[0]->approval_flag + 1, 'approval_status' => '2']);
                    }
                }else if($param['status'] == '0'){
                    $DB2->where('approval_id', $param['approvalId'])->update('transaction_approval', ['approval_flag' => (int)$approval[0]->approval_flag + 1, 'approval_status' => '3']);
                }
                $this->response(['status' => true, 'message' => 'Berhasil memberikan validasi!'], 200);
            }else if($approval[0]->stat_approve != null){
                $this->response(['status' => false, 'message' => 'Anda telah memberikan validasi!'], 200);
            }else{
                $this->response(['status' => false, 'message' => 'Belum saatnya anda untuk memberikan validasi!'], 200);
            }
            
        } catch (Exception $exp) {
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function index_post(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->post();

            // Validation
            if(empty($param['projectNo']) || empty($param['formType']) || empty($_FILES['file'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "PICP" && $user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            $project = $DB2->get_where('master_project', ['project_no' => $param['projectNo']])->result();
            if($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);
            if($project[0]->project_stat_app != '1') $this->response(['status' => false, 'message' => 'Project belum disetujui!'], 200);

            if($project[0]->project_pic != $user->user_no && $user->user_role != "Admin PICP")  $this->response(['status' => false, 'message' => 'Project bukan bagian otoritas anda!'], 200);
            // End Validation

            if($param['formType'] == '1' || $param['formType'] == '2'){ // 1 == Contract; 2 == Amandemen
                $formData['mapping_id'] = $param['formType'];
                $mapping = $DB2->get_where('master_mapping', ['mapping_id' => $param['formType']])->result_array();
                if($mapping == null) $this->response(['status' => false, 'message' => 'Form mapping tidak ditemukan!'], 200);
            }else{
                $this->response(['status' => false, 'message' => 'Form type tidak cocok!'], 200);
            }

            $fileName = $mapping[0]['mapping_name'].' - '.str_replace('/', '', $param['projectNo']);
            $uploadDoc = $this->uploadDoc($fileName);
            if($uploadDoc['status'] == false) $this->response(['status' => false, 'message' => $uploadDoc['msg']], 200);
            
            $formData['approval_id']        = 'TRANS_'.md5(time()."trans");
            $formData['mapping_id']         = $param['formType'];
            $formData['project_no']         = $param['projectNo'];
            $formData['approval_path']      = $uploadDoc['link'];
            $formData['approval_filename']  = $fileName;
            $formData['approval_date']      = date('Y-m-d H:i:s');
            $DB2->insert('transaction_approval', $formData);

            for($i = 1; $i <= 7; $i++){
                if(!empty($mapping[0]['mapping_app_'.$i]) && $mapping[0]['mapping_app_'.$i] != null){
                    if($mapping[0]['mapping_app_'.$i] == 'Vendor'){
                        $DB2->where('approval_id', $formData['approval_id'])->update('transaction_approval', ['approval_send_vendor' => '1']);
                    }

                    $DB2->insert('transaction_approval_detail', ['approval_id' => $formData['approval_id'], 'approval_detail_role' => $mapping[0]['mapping_app_'.$i]]);
                }
            }

            $this->response(['status' => true, 'message' => 'Data berhasil terupload'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function sendVendor_put(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->put();

            // Validation
            if(empty($param['approvalId'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "PICP" && $user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            $approval = $DB2->get_where('transaction_approval', ['approval_id' => $param['approvalId']])->result();
            if($approval == null) $this->response(['status' => false, 'message' => 'Approval ID tidak terdaftar!'], 200);

            $project = $DB2->get_where('master_project', ['project_no' => $approval[0]->project_no])->result();
            if($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);
            if($project[0]->project_stat_app != '1') $this->response(['status' => false, 'message' => 'Project belum disetujui!'], 200);

            if($project[0]->project_pic != $user->user_no && $user->user_role != "Admin PICP")  $this->response(['status' => false, 'message' => 'Project bukan bagian otoritas anda!'], 200);
            $approval = $this->queryGetApproval($param['approvalId'], 'Vendor');
            if($approval[0]->confirm_state != 'Vendor')$this->response(['status' => false, 'message' => 'Approval belum sampai vendor !'], 200);
            // End Validation
            $DB2->where('approval_id', $param['approvalId'])->update('transaction_approval', ['approval_send_vendor' => '2']);
            
            $this->response(['status' => true, 'message' => 'Document berhasil dikirim ke vendor!'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function propose_put(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->put();

            // Validation
            if(empty($param['approvalId'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "PICP" && $user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            $approval = $DB2->get_where('transaction_approval', ['approval_id' => $param['approvalId']])->result();
            if($approval == null) $this->response(['status' => false, 'message' => 'Approval ID tidak terdaftar!'], 200);

            $project = $DB2->get_where('master_project', ['project_no' => $approval[0]->project_no])->result();
            if($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);
            if($project[0]->project_stat_app != '1') $this->response(['status' => false, 'message' => 'Project belum disetujui!'], 200);

            if($project[0]->project_pic != $user->user_no && $user->user_role != "Admin PICP")  $this->response(['status' => false, 'message' => 'Project bukan bagian otoritas anda!'], 200);
            // End Validation

            $DB2->where('approval_id', $param['approvalId'])->update('transaction_approval', ['approval_stat_propose' => '1', 'approval_status' => '1', 'approval_flag' => '1']);
            // $mapping    = $DB2->get_where('master_mapping', ['mapping_id' => $approval[0]->mapping_id])->row();
            // $notif['title']     = 'Pengajuan Baru';
            // $notif['message']   = 'Terdapat Pengajuan Form '.$transaction[0]->NAMA_FORM;
            // $notif['regisIds']  = $this->db->get_where('USERS', ['ROLE_USERS' => $mapping->mapping_app_1])->result_array();
            // $this->notification->push($notif);
            
            $this->response(['status' => true, 'message' => 'Document berhasil dipropose!'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function queryGetListApproval($projectNo, $mappingId, $querySendVendor){
        $DB2 = $this->load->database('gaSys2', true);
        $lists = $DB2->query("
            SELECT 
                ta.approval_id,
                ta.project_no , 
                mm.mapping_name ,
                mp.project_name ,
                ta.approval_stat_propose ,
                ta.approval_status ,
                ta.approval_send_vendor ,
                ta.approval_signed_path ,
                ta.approval_path,
                (
                    SELECT GROUP_CONCAT(tad.approval_detail_role) 
                    FROM transaction_approval_detail tad 
                    WHERE tad.approval_id = ta.approval_id 
                ) as approval_role,
                (
                    SELECT GROUP_CONCAT(
                        IF(tad.approval_detail_stat_approve IS NULL,'-',tad.approval_detail_stat_approve)
                    )
                    FROM transaction_approval_detail tad 
                    WHERE tad.approval_id = ta.approval_id 
                ) as approval_status
            FROM 
                transaction_approval ta ,
                master_mapping mm ,
                master_project mp 
            WHERE 
                ta.project_no = '".$projectNo."'
                ".$querySendVendor."
                AND ta.mapping_id IN(".implode(', ', $mappingId).")
                AND ta.mapping_id = mm.mapping_id
                AND ta.project_no = mp.project_no COLLATE utf8mb4_unicode_ci
        ")->result();

        $res = array();
        foreach ($lists as $list) {
            $temp['approval_id']            = $list->approval_id;
            $temp['project_no']             = $list->project_no;
            $temp['form_name']              = $list->mapping_name;
            $temp['project_name']           = $list->project_name;
            $temp['stat_propose']           = $list->approval_stat_propose;
            $temp['stat_propose']           = $list->approval_stat_propose;
            $temp['approval_send_vendor']   = $list->approval_send_vendor;
            $temp['approval_signed_path']   = $list->approval_signed_path;
            $temp['approval_path']          = $list->approval_path;
            $temp['list_approval']          = array();

            $appRole    = explode(',', $list->approval_role);
            $appStatus  = explode(',', $list->approval_status);
            for($index = 0; $index < count($appRole); $index++) {
                $temp2['role_approval'] = $appRole[$index];
                $temp2['stat_approval'] = $appStatus[$index];
                $temp['list_approval'][] = $temp2;
            }
            $res[] = $temp;
        }
        return $res;
    }
    public function queryGetCheckVendor($appId, $vendorNo){
        $DB2 = $this->load->database('gaSys2', true);
        return $DB2->query("
            SELECT mp.vendor_no 
            FROM transaction_approval ta , master_project mp 
            WHERE 
                ta.approval_id = '".$appId."'
                AND ta.project_no = mp.project_no  COLLATE utf8mb4_unicode_ci
                AND mp.vendor_no = '".$vendorNo."'
        ")->result();
    }
    public function queryGetApproval($appId, $role){
        $DB2 = $this->load->database('gaSys2', true);
        return $DB2->query("
            SELECT
                ta.approval_flag ,
                ta.mapping_id,
                ta.approval_stat_propose,
                (
                    CASE 
                        WHEN ta.approval_flag = 1 THEN mm.mapping_app_1 
                        WHEN ta.approval_flag = 2 THEN mm.mapping_app_2 
                        WHEN ta.approval_flag = 3 THEN mm.mapping_app_3 
                        WHEN ta.approval_flag = 4 THEN mm.mapping_app_4 
                        WHEN ta.approval_flag = 5 THEN mm.mapping_app_5 
                        WHEN ta.approval_flag = 6 THEN mm.mapping_app_6 
                        WHEN ta.approval_flag = 7 THEN mm.mapping_app_7 
                    END
                ) as confirm_state, 
                tad.approval_detail_role, 
                tad.approval_detail_stat_approve as stat_approve
            FROM 
                transaction_approval_detail tad,
                transaction_approval ta ,
                master_mapping mm 
            WHERE 
                tad.approval_id = '".$appId."'
                AND tad.approval_detail_role = '".$role."'
                AND tad.approval_id = ta.approval_id 
                AND ta.mapping_id = mm.mapping_id 
        ")->result();
    }
    public function uploadDoc($fileName){
        $path = 'uploads/project/approvalContract';
        $conf['upload_path']    = $path;
        $conf['allowed_types']  = "pdf";
        $conf['max_size']       = 2048;
        $conf['file_name']      = str_replace(' ', '_', $fileName);

        $this->upload->initialize($conf);
        if($this->upload->do_upload('file')){
            $file = $this->upload->data();
            return [
                    'status'=> true,
                    'msg'   => 'Data berhasil terupload',
                    'name'  => $fileName,
                    'link'  => site_url($path."/".$file['file_name'])
                ];
        }else{
            return [
                'status'=> false,
                'msg'   => $this->upload->display_errors(),
            ];
        }
    }
}
