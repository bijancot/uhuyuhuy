<?php defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;

class FormCRM extends RestController {
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
                $idCRM          = 'CRM_'.md5(time()."crm");
                
                $storeTransaksi['ID_TRANS']         = $idTrans;
                $storeTransaksi['ID_USERS']         = $param['idUser'];
                $storeTransaksi['ID_MAPPING']       = $param['idMapping'];
                $this->db->insert('TRANSACTION', $storeTransaksi);
                
                $storeCRM['ID_MEETING']         = $idCRM;
                $storeCRM['ID_TRANS']           = $idTrans;
                $storeCRM['RUANG_MEETING']      = $param['ruang'];
                $storeCRM['TGL_MEETING']        = $param['tgl'];
                $storeCRM['TGLCEK1_MEETING']    = $param['cek1']['tgl'];
                $storeCRM['TGLCEK2_MEETING']    = $param['cek2']['tgl'];
                $storeCRM['TGLCEK3_MEETING']    = $param['cek3']['tgl'];
                $storeCRM['TGLCEK4_MEETING']    = $param['cek4']['tgl'];
                
                $arr['viewer']   = array();
                $arr['board']    = array();
                $arr['lcd']      = array();
                $arr['screen']   = array();
                $arr['spidol']   = array();

                for($x = 1; $x <= 4; $x++){
                    array_push($arr['viewer'], $param['cek'.$x]['viewer']);                    
                    array_push($arr['board'], $param['cek'.$x]['board']);                    
                    array_push($arr['lcd'], $param['cek'.$x]['lcd']);                    
                    array_push($arr['screen'], $param['cek'.$x]['screen']);                    
                    array_push($arr['spidol'], $param['cek'.$x]['spidol']);
                }

                $storeCRM['VIEWER_MEETING']  = implode(';', $arr['viewer']);
                $storeCRM['BOARD_MEETING']   = implode(';', $arr['board']);
                $storeCRM['LCD_MEETING']     = implode(';', $arr['lcd']);
                $storeCRM['SCREEN_MEETING']  = implode(';', $arr['screen']);
                $storeCRM['SPIDOL_MEETING']  = implode(';', $arr['spidol']);
                $this->db->insert('FORM_MEETING', $storeCRM);

                
                $flow = $this->db->get_where('FLOW', ['ID_MAPPING' => $mapping[0]->ID_MAPPING])->result_array();
                for($i = 1; $i <= 15; $i++){
                    if(!empty($flow[0]['APP_'.$i]) && $flow[0]['APP_'.$i] != null){
                        $this->db->insert('DETAIL_APPROVAL', ['ID_TRANS' => $idTrans, 'ROLE_APP' => $flow[0]['APP_'.$i]]);
                    }else if($flow[0]['APP_1'] == null){
                        $this->db->where('ID_TRANS', $idTrans)->update('TRANSACTION', ['STAT_TRANS' => '2']);
                    }
                }
                
                $resLinkGenerated = $this->ContentPdf->generate(['idTrans' => $idTrans, 'orientation' => 'landscape']);
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
