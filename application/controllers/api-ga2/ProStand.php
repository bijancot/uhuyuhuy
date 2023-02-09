<?php defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ProStand extends RestController {
    
    public function __construct() {
        parent::__construct();
        $this->load->helper("url");
        $this->load->library(array('upload'));
    }

    public function index_get(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->get();

            // Validation
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "PICP" && $user->user_role != "Admin PICP" && $user->user_role != "ADH") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);
            // End Validation

            $proStand = $DB2->order_by('pro_stand_no ASC')->get('master_pro_stand')->result();

            if($proStand != null){
                $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $proStand], 200);
            }else{
                $this->response(['status' => true, 'message' => 'Data tidak ditemukan'], 200);
            }
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    
    public function detail_get($id){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->post();

            // Validation
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "PICP" && $user->user_role != "Admin PICP" && $user->user_role != "ADH") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);
            // End Validation
            
            $proStand = $DB2->get_where('master_pro_stand', ['pro_stand_no' => $id])->row();

            if($proStand == null) $this->response(['status' => false, 'message' => 'Data tidak ditemukan'], 200);
            $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $proStand], 200);
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
            if(empty($param['title']) || empty($param['desc'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok!'], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "PICP" && $user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);
            // End Validation
            $uploadfile = $this->upload_file();

            $formData['pro_stand_title']    = $param['title'];
            $formData['pro_stand_desc']     = $param['desc'];
            $formData['pro_stand_path']     = $uploadfile;
            $formData['pro_stand_publish']  = $param['isPublish'];
            
            $DB2->insert('master_pro_stand', $formData);
            $this->response(['status' => true, 'message' => 'Data berhasil disimpan'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function edit_post(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->post();

            // Validation
            if(empty($param['idProStand']) || empty($param['title']) || empty($param['desc'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok!'], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "PICP" && $user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);
            $proStand = $DB2->get_where('master_pro_stand', ['pro_stand_no' => $param['idProStand']])->result();
            if($proStand == null) $this->response(['status' => false, 'message' => 'Dokumen tidak terdaftar!'], 200);
            // End Validation
            $formData['pro_stand_title']      = $param['title'];
            $formData['pro_stand_desc']       = $param['desc'];
            $formData['pro_stand_publish']    = $param['isPublish'];

            if(!empty($_FILES['file']['name'])){
                $uploadfile = $this->upload_file();
                $formData['pro_stand_path'] = $uploadfile;
            };
            
            $DB2->where('pro_stand_no', $param['idProStand'])->update('master_pro_stand', $formData);
            $this->response(['status' => true, 'message' => 'Data berhasil diubah'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function index_delete($id){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));

            // Validation
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "PICP" && $user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);
            $proStand = $DB2->get_where('master_pro_stand', ['pro_stand_no' => $id])->result();
            if($proStand == null) $this->response(['status' => false, 'message' => 'Dokumen tidak terdaftar!'], 200);
            // End Validation
            
            $DB2->delete('master_pro_stand', ['pro_stand_no' => $id]);
            $this->response(['status' => true, 'message' => 'Data berhasil dihapus'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }

    function upload_file(){
        $config['upload_path'] = './uploads/project/fs/';
        $config['allowed_types'] = '*';
        $config['encrypt_name'] = FALSE;
 
        $this->upload->initialize($config);
        if(!empty($_FILES['file']['name'])){
 
            if ($this->upload->do_upload('file')){
                $ups = $this->upload->data();   
                $hasil = $ups['file_name'];

                return base_url('uploads/project/fs/'.$hasil);
            }                      
        }else{
            return null;
        }         
    }
}
