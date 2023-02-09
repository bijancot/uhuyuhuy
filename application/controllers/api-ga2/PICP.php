<?php defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class PICP extends RestController {
    
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

            $users = $DB2->order_by('user_no ASC')->get('master_user')->result();

            if($users != null){
                $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $users], 200);
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
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            if(!empty('username') && !empty('namaLengkap') && !empty('telepon') && !empty('department') && !empty('division') && !empty('password') && !empty('area') && !empty('inisial')){

                $uname = $DB2->get_where('master_user', ['user_no' => $param['username']])->result();
                if($uname != null) $this->response(['status' => false, 'message' => 'Username telah digunakan!'], 200);

                $initial = $DB2->get_where('master_user', ['user_initials' => $param['inisial']])->result();
                if($initial != null) $this->response(['status' => false, 'message' => 'Inisial telah digunakan!'], 200);
                
                $signature = $this->upload_image();

                $storeUser['user_id']       = substr(md5(time()), 0, 8);
                $storeUser['user_name']     = $param['namaLengkap'];
                $storeUser['user_no']       = $param['username'];
                $storeUser['user_phone']    = $param['telepon'];
                $storeUser['user_role']     = !empty($param['role']) ? $param['role'] : 'Staff';
                $storeUser['user_dept']     = $param['department'];
                $storeUser['user_div']      = $param['division'];
                $storeUser['user_start']    = $param['dateStart'];
                $storeUser['user_end']      = $param['dateEnd'];
                $storeUser['user_area']     = $param['area'];
                $storeUser['user_initials'] = $param['inisial'];
                $storeUser['user_pass']     = hash('sha256', md5($param['password']));
                $storeUser['path_ttd']      = $signature;

                $DB2->insert('master_user', $storeUser);
                $this->response(['status' => true, 'message' => 'Data berhasil ditambahkan'], 200);
            }else{
                $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            }
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
            if(empty($param['username'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            $usr = $DB2->get_where('master_user', ['user_no' => $param['username']])->result();
            if($usr == null) $this->response(['status' => false, 'message' => 'PICP tidak terdaftar!'], 200);

            if($usr[0]->user_isactive == 0){
                $param['status'] = 1;
            }else{
                $param['status'] = 0;
            };
            // End Validation

            $DB2->where('user_no', $param['username'])->update('master_user', ['user_isactive' => $param['status']]);
            $this->response(['status' => true, 'message' => 'Berhasil mengubah status PICP!'], 200);
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

            $userDetail = $DB2->get_where('master_user', ['user_no' => $id])->row();
            if(!empty($userDetail)){
                $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $userDetail], 200);
            }else{
                $this->response(['status' => true, 'message' => 'Data tidak ditemukan'], 200);
            }
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
   
    public function edit_post(){
        try{
            $DB2 = $this->load->database('gaSys2', true);
            $jwt = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param = $this->post();

            // Validation
            if(empty($param['username']) || empty($param['namaLengkap']) || empty($param['telepon']) || empty($param['role']) || empty($param['department']) || empty($param['division']) || empty($param['area']) || empty($param['inisial'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);
            // End Validation      
            
            $userDetail = $DB2->get_where('master_user', ['user_no' => $param['username']])->row();
            if(!empty($userDetail)){                
                $signature = $this->upload_image();

                $storeUser['user_no']       = $param['username'];
                $storeUser['user_name']     = $param['namaLengkap'];
                $storeUser['user_phone']    = $param['telepon'];
                $storeUser['user_role']     = !empty($param['role']) ? $param['role'] : 'Staff';
                $storeUser['user_dept']     = $param['department'];
                $storeUser['user_div']      = $param['division'];
                $storeUser['user_area']     = $param['area'];
                $storeUser['user_initials'] = $param['inisial'];  
                $storeUser['user_start']    = $param['dateStart'];
                $storeUser['user_end']      = $param['dateEnd'];

                $cek = base_url('images/ttd/default.png');
                if($signature != $cek){                    
                    $storeUser['path_ttd']      = $signature;
                };

                $DB2->where('user_no', $param['username'])->update('master_user', $storeUser);
                $this->response(['status' => true, 'message' => 'Berhasil mengubah data!'], 200);
            }else{
                $this->response(['status' => false, 'message' => 'Username tidak ditemukan'], 200);
            }         
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }

    function upload_image(){
        $config['upload_path'] = './images/ttd/'; //path folder
        $config['allowed_types'] = 'gif|jpg|png|jpeg|bmp'; //type yang dapat diakses bisa anda sesuaikan
        $config['encrypt_name'] = TRUE; //Enkripsi nama yang terupload
 
        $this->upload->initialize($config);
        if(!empty($_FILES['ttd']['name'])){
 
            if ($this->upload->do_upload('ttd')){
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
}
