<?php defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;

class FormSnack extends RestController {
    public function __construct() {
        parent::__construct();
        $this->load->helper("url");
        $this->load->library(array('upload', 'image_lib'));
    }

    public function index_get(){
        $param = $this->get();
        if(!empty($param['idTrans'])){
            $formSnack = $this->db->get_where('V_FORM_SNACK', ['ID_TRANS' => $param['idTrans']])->result();
            if($formSnack != null){
                $formSnack[0]->DETAIL_SNACK = $this->db->get_where('DETAIL_SNACK', ['ID_SNACK' => $formSnack[0]->ID_SNACK])->result();
                $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $formSnack[0]], 200);
            }else{
                $this->response(['status' => false, 'message' => 'Data tidak ditemukan'], 200);
            }
        }else{
            $this->response(['status' => false, 'message' => 'Parameter tidak cocok'], 200);
        }
    }

    public function detail_get($idSnack){
        $detSnacks = $this->db->get_where('V_DETAIL_SNACK', ['ID_SNACK' => $idSnack])->result();
        if($detSnacks != null){
            $res['PATH_TRANS']      = $detSnacks[0]->PATH_TRANS;
            $res['DETAIL_SNACK']    = $detSnacks;
            $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $res], 200);
        }else{
            $this->response(['status' => false, 'message' => 'Data tidak ditemukan'], 200);
        }
    }

    public function index_post(){
        $param = $this->post();
        if(!empty($param['idUser']) && !empty($param['idMapping']) && !empty($param['tglSnack']) && !empty($param['divisiSnack']) && !empty($param['keperluanSnack']) && !empty($param['detSnack'])){
            $user       = $this->db->get_where('USERS', ['ID_USERS' => $param['idUser']])->result();
            $mapping    = $this->db->get_where('MAPPING', ['ID_MAPPING' => $param['idMapping']])->result();
            if($user != null && $mapping != null){
                $idTrans        = 'TRANS_'.md5(time()."trans");
                $idSnack        = 'SNACK_'.md5(time()."snack");
                
                $storeTransaksi['ID_TRANS']         = $idTrans;
                $storeTransaksi['ID_USERS']         = $param['idUser'];
                $storeTransaksi['ID_MAPPING']       = $param['idMapping'];
                $this->db->insert('TRANSACTION', $storeTransaksi);
                
                $storeFrmSnack['ID_SNACK']          = $idSnack;
                $storeFrmSnack['ID_TRANS']          = $idTrans;
                $storeFrmSnack['TGL_SNACK']         = $param['tglSnack'];
                $storeFrmSnack['DIVISI_SNACK']      = $param['divisiSnack'];
                $storeFrmSnack['KEPERLUAN_SNACK']   = $param['keperluanSnack'];
                $this->db->insert('FORM_SNACK', $storeFrmSnack);

                foreach($param['detSnack'] as $item){
                    $storeDetFrmSnack['ID_SNACK']       = $idSnack;
                    $storeDetFrmSnack['JENIS_SNACK']    = $item['jenisSnack'];
                    $storeDetFrmSnack['JML_SNACK']      = $item['jmlSnack'];
                    $this->db->insert('DETAIL_SNACK', $storeDetFrmSnack);
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
