<?php defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;

class FormDeklarasi extends RestController {
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
                $idDeklarasi     = 'DEKL_'.md5(time()."deklarasi");
                
                $storeTransaksi['ID_TRANS']         = $idTrans;
                $storeTransaksi['ID_USERS']         = $param['idUser'];
                $storeTransaksi['ID_MAPPING']       = $param['idMapping'];
                $this->db->insert('TRANSACTION', $storeTransaksi);

                $storeDeklarasi['ID_DEKLARASI']     = $idDeklarasi;
                $storeDeklarasi['ID_TRANS']         = $idTrans;
                $storeDeklarasi['TGL_DEKLARASI']    = $param['tgl'];
                $storeDeklarasi['DD_DEKLARASI']     = $param['dd'];
                $storeDeklarasi['NOPOL_DEKLARASI']  = $param['nopol'];                
                $this->db->insert('FORM_DEKLARASI', $storeDeklarasi);

                foreach($param['detKeperluan'] as $item){
                    $storeDetDeklarasi['ID_DEKLARASI']    = $idDeklarasi;
                    $storeDetDeklarasi['BBM_DEKLARASI']   = $item['bbm'];
                    $storeDetDeklarasi['TOL_DEKLARASI']   = $item['tol']; 
                    $storeDetDeklarasi['GRAB_DEKLARASI']  = $item['grab'];
                    $storeDetDeklarasi['LAIN_DEKLARASI']  = $item['lain'];
                    $storeDetDeklarasi['JML_DEKLARASI']   = $item['bbm']+$item['tol']+$item['grab']+$item['lain'];
                    $this->db->insert('DETAIL_DEKLARASI', $storeDetDeklarasi);
                }
                
                $flow = $this->db->get_where('FLOW', ['ID_MAPPING' => $mapping[0]->ID_MAPPING])->result_array();
                for($i = 1; $i <= 15; $i++){
                    if(!empty($flow[0]['APP_'.$i]) && $flow[0]['APP_'.$i] != null){
                        $this->db->insert('DETAIL_APPROVAL', ['ID_TRANS' => $idTrans, 'ROLE_APP' => $flow[0]['APP_'.$i]]);
                    }else if($flow[0]['APP_1'] == null){
                        $this->db->where('ID_TRANS', $idTrans)->update('TRANSACTION', ['STAT_TRANS' => '2']);
                    }
                }
                
                $resLinkGenerated = $this->ContentPdf->generate(['idTrans' => $idTrans, 'orientation' => 'portrait']);
                $this->db->where('ID_TRANS', $idTrans)->update('TRANSACTION', ['PATH_TRANS' => base_url($resLinkGenerated)]);
                $this->pusherjs->push();
                $this->response(['status' => true, 'message' => 'Data berhasil ditambahkan'], 200);
            }else{
                $this->response(['status' => false, 'message' => 'Data user atau mapping tidak ditemukan'], 200);
            }
        }else{
            $this->response(['status' => false, 'message' => 'Parameter tidak cocok'], 200);
        }
    }
}
