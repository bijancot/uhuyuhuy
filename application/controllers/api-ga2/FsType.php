<?php defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class FsType extends RestController {
    
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
            if($user->user_role != "PICP" && $user->user_role != "Admin PICP"  && $user->user_role != "ADH") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);
            // End Validation

            $type = $DB2->order_by('fs_type_no ASC')->get('master_fs_type')->result();

            if($type != null){
                $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $type], 200);
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
            if(empty($param['typeName'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            $type = $DB2->get_where('master_fs_type', ['fs_type_name' => $param['typeName']])->result();
            if($type != null) $this->response(['status' => false, 'message' => 'Nama type telah digunakan!'], 200);
             // End Validation
            
            $formData['fs_type_name'] = $param['typeName'];
            $DB2->insert('master_fs_type', $formData);

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
            if(empty($param['idFSType'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            $type = $DB2->get_where('master_fs_type', ['fs_type_no' => $param['idFSType']])->result();
            if($type == null) $this->response(['status' => false, 'message' => 'FS type tidak terdaftar!'], 200);

            if($type[0]->fs_type_status == 0){
                $param['status'] = 1;
            }else{
                $param['status'] = 0;
            };
            // End Validation

            $DB2->where('fs_type_no', $param['idFSType'])->update('master_fs_type', ['fs_type_status' => $param['status']]);
            $this->response(['status' => true, 'message' => 'Berhasil mengubah status FS type!'], 200);
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

            $type = $DB2->get_where('master_fs_type', ['fs_type_no' => $id])->row();
            if(!empty($type)){
                $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $type], 200);
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
            if(empty($param['idFSType']) || empty($param['typeName']) || $param['mandatory'] == NULL) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);
            // End Validation

            $formData['fs_type_no']             = $param['idFSType'];
            $formData['fs_type_name']           = $param['typeName'];
            $formData['fs_type_mandatory']      = $param['mandatory'];

            $DB2->where('fs_type_no', $param['idFSType'])->update('master_fs_type', $formData);
            $this->response(['status' => true, 'message' => 'Berhasil mengubah data!'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }

}
