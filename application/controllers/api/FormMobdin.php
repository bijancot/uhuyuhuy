<?php defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;

class FormMobdin extends RestController {
    public function __construct() {
        parent::__construct();
        $this->load->helper("url");
        $this->load->library(array('upload', 'image_lib'));
    }

    public function index_post(){
        $param = $this->post();
        if(!empty($param['idUser']) && !empty($param['idMapping']) && !empty($param['pengemudi']) && !empty($param['tglPinjam']) && !empty($param['tglKembali']) && !empty($param['divDept']) && !empty($param['nopol']) && !empty($param['jamBerangkat']) && !empty($param['jamPulang']) && $param['kmAwal'] != '' && $param['kmAkhir'] != '' && !empty($param['catatan']) && !empty($param['detMobdin'])){
            $user       = $this->db->get_where('USERS', ['ID_USERS' => $param['idUser']])->result();
            $mapping    = $this->db->get_where('MAPPING', ['ID_MAPPING' => $param['idMapping']])->result();
            if($user != null && $mapping != null){
                $idTrans        = 'TRANS_'.md5(time()."trans");
                $idMobdin       = 'MOBDIN_'.md5(time()."mobdin");
                
                $storeTransaksi['ID_TRANS']         = $idTrans;
                $storeTransaksi['ID_USERS']         = $param['idUser'];
                $storeTransaksi['ID_MAPPING']       = $param['idMapping'];
                $this->db->insert('TRANSACTION', $storeTransaksi);

                $storeMobdin['ID_MOBDIN']           = $idMobdin;
                $storeMobdin['ID_TRANS']            = $idTrans;
                $storeMobdin['PEMINJAM_MOBDIN']     = $user[0]->NAMA_USERS;
                $storeMobdin['PENGEMUDI_MOBDIN']    = $param['pengemudi'];
                $storeMobdin['TGLPINJAM_MOBDIN']    = $param['tglPinjam'];
                $storeMobdin['TGLAMBIL_MOBDIN']     = $param['tglKembali'];
                $storeMobdin['DD_MOBDIN']           = $param['divDept'];
                $storeMobdin['NOPOL_MOBDIN']        = $param['nopol'];
                $storeMobdin['JAMBERANGKAT_MOBDIN'] = $param['jamBerangkat'];
                $storeMobdin['JAMPULANG_MOBDIN']    = $param['jamPulang'];
                $storeMobdin['KMAWAL_MOBDIN']       = $param['kmAwal'];
                $storeMobdin['KMAKHIR_MOBDIN']      = $param['kmAkhir'];
                $storeMobdin['CATATAN_MOBDIN']      = $param['catatan'];
                $this->db->insert('FORM_MOBDIN', $storeMobdin);

                foreach($param['detMobdin'] as $item){
                    $storeDetMobdin['ID_MOBDIN']        = $idMobdin;
                    $storeDetMobdin['TUJUAN_MOBDIN']    = $item['tujuan'];
                    $storeDetMobdin['KEPERLUAN_MOBDIN'] = $item['keperluan'];
                    $this->db->insert('DETAIL_MOBDIN', $storeDetMobdin);
                }
                
                $flow = $this->db->get_where('FLOW', ['ID_MAPPING' => $mapping[0]->ID_MAPPING])->result_array();
                for($i = 1; $i <= 15; $i++){
                    if(!empty($flow[0]['APP_'.$i]) && $flow[0]['APP_'.$i] != null){
                        $this->db->insert('DETAIL_APPROVAL', ['ID_TRANS' => $idTrans, 'ROLE_APP' => $flow[0]['APP_'.$i]]);
                    }else if($flow[0]['APP_1'] == null){
                        $this->db->where('ID_TRANS', $idTrans)->update('TRANSACTION', ['STAT_TRANS' => '2']);
                    }
                }
                
                $this->ContentPdf->generate(['idTrans' => $idTrans, 'orientation' => 'portrait']);
                $this->pusherjs->push();
                $this->response(['status' => true, 'message' => 'Data berhasil ditambahkan', 'idTrans' => $idTrans], 200);
            }else{
                $this->response(['status' => false, 'message' => 'Data user atau mapping tidak ditemukan'], 200);
            }
        }else{
            $this->response(['status' => false, 'message' => 'Parameter tidak cocok'], 200);
        }
    }
    public function upload_post(){
        $param = $this->post();
        if(!empty($param['idTrans']) && !empty($param['idUser'])){
            $mobdin = $this->db->get_where('FORM_MOBDIN', ['ID_TRANS' => $param['idTrans']])->result();
            $user   = $this->db->get_where('USERS', ['ID_USERS' => $param['idUser']])->result();
            if($user != null && $mobdin != null){
                $file = $this->upload_image($user[0]->USER_USERS);
                $this->db->where('ID_TRANS', $param['idTrans'])->update('FORM_MOBDIN', ['ATTACHMENT_MOBDIN' => $file]);

                $resLinkGenerated = $this->ContentPdf->generate(['idTrans' => $param['idTrans'], 'orientation' => 'portrait']);
                $this->db->where('ID_TRANS', $param['idTrans'])->update('TRANSACTION', ['PATH_TRANS' => base_url($resLinkGenerated)]);
                $this->response(['status' => true, 'message' => 'Data berhasil ditambahkan'], 200);
            }else{
                $this->response(['status' => false, 'message' => 'Data user atau transaksi mobil dinas tidak ditemukan'], 200);
            }
        }else{
            $this->response(['status' => false, 'message' => 'Parameter tidak cocok'], 200);
        }
    }

