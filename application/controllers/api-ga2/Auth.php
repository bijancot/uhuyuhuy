<?php defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth extends RestController {
    
    public function __construct() {
        parent::__construct();
        $this->load->helper("url");
        $this->load->library(array('upload', 'image_lib'));
    }

    public function login_post(){
        $DB2 = $this->load->database('gaSys2', true);
        $param = $this->post();
        // Validation
        if(empty($param['username']) || empty($param['password']) || empty($param['token'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
        $user = $DB2->get_where('master_user', ['user_no' => $param['username']])->result();
        if($user == null) $this->response(['status' => false, 'message' => 'Data tidak ditemukan' ], 200);
        if($user[0]->user_isactive == '0') $this->response(['status' => false, 'message' => 'User sedang dinonaktifkan' ], 200);
        // End Validation

        $resLogin = $DB2->get_where(
            'master_user', 
            ['user_no' => $param['username'], 'user_pass' => hash('sha256', md5($param['password']))]
        )->result();

        if($resLogin != null){
            if($resLogin[0]->user_isactive == 1){
                $sess = md5($param['username'].time());
                $DB2->where('user_no', $param['username'])->update('master_user', ['user_session' => $sess, 'user_token' => $param['token']]);

                $user = array();
                $user['user_no']    = $resLogin[0]->user_no;
                $user['user_name']  = $resLogin[0]->user_name;
                $user['user_role']  = $resLogin[0]->user_role;
                $user['user_area']  = $resLogin[0]->user_area;
                $user['user_dept']  = $resLogin[0]->user_dept;
                $user['user_div']   = $resLogin[0]->user_div;
                $user['user_phone'] = $resLogin[0]->user_phone;
                $user['user_sess']  = $sess;
                if($param['token'] != '-') $user['user_token'] = $param['token'];
                $user['iat']        = time();
                $user['exp']        = time() + 86400;

                $jwt = JWT::encode($user, $this->config->item('SECRET_KEY'), 'HS256');
                $user['user_jwt'] = $jwt;
                unset($user['user_sess']);
                $this->response(['status' => true, 'message' => 'Data berhasil ditemukan' , 'data' => ['jwt' => $jwt]], 200);
            }else{
                $this->response(['status' => false, 'message' => 'User sedang dinonaktifkan' ], 200);
            }            
        }else{
            $this->response(['status' => false, 'message' => 'Username atau password salah' ], 200);
        }
    }
    public function register_post(){
        $DB2 = $this->load->database('gaSys2', true);
        $param = $this->post();        
        if(!empty($param['username']) && !empty($param['namaLengkap']) && !empty($param['telepon']) && !empty($param['department']) && !empty($param['division']) && !empty($param['password']) && !empty($param['signature']) && !empty($param['token'])){
            $user = $DB2->get_where('master_user', ['user_no' => $param['username']])->result();
            if($user == null){
                $signature = $this->upload_image();
                
                $storeUser['user_id']       = substr(md5(time()), 0, 8);
                $storeUser['user_name']     = $param['namaLengkap'];
                $storeUser['user_no']       = $param['username'];
                $storeUser['user_phone']    = $param['telepon'];
                $storeUser['user_role']     = !empty($param['role']) ? $param['role'] : 'Staff';
                $storeUser['user_dept']     = $param['departement'];
                $storeUser['user_div']      = $param['division'];
                $storeUser['user_pass']     = hash('sha256', md5($param['password']));
                $storeUser['path_ttd']      = $signature;
                if($param['token'] != '-'){
                    $storeUser['user_token']    = $param['token'];
                }

                $DB2->insert('master_user', $storeUser);
                $this->response(['status' => true, 'message' => 'Data berhasil ditambahkan'], 200);
            }else{
                $this->response(['status' => false, 'message' => 'NRP telah digunakan'], 200);
            }
        }
    }

    function upload_image(){
        $config['upload_path'] = './images/ttd/'; //path folder
        $config['allowed_types'] = 'gif|jpg|png|jpeg|bmp'; //type yang dapat diakses bisa anda sesuaikan
        $config['encrypt_name'] = TRUE; //Enkripsi nama yang terupload
 
        $this->upload->initialize($config);
        if(!empty($_FILES['signature']['name'])){
 
            if ($this->upload->do_upload('signature')){
                $gbr = $this->upload->data();
                //Compress Image
                $config['image_library']='gd2';
                $config['source_image']='./images/ttd/'.$gbr['file_name'];
                $config['create_thumb']= FALSE;
                $config['maintain_ratio']= true;
                // $config['quality']= '100%';
                $config['width']= 600;
                // $config['height']= 400;
                $config['new_image']= './images/ttd/'.$gbr['file_name'];
                $this->load->library('image_lib', $config);
                $this->image_lib->resize();
 
                $gambar=$gbr['file_name'];

                return base_url('images/ttd/'.$gambar);
            }
                      
        }else{
            return base_url('images/ttd/default.png');
        }         
    }

    public function logout_post(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));

            $DB2->where('user_no', $jwt->user_no)->update('master_user', ['user_session' => null]);

            $this->response(['status' => true, 'message' => 'Berhasil logout'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }

    }

    public function tes_post(){
        $param = $this->post();
        $SECRET_KEY = '69122c359b02fdb14405fd0bf09abf8d1f903ece379d8b08b14c8a8c36178696f151405951e336fe0ffb3e8a5a37003228dcb9c066bde5e5c7c026f725f84fa726493d2ca491ed2ad31a29a1228bdbce5f5d0f5bdb1ca36151f38d5a8ed38dde0807386ed5e98c5a32a11eab14e441ec189731d9900e8db18378e00eab9127b2c3da6865d335274c49ea21f9568cf4de09636eb0a0d7b79d876c3532e3af806b20de679f59881197064ec16bed63b52cbb4df5e74c370dd0343dacc476e51f9ddc289c69cc32779f0225a87613bd91cb174e29a2d2b264687a2704c146e689f0e74e9844e85fef6d8812cc4c786c4b61bc83d74de8b62d30ec708d63381f4f0d';

        try {
            JWT::decode('eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VyX25vIjoiMTI5MzEyOTgxMiIsInVzZXJfbmFtZSI6IklsaGFtIiwidXNlcl9yb2xlIjoiUElDQSIsInVzZXJfYXJlYSI6IkpLVCIsInVzZXJfcGhvbmUiOiIwODY4Njc4IiwidXNlcl90b2tlbiI6Im9rZWRva2V5IiwidXNlcl9zZXNzIjoiYTM0ZDdlOTg1MjI2ZGJkZGJmZTExMTUxNTVjYjBjYWIifQ.eFQuj0AGDoaLeZjzsvlTbG2o6V76yTtcnhgNjknQet0', new Key($SECRET_KEY, 'HS256'));
            $this->response('berhasil', 200);
        } catch (Exception $exp) {
            $this->response('gagal', 200);
        }
    }
}
