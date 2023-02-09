<?php defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;

class FormIdentifikasi extends RestController {
    public function __construct() {
        parent::__construct();
        $this->load->helper("url");
        $this->load->library(array('upload', 'image_lib'));
    }

    public function index_post(){
        $param = $this->post();
        if(!empty($param['idUser']) && !empty($param['idMapping']) && !empty($param['detIdentifikasi'])){
            $user       = $this->db->get_where('USERS', ['ID_USERS' => $param['idUser']])->result();
            $mapping    = $this->db->get_where('MAPPING', ['ID_MAPPING' => $param['idMapping']])->result();
            if($user != null && $mapping != null){
                $idTrans        = 'TRANS_'.md5(time()."trans");
                $idIdentifikasi = 'IDENT_'.md5(time()."identifikasi");
                
                $storeTransaksi['ID_TRANS']         = $idTrans;
                $storeTransaksi['ID_USERS']         = $param['idUser'];
                $storeTransaksi['ID_MAPPING']       = $param['idMapping'];
                $this->db->insert('TRANSACTION', $storeTransaksi);

                $storeIdentifikasi['ID_IDENTIFIKASI']   = $idIdentifikasi;
                $storeIdentifikasi['ID_TRANS']          = $idTrans;
                $this->db->insert('FORM_IDENTIFIKASI', $storeIdentifikasi);

                foreach($param['detIdentifikasi'] as $item){
                    $storeDetIdentifikasi['ID_IDENTIFIKASI']          = $idIdentifikasi;
                    $storeDetIdentifikasi['TEMUAN_IDENTIFIKASI']      = $item['temuan'];
                    $storeDetIdentifikasi['TGL_IDENTIFIKASI']         = $item['tgl'];
                    $storeDetIdentifikasi['KATEGORI_IDENTIFIKASI']    = $item['kategori'];
                    $storeDetIdentifikasi['LOKASI_IDENTIFIKASI']      = $item['lokasi'];
                    $storeDetIdentifikasi['USER_IDENTIFIKASI']        = $item['user'];
                    $this->db->insert('DETAIL_IDENTIFIKASI', $storeDetIdentifikasi);
                }
                
                $flow = $this->db->get_where('FLOW', ['ID_MAPPING' => $mapping[0]->ID_MAPPING])->result_array();
                for($i = 1; $i <= 15; $i++){
                    if(!empty($flow[0]['APP_'.$i]) && $flow[0]['APP_'.$i] != null){
                        $this->db->insert('DETAIL_APPROVAL', ['ID_TRANS' => $idTrans, 'ROLE_APP' => $flow[0]['APP_'.$i]]);
                    }else if($flow[0]['APP_1'] == null){
                        $this->db->where('ID_TRANS', $idTrans)->update('TRANSACTION', ['STAT_TRANS' => '2']);
                    }
                }
                
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
            $identifikasi = $this->db->get_where('FORM_IDENTIFIKASI', ['ID_TRANS' => $param['idTrans']])->result();
            $user   = $this->db->get_where('USERS', ['ID_USERS' => $param['idUser']])->result();
            $statUpload   = $param['statUpload'];
            $totalUpload  = $param['totalUpload'];
            if($statUpload <= $totalUpload && $user != null && $identifikasi != null){
                $resUpload               = $this->upload_file($user[0]->USER_USERS);
                $resUpload['keterangan'] = $param['keterangan'];
                if($identifikasi[0]->ATTACHMENT_IDENTIFIKASI != null){
                    $resUpload['keterangan'] = $identifikasi[0]->KETATTACHMENT_IDENTIFIKASI.';'.$param['keterangan'];
                    $resUpload['link']       = $identifikasi[0]->ATTACHMENT_IDENTIFIKASI.';'.$resUpload['link'];
                }              
                $this->db->where('ID_TRANS', $param['idTrans'])->update('FORM_IDENTIFIKASI', ['ATTACHMENT_IDENTIFIKASI' => $resUpload['link'], 'KETATTACHMENT_IDENTIFIKASI' => $resUpload['keterangan']]);

                if($statUpload == $totalUpload){
                    $resLinkGenerated = $this->ContentPdf->generate(['idTrans' => $param['idTrans'], 'orientation' => 'portrait']);
                    $this->db->where('ID_TRANS', $param['idTrans'])->update('TRANSACTION', ['PATH_TRANS' => base_url($resLinkGenerated)]);
                }
                $this->response(['status' => true, 'message' => 'Data berhasil ditambahkan'], 200);
            }else{
                $this->response(['status' => false, 'message' => 'Data user atau transaksi pvrv tidak ditemukan / Selesai mengupload'], 200);
            }
        }else{
            $this->response(['status' => false, 'message' => 'Parameter tidak cocok'], 200);
        }
    }
    function upload_file($username){
        $date = date('Ymd');
        $newPath = './uploads/assets/dokpend/identifikasi/'.$username.'/'.$date.'/';
        if(!is_dir($newPath)){
            mkdir($newPath, 0777, TRUE);
        }
        $config = array();
        $config['upload_path']          = $newPath;
        $config['allowed_types']        = 'gif|jpg|png|jpeg|bmp';
        $config['max_size']             = 2000;
        $config['encrypt_name']         = FALSE;

        $this->upload->initialize($config);
    
        if ( ! $this->upload->do_upload('file') ) {
            return ['link' => base_url('images/ttd/default.png'), 'fileName' => ''];
        } else {
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

            $upname = $gbr['file_name'];

            return ['link' => base_url('/uploads/assets/dokpend/identifikasi/'.$username.'/'.$date.'/'.$upname), 'fileName' => $upname];
        }             
    }
}