    public function nopol_put(){
        $param = $this->put();
        if(!empty($param['idUser'] && !empty($param['idTrans']) && !empty('nopol'))){
           $user    = $this->db->get_where('USERS', ['ID_USERS' => $param['idUser']])->result();
           $trans   = $this->db->get_where('TRANSACTION', ['ID_TRANS' => $param['idTrans']])->result();
           if($user != null && $trans != null){
                $this->db->where('ID_TRANS', $param['idTrans'])->update('FORM_MOBDIN', ['NOPOL_MOBDIN' => $param['nopol']]);

                $this->response(['status' => true, 'message' => 'Data berhasil diubah'], 200);
           }else{
               $this->response(['status' => false, 'message' => 'Data user atau transaksi tidak ditemukan'], 200);
            }
        }else{
            $this->response(['status' => false, 'message' => 'Parameter tidak cocok'], 200);
        }
    }

    function upload_image($username){
        $newPath = './uploads/assets/sim/mobdin/'.$username.'/';
        if(!is_dir($newPath)){
            mkdir($newPath, 0777, TRUE);
        }
        $config['upload_path'] = $newPath; //path folder
        $config['allowed_types'] = 'gif|jpg|png|jpeg|bmp'; //type yang dapat diakses bisa anda sesuaikan
        $config['encrypt_name'] = TRUE; //Enkripsi nama yang terupload
 
        $this->upload->initialize($config);
        if(!empty($_FILES['file']['name'])){
 
            if ($this->upload->do_upload('file')){
                $gbr = $this->upload->data();
                //Compress Image
                $config['image_library']='gd2';
                $config['source_image']=$newPath.$gbr['file_name'];
                $config['create_thumb']= FALSE;
                $config['maintain_ratio']= true;
                // $config['quality']= '100%';
                $config['width']= 600;
                // $config['height']= 400;
                $config['new_image']= $newPath.$gbr['file_name'];
                $this->load->library('image_lib', $config);
                $this->image_lib->resize();
 
                $gambar=$gbr['file_name'];

                return base_url('/uploads/assets/sim/mobdin/'.$username.'/'.$gambar);
            }
                      
        }else{
            return base_url('images/ttd/default.png');
        }
    }
}
