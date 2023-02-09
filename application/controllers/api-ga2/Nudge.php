<?php defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Nudge extends RestController {
    
    public function __construct() {
        parent::__construct();
        $this->load->helper("url");
        $this->load->library(array('upload', 'image_lib'));
    }
    public function dnd_put(){
        try{
            $DB2 = $this->load->database('gaSys2', true);
            $jwt = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param = $this->put();
            
            if(empty($param['projectNo'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            $project = $DB2->get_where('master_project', ['project_no' => $param['projectNo']])->result();
            if($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);
            
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            $formNotif['project_no']        = $param['projectNo'];
            $formNotif['notif_content']     = "Terdapat status nudge project ".$param['projectNo'];
            $formNotif['notif_date']        = date('Y-m-d H:i:s');
            $formNotif['notif_from_role']   = "Admin PICP";
            $formNotif['notif_from_name']   = $user->user_name;
            $formNotif['notif_for']         = "PICP";
            $formNotif['notif_type']        = "NUDGE D&D";
            $DB2->insert('transaction_notif', $formNotif);
            $this->response(['status' => true, 'message' => 'Berhasil nudge project!'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function tender_put(){
        try{
            $DB2 = $this->load->database('gaSys2', true);
            $jwt = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param = $this->put();
            
            if(empty($param['projectNo'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            $project = $DB2->get_where('master_project', ['project_no' => $param['projectNo']])->result();
            if($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);
            
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            $formNotif['project_no']        = $param['projectNo'];
            $formNotif['notif_content']     = "Terdapat status nudge project ".$param['projectNo'];
            $formNotif['notif_date']        = date('Y-m-d H:i:s');
            $formNotif['notif_from_role']   = "Admin PICP";
            $formNotif['notif_from_name']   = $user->user_name;
            $formNotif['notif_for']         = "PICP";
            $formNotif['notif_type']        = "NUDGE TENDER";
            $DB2->insert('transaction_notif', $formNotif);
            $this->response(['status' => true, 'message' => 'Berhasil nudge project!'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
}