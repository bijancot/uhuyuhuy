<?php defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;

class FormSidakCatering extends RestController {
    public function __construct() {
        parent::__construct();
        $this->load->helper("url");
        $this->load->library(array('upload', 'image_lib'));
    }

    public function index_post(){
        $param = $this->post();
        if(!empty($param['idUser']) && !empty($param['idMapping']) && !empty($param['perusahaan']) && !empty($param['pemilik']) && !empty($param['pengurus']) && !empty($param['alamat']) && !empty($param['tlp']) && !empty($param['jmlTenagaKerja']) && !empty($param['jmlTenagaKerja']) && !empty($param['kanwil']) ){
            $user       = $this->db->get_where('USERS', ['ID_USERS' => $param['idUser']])->result();
            $mapping    = $this->db->get_where('MAPPING', ['ID_MAPPING' => $param['idMapping']])->result();
            if($user != null && $mapping != null){
                $idTrans        = 'TRANS_'.md5(time()."trans");
                $idSidak        = 'SIDAK_'.md5(time()."sidak");
                
                $storeTransaksi['ID_TRANS']         = $idTrans;
                $storeTransaksi['ID_USERS']         = $param['idUser'];
                $storeTransaksi['ID_MAPPING']       = $param['idMapping'];
                $this->db->insert('TRANSACTION', $storeTransaksi);

                $storeSidak['ID_SIDAK_CATERING']    = $idSidak;
                $storeSidak['ID_TRANS']             = $idTrans;
                $storeSidak['PERUSHAAN_CATERING']   = $param['perusahaan'];
                $storeSidak['PEMILIK_CATERING']     = $param['pemilik'];
                $storeSidak['PENGURUS_CATERING']    = $param['pengurus'];
                $storeSidak['ALAMAT_CATERING']      = $param['alamat'];
                $storeSidak['TELEPON_CATERING']     = $param['tlp'];
                $storeSidak['FAX_CATERING']         = $param['fax'];
                $storeSidak['JMLKERJA_CATERING']    = $param['jmlTenagaKerja'];
                $storeSidak['LAYANI_CATERING']      = $param['perusahanDilayani'];
                $storeSidak['KANDEPNAKER_CATERING'] = $param['kandepnaker'];
                $storeSidak['KANWIL_CATERING']      = $param['kanwil'];
                $storeSidak['CATATAN_CATERING']     = $param['catatan'];

                $arr = array();
                foreach ($param['ptk'] as $item) {
                    array_push($arr, $item['status']);
                }
                $storeSidak['PTK'] = implode(';', $arr);

                $arr = array();
                foreach ($param['pkb_pm'] as $item) {
                    array_push($arr, $item['status']);
                }
                $storeSidak['PKB_PM'] = implode(';', $arr);
                
                $arr = array();
                foreach ($param['psl_pfm'] as $item) {
                    array_push($arr, $item['status']);
                }
                $storeSidak['PSL_FPM'] = implode(';', $arr);
                $this->db->insert('FORM_SIDAK_CATERING', $storeSidak);
                
                
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
