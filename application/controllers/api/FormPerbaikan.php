<?php defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;

class FormPerbaikan extends RestController {
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
                $idPermohonan   = 'PERMO_'.md5(time()."permohonan");
                
                $storeTransaksi['ID_TRANS']         = $idTrans;
                $storeTransaksi['ID_USERS']         = $param['idUser'];
                $storeTransaksi['ID_MAPPING']       = $param['idMapping'];
                $this->db->insert('TRANSACTION', $storeTransaksi);
                
                $storePermohonan['ID_PERMOHONAN']       = $idPermohonan;
                $storePermohonan['ID_TRANS']            = $idTrans;
                $storePermohonan['TGL_PERMOHONAN']      = $param['tgl'];
                $storePermohonan['WAKTU_PERMOHONAN']    = $param['waktu'];
                $storePermohonan['NAMA_PEMOHON']        = $param['namaDari'];
                $storePermohonan['DIV_PEMOHON']         = $param['divisi'];
                $storePermohonan['EXT_PEMOHON']         = $param['extension'];
                $storePermohonan['JENIS_PERBAIKAN']     = $param['jenisPerbaikan'];
                $storePermohonan['ALASAN_PERBAIKAN']    = $param['alasan'];
                $storePermohonan['DIKERJAKAN']          = $param['dikerjakanOleh'];
                $storePermohonan['EST_WAKTU']           = $param['estWaktu'];
                $storePermohonan['EST_BIAYA']           = $param['estBiaya'];

                $namaPenerima = $this->db->select('NAMA_USERS')->where('ROLE_USERS', 'PICA')->where_not_in("USER_USERS", ['pica'])->get('USERS')->row();
                $storePermohonan['NAMA_PENERIMA']       = ($namaPenerima != null ? $namaPenerima->NAMA_USERS : '');

                $ttPenerima = $this->db->order_by('TGLOUT_PERMOHONAN', 'desc')->get('FORM_PERMOHONAN')->row();
                if($ttPenerima != null){
                    $dateTT  = substr($ttPenerima->TT_PENERIMA, 0,8);
                    $noRegis = substr($ttPenerima->TT_PENERIMA, 8);
                    $dateNow = date('Ymd');
                    if($dateTT != $dateNow){
                        $dateTT  = $dateNow;
                        $noRegis = '001';
                    }else{
                        $noRegis = sprintf("%03d", (int)++$noRegis);
                    }
                    $ttPenerima = $dateTT.$noRegis;
                }else{
                    $ttPenerima = date('Ymd').'001';
                }
                $storePermohonan['TT_PENERIMA'] = $ttPenerima;
                $this->db->insert('FORM_PERMOHONAN', $storePermohonan);
                
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
