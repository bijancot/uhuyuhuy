<?php defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;

class FormExternalWorkOrder extends RestController {
    public function __construct() {
        parent::__construct();
        $this->load->helper("url");
        $this->load->library(array('upload', 'image_lib'));
    }

    public function index_post(){
        $param = $this->post();
        if(!empty($param['idUser']) && !empty($param['idMapping']) && !empty($param['intrKepada']) && !empty($param['intrDari']) && !empty($param['deptDiv']) && !empty($param['pekerjaan']) && !empty($param['noReg']) && !empty($param['reqDate']) && !empty($param['pages']) && !empty($param['cc']) && !empty($param['detEwo'])){
            $user       = $this->db->get_where('USERS', ['ID_USERS' => $param['idUser']])->result();
            $mapping    = $this->db->get_where('MAPPING', ['ID_MAPPING' => $param['idMapping']])->result();
            if($user != null && $mapping != null){
                $idTrans        = 'TRANS_'.md5(time()."trans");
                $idEwo          = 'EWO_'.md5(time()."ewo");
                
                $storeTransaksi['ID_TRANS']         = $idTrans;
                $storeTransaksi['ID_USERS']         = $param['idUser'];
                $storeTransaksi['ID_MAPPING']       = $param['idMapping'];
                $this->db->insert('TRANSACTION', $storeTransaksi);
                
                $storeFrmEwo['ID_EWO']              = $idEwo;
                $storeFrmEwo['ID_TRANS']            = $idTrans;
                $storeFrmEwo['INTRUKSIKEPADA_EWO']  = $param['intrKepada'];
                $storeFrmEwo['INTRUKSIDARI_EWO']    = $param['intrDari'];
                $storeFrmEwo['DEPTDIV_EWO']         = $param['deptDiv'];
                $storeFrmEwo['PEKERJAAN_EWO']       = $param['pekerjaan'];
                $storeFrmEwo['REG_EWO']             = $param['noReg'];
                $storeFrmEwo['REQUEST_EWO']         = $param['reqDate'];
                $storeFrmEwo['PAGES_EWO']           = $param['pages'];
                $storeFrmEwo['CC_EWO']              = $param['cc'];
                $this->db->insert('FORM_EWO', $storeFrmEwo);

                foreach($param['detEwo'] as $item){
                    $storeDetFrmEwo['ID_EWO']       = $idEwo;
                    $storeDetFrmEwo['ITEM_EWO']     = $item['item'];
                    $storeDetFrmEwo['LOKASI_EWO']   = $item['lokasi'];
                    $storeDetFrmEwo['TGL_EWO']      = $item['tglDiminta'];
                    $storeDetFrmEwo['TT_EWO']       = $item['troubleTicket'];
                    $storeDetFrmEwo['KET_EWO']      = $item['keterangan'];
                    $this->db->insert('DETAIL_EWO', $storeDetFrmEwo);
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
