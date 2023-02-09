<?php defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;

class FormMonitoring extends RestController {
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
                $idMonitoring   = 'MON_'.md5(time()."mon");
                
                $storeTransaksi['ID_TRANS']         = $idTrans;
                $storeTransaksi['ID_USERS']         = $param['idUser'];
                $storeTransaksi['ID_MAPPING']       = $param['idMapping'];
                $this->db->insert('TRANSACTION', $storeTransaksi);
                
                $storeMonitoring['ID_MONITORING']         = $idMonitoring;
                $storeMonitoring['ID_TRANS']              = $idTrans;
                $storeMonitoring['TGLCEK1_MONITORING']    = $param['cek1']['tgl'];
                $storeMonitoring['TGLCEK2_MONITORING']    = $param['cek2']['tgl'];
                $storeMonitoring['TGLCEK3_MONITORING']    = $param['cek3']['tgl'];
                $storeMonitoring['TGLCEK4_MONITORING']    = $param['cek4']['tgl'];
                $storeMonitoring['TGLCEK5_MONITORING']    = $param['cek5']['tgl'];
                $storeMonitoring['TGLCEK6_MONITORING']    = $param['cek6']['tgl'];
                $storeMonitoring['TGLCEK7_MONITORING']    = $param['cek7']['tgl'];
                
                $arr['order']   = array();
                $arr['bawa']    = array();
                $arr['kupon']   = array();

                for($x = 1; $x <= 7; $x++){
                    array_push($arr['order'], $param['cek'.$x]['order']);                    
                    array_push($arr['bawa'], $param['cek'.$x]['bawa']);                    
                    array_push($arr['kupon'], $param['cek'.$x]['kupon']);       
                }

                $storeMonitoring['JML_ORDER']  = implode(';', $arr['order']);
                $storeMonitoring['ACT_BAWA']   = implode(';', $arr['bawa']);
                $storeMonitoring['ACT_KUPON']  = implode(';', $arr['kupon']);
                $this->db->insert('FORM_MONITORING', $storeMonitoring);

                
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
