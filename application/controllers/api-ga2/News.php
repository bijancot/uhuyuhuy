<?php defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class News extends RestController {
    
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
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "PICP" && $user->user_role != "Admin PICP" && $user->user_role != "ADH") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);
            // End Validation

            $news = $this->queryGet($param['limit'], $param['page']);

            if($news['data'] == null)  $this->response(['status' => false, 'message' => 'Data tidak ditemukan!'], 200);
            $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'pagination' => $news['pagination'], 'data' => $news['data']], 200);
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
            
            $news = $DB2->get_where('master_news', ['news_id' => $id])->result();
            if($news == null) $this->response(['status' => false, 'message' => 'Data tidak ditemukan'], 200);
            $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $news[0]], 200);
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
            if(empty($param['title']) || empty($param['content'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok!'], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "PICP" && $user->user_role != "Admin PICP" && $user->user_role != "ADH") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);
            // End Validation
            $banner = $this->upload_image();

            $formData['news_title']         = $param['title'];
            $formData['news_content']       = $param['content'];
            $formData['news_date']          = date('Y-m-d H:i:s');
            $formData['news_ispublish']     = $param['isPublish'];
            $formData['news_image']         = $banner;
            
            $DB2->insert('master_news', $formData);
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
            if(empty($param['idNews']) || empty($param['title']) || empty($param['content'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok!'], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "PICP" && $user->user_role != "Admin PICP" && $user->user_role != "ADH") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);
            $news = $DB2->get_where('master_news', ['news_id' => $param['idNews']])->result();
            if($news == null) $this->response(['status' => false, 'message' => 'Berita tidak terdaftar!'], 200);
            // End Validation
            $formData['news_title']         = $param['title'];
            $formData['news_content']       = $param['content'];
            $formData['news_ispublish']     = $param['isPublish'];

            if(!empty($_FILES['file']['name'])){
                $banner = $this->upload_image();
                $formData['news_image'] = $banner;
            };
            
            $DB2->where('news_id', $param['idNews'])->update('master_news', $formData);
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
            if($user->user_role != "PICP" && $user->user_role != "Admin PICP" && $user->user_role != "ADH") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);
            $news = $DB2->get_where('master_news', ['news_id' => $id])->result();
            if($news == null) $this->response(['status' => false, 'message' => 'Berita tidak terdaftar!'], 200);
            // End Validation
            
            $DB2->delete('master_news', ['news_id' => $id]);
            $this->response(['status' => true, 'message' => 'Data berhasil dihapus'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function queryGet($limit, $page){
        $DB2 = $this->load->database('gaSys2', true);
        $datas = $DB2->query("
            SELECT * FROM master_news
            ORDER BY news_date DESC
            LIMIT ".$limit."
            OFFSET ".(($page-1)*$limit)."
        ")->result();

        $allData = $DB2->query("
            SELECT * FROM master_news  
            ORDER BY news_date DESC 
        ")->result();

        $pagination['limit']        = $limit;
        $pagination['page']         = $page;
        $pagination['total_page']   = ceil((count($allData) / $limit));
        $pagination['total_data']   = count($allData);
        return ['pagination' => $pagination, 'data' => $datas];
    }

    function upload_image(){
        $config['upload_path'] = './uploads/project/fs/'; //path folder
        $config['allowed_types'] = 'gif|jpg|png|jpeg|bmp'; //type yang dapat diakses bisa anda sesuaikan
        $config['encrypt_name'] = TRUE; //Enkripsi nama yang terupload
 
        $this->upload->initialize($config);
        if(!empty($_FILES['file']['name'])){
 
            if ($this->upload->do_upload('file')){
                $gbr = $this->upload->data();
                //Compress Image
                $config['image_library']='gd2';
                $config['source_image']='./uploads/project/fs/'.$gbr['file_name'];
                $config['create_thumb']= FALSE;
                $config['maintain_ratio']= true;
                // $config['quality']= '100%';
                //$config['width']= 600;
                // $config['height']= 400;
                $config['new_image']= './uploads/project/fs/'.$gbr['file_name'];
                $this->load->library('image_lib', $config);
                $this->image_lib->resize();
 
                $gambar=$gbr['file_name'];

                return base_url('uploads/project/fs/'.$gambar);
            }                      
        }else{
            return base_url('uploads/news/default.jpg');
        }         
    }
}
