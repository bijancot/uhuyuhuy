<?php defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;

class FormNonAsset extends RestController {
    public function __construct() {
        parent::__construct();
        $this->load->helper("url");
        $this->load->library(array('upload', 'image_lib'));
    }

    public function index_post(){
        $param = $this->post();
        if(!empty($param['idUser']) && !empty($param['idMapping'])){
            $user       = $this->db->get_where('USERS', ['ID_USERS' => $param['idUser']])->result();
            $mapping    = $this->db->get_where('MAPPING', ['ID_MAPPING' => $param['idMapping']])->result();
            if($user != null && $mapping != null){
                $idTrans    = 'TRANS_'.md5(time()."trans");
                $idNA       = 'NA_'.md5(time()."nonasset");
                
                $storeTransaksi['ID_TRANS']         = $idTrans;
                $storeTransaksi['ID_USERS']         = $param['idUser'];
                $storeTransaksi['ID_MAPPING']       = $param['idMapping'];
                $this->db->insert('TRANSACTION', $storeTransaksi);

                $storeNA['ID_NA']       = $idNA;
                $storeNA['ID_TRANS']    = $idTrans;
                $this->db->insert('FORM_NONASSET', $storeNA);

                foreach($param['detNonAsset'] as $item){
                    $storeDetNA['ID_NA']            = $idNA;
                    $storeDetNA['SPESIFIKASI_NA']   = $item['jenBarang'];
                    $storeDetNA['JML_NA']           = $item['jmlPesan'];
                    $storeDetNA['ACC_NA']           = $item['account'];
                    $storeDetNA['CC_NA']            = $item['cost'];
                    $storeDetNA['TGL_NA']           = $item['tgl'];
                    $storeDetNA['KET_NA']           = $item['keterangan'];
                    $this->db->insert('DETAIL_NONASSET', $storeDetNA);
                }
                
                $flow = $this->db->get_where('FLOW', ['ID_MAPPING' => $mapping[0]->ID_MAPPING])->result_array();
                for($i = 1; $i <= 15; $i++){
                    if(!empty($flow[0]['APP_'.$i]) && $flow[0]['APP_'.$i] != null){
                        if($param['isDivApprove'] == "1" && $flow[0]['APP_'.$i] == "Division Head"){
                            break;
                        }else{
                            $this->db->insert('DETAIL_APPROVAL', ['ID_TRANS' => $idTrans, 'ROLE_APP' => $flow[0]['APP_'.$i]]);
                        }
                    }else if($flow[0]['APP_1'] == null){
                        $this->db->where('ID_TRANS', $idTrans)->update('TRANSACTION', ['STAT_TRANS' => '2']);
                    }
                }
                
                $this->pusherjs->push();
                $this->response(['status' => true, 'message' => 'Data berhasil ditambahkan', 'idTrans' => $idTrans], 200);
            }else{
                $this->response(['status' => false, 'message' => 'Data user atau mapping tidak ditemukan'], 200);
            }
        }else{
            $this->response(['status' => false, 'message' => 'Parameter tidak cocok'], 200);
        }
    }

    public function upload_post(){
        $param = $this->post();
        if(!empty($param['idTrans']) && !empty($param['idUser'])){
            $na = $this->db->get_where('FORM_NONASSET', ['ID_TRANS' => $param['idTrans']])->result();
            $user   = $this->db->get_where('USERS', ['ID_USERS' => $param['idUser']])->result();
            $statUpload   = $param['statUpload'];
            $totalUpload  = $param['totalUpload'];
            if($statUpload <= $totalUpload && $user != null && $na != null){
                $resUpload = $this->upload_file($user[0]->USER_USERS);
                if($na[0]->LINKDOKPEND_NA != null){
                    $resUpload['fileName']  = $na[0]->DOKPEND_NA.';'.$resUpload['fileName'];
                    $resUpload['link']      = $na[0]->LINKDOKPEND_NA.';'.$resUpload['link'];
                }              
                $this->db->where('ID_TRANS', $param['idTrans'])->update('FORM_NONASSET', ['LINKDOKPEND_NA' => $resUpload['link'], 'DOKPEND_NA' => $resUpload['fileName']]);

                if($statUpload == $totalUpload){
                    $resLinkGenerated = $this->ContentPdf->generate(['idTrans' => $param['idTrans'], 'orientation' => 'portrait']);
                    $this->db->where('ID_TRANS', $param['idTrans'])->update('TRANSACTION', ['PATH_TRANS' => base_url($resLinkGenerated)]);
                }
                $this->response(['status' => true, 'message' => 'Data berhasil ditambahkan'], 200);
            }else{
                $this->response(['status' => false, 'message' => 'Data user atau transaksi pvrv tidak ditemukan / Selesai mengupload'], 200);
            }
        }else{
            $this->response(['status' => false, 'message' => 'Parameter tidak cocok'], 200);
        }
    }

    public function dokPend_get(){
        $param = $this->get();
        if(!empty($param['idTrans']) && !empty($param['idUser'])){
            $na = $this->db->get_where('FORM_NONASSET', ['ID_TRANS ' => $param['idTrans']])->result();
            $user = $this->db->get_where('USERS', ['ID_USERS ' => $param['idUser']])->result();
            if($user != null && $na != null){
                $dokPends   = array();
                $link       = explode(';', $na[0]->LINKDOKPEND_NA);
                $fileName   = explode(';', $na[0]->DOKPEND_NA);
                $x = 0;
                foreach($link as $item){
                    $obj = new stdClass();
                    $obj->fileName = $fileName[$x];
                    $obj->link     = $link[$x];
                    array_push($dokPends, $obj);
                    $x++;
                }
                $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $dokPends], 200);
            }else{
                $this->response(['status' => false, 'message' => 'Data pvrv atau user tidak ditemukan'], 200);
            }
        }else{
            $this->response(['status' => false, 'message' => 'Parameter tidak cocok'], 200);
        }
    }

    function upload_file($username){
        $date = date('Ymd');
        $newPath = './uploads/assets/dokpend/non-asset/'.$username.'/'.$date.'/';
        if(!is_dir($newPath)){
            mkdir($newPath, 0777, TRUE);
        }
        $config = array();
        $config['upload_path']          = $newPath;
        $config['allowed_types']        = 'pdf';
        $config['max_size']             = 2000;
        $config['encrypt_name']         = FALSE;

        $this->upload->initialize($config);
    
        if ( ! $this->upload->do_upload('file') ) {
            return ['link' => base_url('images/ttd/default.png'), 'fileName' => ''];
        } else {
            $ups = $this->upload->data();

            $upname = $ups['file_name'];

            return ['link' => base_url('/uploads/assets/dokpend/non-asset/'.$username.'/'.$date.'/'.$upname), 'fileName' => $upname];
        }             
    }
}
