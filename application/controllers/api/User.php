<?php defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;

class User extends RestController {
    public function __construct() {
        parent::__construct();
        $this->load->helper("url");
        $this->load->library(array('upload', 'image_lib'));
    }

    public function index_get(){
        
    }

    public function login_post(){
        $param = $this->post();
        if(!empty($param['username']) && !empty($param['password'])){
            $user = $this->db->get_where('USERS', ['USER_USERS' => $param['username']])->result();
            if($user != null){
                if($user[0]->ISACTIVE_USERS == '1'){
                    $resLogin = $this->db->get_where(
                        'USERS', 
                        ['USER_USERS' => $param['username'], 'PASS_USERS' => hash('sha256', md5($param['password']))]
                    )->result();
                    if($resLogin != null){
                        $this->db->where('USER_USERS', $param['username'])->update('USERS', ['LOGIN_USERS' => 1, 'TOKEN_USERS' => $param['token']]);
                        $resLogin[0]->LOGIN_USERS = '1';
                        $resLogin[0]->TOKEN_USERS = $param['token'];
                        $this->response(['status' => true, 'message' => 'Data berhasil ditemukan' , 'data' => $resLogin[0]], 200);
                    }else{
                        $this->response(['status' => false, 'message' => 'Username atau password salah' ], 200);
                    }
                }else{
                    $this->response(['status' => false, 'message' => 'User sedang dinonaktifkan' ], 200);
                }
            }else{
                $this->response(['status' => false, 'message' => 'Data tidak ditemukan' ], 200);
            }
        }else{
            $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
        }
    }

    public function register_post(){
        $param = $this->post();        
        if(!empty('username') && !empty('namaLengkap') && !empty('telepon') && !empty('department') && !empty('division') && !empty('password') && !empty('signature') && !empty('token')){
            $this->form_validation->set_rules('username', 'USER_USERS','is_unique[USERS.USER_USERS]');
            if($this->form_validation->run()==TRUE){
                $signature = $this->upload_image();
                
                $storeUser['ID_USERS']      = substr(md5(time()), 0, 8);
                $storeUser['NAMA_USERS']    = $param['namaLengkap'];
                $storeUser['USER_USERS']    = $param['username'];
                $storeUser['NOTELP_USERS']  = $param['telepon'];
                $storeUser['ROLE_USERS']    = 'Staff';
                $storeUser['DEPT_USERS']    = $param['departement'];
                $storeUser['DIV_USERS']     = $param['division'];
                $storeUser['PASS_USERS']    = hash('sha256', md5($param['password']));
                $storeUser['PATH_TTD']      = $signature;
                $storeUser['TOKEN_USERS']   = $param['token'];

                $this->db->insert('USERS', $storeUser);
                $this->response(['status' => true, 'message' => 'Data berhasil ditambahkan'], 200);
            }else{
                $this->response(['status' => false, 'message' => 'NRP telah digunakan'], 200);
            }
        }
    }

    public function change_post(){
        $param = $this->post();
        if(!empty($param['idUser']) && !empty($param['newPass']) && !empty($param['oldPass'])){
            $user = $this->db->get_where('USERS', ['ID_USERS' => $param['idUser']])->result();
            if($user != null){
                if($user[0]->PASS_USERS == hash('sha256', md5($param['oldPass']))){
                    $this->db->where('ID_USERS', $param['idUser'])->update('USERS', ['PASS_USERS' => hash('sha256', md5($param['newPass']))]);
                    $this->response(['status' => true, 'message' => 'Berhasil mengubah password'], 200);
                }else{
                    $this->response(['status' => false, 'message' => 'Password tidak cocok'], 200);    
                }
            }else{
                $this->response(['status' => false, 'message' => 'Id User tidak ditemukan'], 200);
            }
        }else{
            $this->response(['status' => false, 'message' => 'Parameter tidak cocok'], 200); 
        }
    }

    public function logout_post(){
        $param = $this->post();
        if(!empty($param['idUser'])){
            $user = $this->db->get_where('USERS', ['ID_USERS' => $param['idUser']])->result();
            if($user != null){
                $this->db->where('ID_USERS', $param['idUser'])->update('USERS', ['LOGIN_USERS' => 0, 'TOKEN_USERS' => null]);
                $this->response(['status' => true, 'message' => 'Berhasil logout'], 200);
            }else{
                $this->response(['status' => false, 'message' => 'Id User tidak ditemukan'], 200);
            }
        }else{
            $this->response(['status' => false, 'message' => 'Parameter tidak cocok'], 200); 
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

}
