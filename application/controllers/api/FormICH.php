<?php defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;

class FormICH extends RestController {
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
                $idICH          = 'ICH_'.md5(time()."ich");
                
                $storeTransaksi['ID_TRANS']         = $idTrans;
                $storeTransaksi['ID_USERS']         = $param['idUser'];
                $storeTransaksi['ID_MAPPING']       = $param['idMapping'];
                $this->db->insert('TRANSACTION', $storeTransaksi);
                
                $storeICH['ID_HYDRANT']         = $idICH;
                $storeICH['ID_TRANS']           = $idTrans;
                $storeICH['TGL_HYDRANT']        = $param['tgl'];
                $storeICH['LOKASI_HYDRANT']     = $param['lokasi'];                                    
                $storeICH['CATATAN_HYDRANT']    = $param['catatan'];

                $arr = array();
                foreach ($param['sistemPipa'] as $item) {
                    array_push($arr, $item['status']);
                }
                $storeICH['SISTEM_PEMIPAAN'] = implode(';', $arr);

                $arr = array();
                foreach ($param['jockey'] as $item) {
                    array_push($arr, $item['status']);
                }
                $storeICH['JOCKEY_PUMP'] = implode(';', $arr);

                $arr = array();
                foreach ($param['electric'] as $item) {
                    array_push($arr, $item['status']);
                }
                $storeICH['ELECTRIC_PUMP'] = implode(';', $arr);

                $arr = array();
                foreach ($param['diesel'] as $item) {
                    array_push($arr, $item['status']);
                }
                $storeICH['DIESEL_PUMP'] = implode(';', $arr);

                $arr = array();
                foreach ($param['panel'] as $item) {
                    array_push($arr, $item['status']);
                }
                $storeICH['PANEL_HYDRANT'] = implode(';', $arr);
                $this->db->insert('FORM_HYDRANT', $storeICH);
                
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
            $genset = $this->db->get_where('FORM_HYDRANT', ['ID_TRANS' => $param['idTrans']])->result();
            $user   = $this->db->get_where('USERS', ['ID_USERS' => $param['idUser']])->result();
            if($user != null && $genset != null){
                $file = $this->upload_image($user[0]->USER_USERS);
                $this->db->where('ID_TRANS', $param['idTrans'])->update('FORM_HYDRANT', ['PIC' => $file]);

                $resLinkGenerated = $this->ContentPdf->generate(['idTrans' => $param['idTrans'], 'orientation' => 'portrait']);
                $this->db->where('ID_TRANS', $param['idTrans'])->update('TRANSACTION', ['PATH_TRANS' => base_url($resLinkGenerated)]);
                $this->response(['status' => true, 'message' => 'Data berhasil ditambahkan'], 200);
            }else{
                $this->response(['status' => false, 'message' => 'Data user atau transaksi mobil pribadi tidak ditemukan'], 200);
            }
        }else{
            $this->response(['status' => false, 'message' => 'Parameter tidak cocok'], 200);
        }
    }

    function upload_image($username){
        $newPath = './uploads/assets/pic/hydrant/'.$username.'/';
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

                return base_url('/uploads/assets/pic/hydrant/'.$username.'/'.$gambar);
            }
                      
        }else{
            return base_url('images/ttd/default.png');
        }
    }
}
