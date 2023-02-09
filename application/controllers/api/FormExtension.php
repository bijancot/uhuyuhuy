<?php defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;

class FormExtension extends RestController {
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
                $idExtension    = 'EXT_'.md5(time()."extension");
                
                $storeTransaksi['ID_TRANS']         = $idTrans;
                $storeTransaksi['ID_USERS']         = $param['idUser'];
                $storeTransaksi['ID_MAPPING']       = $param['idMapping'];
                $this->db->insert('TRANSACTION', $storeTransaksi);

                $storeExtension['ID_EXTENSION']       = $idExtension;
                $storeExtension['ID_TRANS']    = $idTrans;
                $this->db->insert('FORM_EXTENSION', $storeExtension);

                foreach($param['detExtension'] as $item){
                    $storeDetNA['ID_EXTENSION']     = $idExtension;
                    $storeDetNA['NAMA_EXT']         = $item['nama'];
                    $storeDetNA['NRP_EXT']          = $item['nrp'];
                    $storeDetNA['JABATAN_EXT']      = $item['div'];
                    $storeDetNA['NOMOR_EXT']        = $item['noExtension'];
                    $storeDetNA['EXIST_EXT']        = $item['aksesExisting'];
                    $storeDetNA['BARU_EXT']         = $item['aksesBaru'];

                    $ct =  $item['ct']['principle'].';';
                    $ct .= $item['ct']['uthi'].';';
                    $ct .= $item['ct']['cabang'].';';
                    $ct .= $item['ct']['partner'].';';
                    $ct .= $item['ct']['customer'].';';
                    $ct .= $item['ct']['vendor'].';';
                    $ct .= $item['ct']['subCont'].';';
                    $ct .= $item['ct']['hotel'].';';
                    $storeDetNA['CONTACT_EXT']  = $ct;
                    $this->db->insert('DETAIL_EXTENSION', $storeDetNA);
                }
                
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
