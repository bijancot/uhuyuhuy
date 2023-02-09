<?php defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class TenderDocument extends RestController {
    
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
            if($user->user_role != "Admin PICP" && $user->user_role != "ADH") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);
            // End Validation

            $doc = $DB2->order_by('tender_document_no ASC')->get('master_tender_document')->result();

            if($doc != null){
                $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $doc], 200);
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
            if(empty($param['docName'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            $doc = $DB2->get_where('master_tender_document', ['tender_document_name' => $param['docName']])->result();
            if($doc != null) $this->response(['status' => false, 'message' => 'Nama Dokumen telah digunakan!'], 200);
             // End Validation
            
            $storeDoc['tender_document_name']   = $param['docName'];

            $DB2->insert('master_tender_document', $storeDoc);

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
            if(empty($param['idDocument'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            $doc = $DB2->get_where('master_tender_document', ['tender_document_no' => $param['idDocument']])->result();
            if($doc == null) $this->response(['status' => false, 'message' => 'Dokumen tidak ditemukan!'], 200);

            if($doc[0]->tender_document_status == 0){
                $param['status'] = 1;
            }else{
                $param['status'] = 0;
            };
            // End Validation

            $DB2->where('tender_document_no', $param['idDocument'])->update('master_tender_document', ['tender_document_status' => $param['status']]);
            $this->response(['status' => true, 'message' => 'Berhasil mengubah status tipe dokumen!'], 200);
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

            $doc = $DB2->get_where('master_tender_document', ['tender_document_no' => $id])->row();
            if(!empty($doc)){
                $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $doc], 200);
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
            if(empty($param['idDocument']) || empty($param['docName']) || $param['mandatory'] == NULL) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);
            // End Validation

            $formData['tender_document_no']         = $param['idDocument'];
            $formData['tender_document_name']       = $param['docName'];
            $formData['tender_document_mandatory']  = $param['mandatory'];

            $DB2->where('tender_document_no', $param['idDocument'])->update('master_tender_document', $formData);
            $this->response(['status' => true, 'message' => 'Berhasil mengubah data!'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
}
