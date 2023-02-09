<?php defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;

class FormICAK extends RestController {
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
                $idTrans        = 'TRANS_'.md5(time()."trans");
                $idICAK         = 'ICAK_'.md5(time()."icak");
                
                $storeTransaksi['ID_TRANS']         = $idTrans;
                $storeTransaksi['ID_USERS']         = $param['idUser'];
                $storeTransaksi['ID_MAPPING']       = $param['idMapping'];
                $this->db->insert('TRANSACTION', $storeTransaksi);
                
                $storeICAK['ID_ALKOM']          = $idICAK;
                $storeICAK['ID_TRANS']          = $idTrans;
                $storeICAK['TGL_ALKOM']         = $param['tgl'];
                $storeICAK['LOKASI_ALKOM']      = $param['lokasi'];
                $storeICAK['KETERANGAN']        = $param['keterangan'];
                $storeICAK['PROB_IDENT']        = $param['probIdentification'];
                $storeICAK['ROOT_CAUSE']        = $param['rootCause'];
                $storeICAK['CORRECTIVE_ACT']    = $param['correctAct'];
                $storeICAK['PREVENT_ACT']       = $param['preventAct'];
                $storeICAK['DEADLINE']          = $param['deadLine'];
                $storeICAK['PIC']               = $param['pic'];

                $arr = array();
                foreach($param['pabx'] as $item){
                    array_push($arr, $item['status']);
                }
                $storeICAK['PABX_ALKOM'] = implode(';',$arr);
                
                $arr = array();
                foreach($param['progData'] as $item){
                    array_push($arr, $item['status']);
                }
                $storeICAK['PROGDATA_ALKOM'] = implode(';',$arr);
                
                $arr = array();
                foreach($param['repeater'] as $item){
                    array_push($arr, $item['status']);
                }
                $storeICAK['REPEATER_ALKOM'] = implode(';',$arr);
               
                $arr = array();
                foreach($param['radio'] as $item){
                    array_push($arr, $item['status']);
                }
                $storeICAK['RADIO_ALKOM'] = implode(';',$arr);
                
                $this->db->insert('FORM_ALKOM', $storeICAK);
                
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
                $this->response(['status' => true, 'message' => 'Data berhasil ditambahkan', 'data' => $storeICAK], 200);
            }else{
                $this->response(['status' => false, 'message' => 'Data user atau mapping tidak ditemukan'], 200);
            }
        }else{
            $this->response(['status' => false, 'message' => 'Parameter tidak cocok'], 200);
        }
    }
}
