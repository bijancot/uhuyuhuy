<?php defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Area extends RestController {
    
    public function __construct() {
        parent::__construct();
        $this->load->helper("url");
        $this->load->library(array('upload', 'image_lib'));
    }

    public function index_get(){
        try{
            $DB2 = $this->load->database('gaSys2', true);
            $jwt = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));

            // Validation
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "PICP" && $user->user_role != "Admin PICP" && $user->user_role != "ADH") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);
            // End Validation

            $areas = $DB2->order_by('area_code ASC')->get('master_area')->result();

            if($areas != null){
                $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $areas], 200);
            }else{
                $this->response(['status' => true, 'message' => 'Data tidak ditemukan'], 200);
            }
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }

    public function index_post(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->post();

            // Validation
            if(empty($param['areaCode']) || empty($param['areaName']) || empty($param['areaAddress']) ) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            $areas = $DB2->get_where('master_area', ['area_code' => $param['areaCode']])->result();
            if($areas != null) $this->response(['status' => false, 'message' => 'Kode Area telah digunakan!'], 200);
             // End Validation
            
            $formData['area_code']          = $param['areaCode'];
            $formData['area_name']          = $param['areaName'];
            $formData['area_branch_address']= $param['areaAddress'];
            $DB2->insert('master_area', $formData);

            $this->response(['status' => true, 'message' => 'Data berhasil ditambahkan!'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }

    public function changeStatus_put(){
        try{
            $DB2 = $this->load->database('gaSys2', true);
            $jwt = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param = $this->put();

            // Validation
            if(empty($param['areaCode'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            $area = $DB2->get_where('master_area', ['area_code' => $param['areaCode']])->result();
            if($area == null) $this->response(['status' => false, 'message' => 'Area tidak terdaftar!'], 200);

            if($area[0]->area_status == 0){
                $param['status'] = 1;
            }else{
                $param['status'] = 0;
            };
            // End Validation

            $DB2->where('area_code', $param['areaCode'])->update('master_area', ['area_status' => $param['status']]);
            $this->response(['status' => true, 'message' => 'Berhasil mengubah status area!'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }

    public function detail_get($id){
        try{
            $DB2 = $this->load->database('gaSys2', true);
            $jwt = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));

            // Validation
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);
            // End Validation

            $area = $DB2->get_where('master_area', ['area_code' => $id])->row();
            if(!empty($area)){
                $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $area], 200);
            }else{
                $this->response(['status' => true, 'message' => 'Data tidak ditemukan'], 200);
            }
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
   
    public function index_put(){
        try{
            $DB2 = $this->load->database('gaSys2', true);
            $jwt = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param = $this->put();

            // Validation
            if(empty($param['areaCode']) || empty($param['areaName']) || empty($param['areaAddress']) ) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);
            // End Validation

            $formData['area_code']          = $param['areaCode'];
            $formData['area_name']          = $param['areaName'];
            $formData['area_branch_address']= $param['areaAddress'];

            $DB2->where('area_code', $param['areaCode'])->update('master_area', $formData);
            $this->response(['status' => true, 'message' => 'Berhasil mengubah data!'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }

}
