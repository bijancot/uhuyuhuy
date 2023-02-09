<?php defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;

class Form extends RestController {
    public function __construct() {
        parent::__construct();
        $this->load->helper("url");
        $this->load->library(array('upload', 'image_lib'));
    }

    public function index_get(){
        $param = $this->get();
        if(!empty($param['role'])){
            $forms = $this->db->get_where('MAPPING', ['SECTION_FORM' => $param['role']])->result();
            if($forms != null){
                $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $forms], 200);
            }else{
                $this->response(['status' => false, 'message' => 'Data form tidak ditemukan'], 200);
            }
        }else{
            $this->response(['status' => false, 'message' => 'Parameter tidak cocok'], 200);
        }
    }
}
