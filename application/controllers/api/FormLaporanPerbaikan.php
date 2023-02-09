<?php defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;

class FormLaporanPerbaikan extends RestController {
    public function __construct() {
        parent::__construct();
        $this->load->helper("url");
        $this->load->library(array('upload', 'image_lib'));
    }

    public function index_post(){
        $param = $this->post();
        if(!empty($param['idUser']) && !empty($param['idMapping']) && !empty($param['tglPerbaikan']) && !empty($param['detPerbaikan'])){
            $user       = $this->db->get_where('USERS', ['ID_USERS' => $param['idUser']])->result();
            $mapping    = $this->db->get_where('MAPPING', ['ID_MAPPING' => $param['idMapping']])->result();
            if($user != null && $mapping != null){
                $idTrans        = 'TRANS_'.md5(time()."trans");
                $idPerbaikan        = 'PERBAIKAN_'.md5(time()."perbaikan");
                
                $storeTransaksi['ID_TRANS']         = $idTrans;
                $storeTransaksi['ID_USERS']         = $param['idUser'];
                $storeTransaksi['ID_MAPPING']       = $param['idMapping'];
                $this->db->insert('TRANSACTION', $storeTransaksi);
                
                $storePerbaikan['ID_PERBAIKAN']      = $idPerbaikan;
                $storePerbaikan['ID_TRANS']          = $idTrans;
                $storePerbaikan['TGL_PERBAIKAN']     = $param['tglPerbaikan'];
                $storePerbaikan['TGLOUT_PERBAIKAN']  = date('Y-m-d');
                $this->db->insert('FORM_PERBAIKAN', $storePerbaikan);

                foreach($param['detPerbaikan'] as $item){
                    $storeDetPerbaikan['ID_PERBAIKAN']       = $idPerbaikan;
                    $storeDetPerbaikan['LAPORAN_PERBAIKAN']  = $item['laporanPerbaikan'];
                    $storeDetPerbaikan['KATEGORI_PERBAIKAN'] = $item['kategoriPerbaikan'];
                    $storeDetPerbaikan['LOKASI_PERBAIKAN']   = $item['lokasiPerbaikan'];
                    $storeDetPerbaikan['USER_PERBAIKAN']     = $item['userPerbaikan'];
                    $storeDetPerbaikan['PIC_PERBAIKAN']      = $item['picPerbaikan'];
                    $storeDetPerbaikan['DURASI_PERBAIKAN']   = $item['durasiPerbaikan'];
                    $storeDetPerbaikan['STATUS_PERBAIKAN']   = $item['statusPerbaikan'];
                    $this->db->insert('DETAIL_PERBAIKAN', $storeDetPerbaikan);
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
