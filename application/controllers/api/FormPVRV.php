<?php defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;

class FormPVRV extends RestController {
    public function __construct() {
        parent::__construct();
        $this->load->helper("url");
        $this->load->library(array('upload', 'image_lib'));
    }

    public function index_post(){
        $param = $this->post();
        if(!empty($param['idUser']) && !empty($param['idMapping']) && !empty($param['detPVRV'])){
            $user       = $this->db->get_where('USERS', ['ID_USERS' => $param['idUser']])->result();
            $mapping    = $this->db->get_where('MAPPING', ['ID_MAPPING' => $param['idMapping']])->result();
            if($user != null && $mapping != null){
                $idTrans        = 'TRANS_'.md5(time()."trans");
                $idPVRV         = 'PVRV_'.md5(time()."pvrv");
                
                $storeTransaksi['ID_TRANS']         = $idTrans;
                $storeTransaksi['ID_USERS']         = $param['idUser'];
                $storeTransaksi['ID_MAPPING']       = $param['idMapping'];
                $this->db->insert('TRANSACTION', $storeTransaksi);

                $storePVRV['ID_PVRV']           = $idPVRV;
                $storePVRV['ID_TRANS']          = $idTrans;
                $storePVRV['TO_PVRV']           = $param['byrKepada'];
                $storePVRV['NRP_PVRV']          = $param['nrp'];
                $storePVRV['KEPERLUAN_PVRV']    = $param['keperluan'];
                $storePVRV['NOPO_PVRV']         = $param['noPo'];
                $storePVRV['INVOICE_PVRV']      = $param['noInvoice'];
                $storePVRV['TYPE_PVRV']         = $param['type'];
                $storePVRV['PPN_PVRV']          = $param['noPPN'];
                $storePVRV['PPH_PVRV']          = $param['noPPH'];
                $storePVRV['TOTAL_PVRV']        = $param['totAmount'];
                $this->db->insert('FORM_PVRV', $storePVRV);

                foreach($param['detPVRV'] as $item){
                    $storeDetPVRV['ID_PVRV']        = $idPVRV;
                    $storeDetPVRV['ACCOUNT']        = $item['acc'];
                    $storeDetPVRV['DESCRIPTION']    = $item['desc'];
                    $storeDetPVRV['ALLOCATION']     = $item['alloc'];
                    $storeDetPVRV['BUSS_AREA']      = $item['bArea'];
                    $storeDetPVRV['COST_CENTER']    = $item['costCenter'];
                    $storeDetPVRV['AMOUNT']         = $item['amount'];
                    $storeDetPVRV['KETERANGAN']     = $item['ket'];
                    $this->db->insert('DETAIL_PVRV', $storeDetPVRV);
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
            $pvrv = $this->db->get_where('FORM_PVRV', ['ID_TRANS' => $param['idTrans']])->result();
            $user   = $this->db->get_where('USERS', ['ID_USERS' => $param['idUser']])->result();
            $statUpload   = $param['statUpload'];
            $totalUpload  = $param['totalUpload'];
            if($statUpload <= $totalUpload && $user != null && $pvrv != null){
                $resUpload = $this->upload_file($user[0]->USER_USERS);
                if($pvrv[0]->LINKDOKPEND_PVRV != null){
                    $resUpload['fileName']  = $pvrv[0]->DOKPEND_PVRV.';'.$resUpload['fileName'];
                    $resUpload['link']      = $pvrv[0]->LINKDOKPEND_PVRV.';'.$resUpload['link'];
                }              
                $this->db->where('ID_TRANS', $param['idTrans'])->update('FORM_PVRV', ['LINKDOKPEND_PVRV' => $resUpload['link'], 'DOKPEND_PVRV' => $resUpload['fileName']]);

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

    public function dokPend_get(){
        $param = $this->get();
        if(!empty($param['idTrans']) && !empty($param['idUser'])){
            $pvrv = $this->db->get_where('FORM_PVRV', ['ID_TRANS ' => $param['idTrans']])->result();
            $user = $this->db->get_where('USERS', ['ID_USERS ' => $param['idUser']])->result();
            if($user != null && $pvrv != null){
                $dokPends   = array();
                $link       = explode(';', $pvrv[0]->LINKDOKPEND_PVRV);
                $fileName   = explode(';', $pvrv[0]->DOKPEND_PVRV);
                $x = 0;
                foreach($link as $item){
                    $obj = new stdClass();
                    $obj->fileName = $fileName[$x];
                    $obj->link     = $link[$x];
                    array_push($dokPends, $obj);
                    $x++;
                }
                $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $dokPends], 200);
            }else{
                $this->response(['status' => false, 'message' => 'Data pvrv atau user tidak ditemukan'], 200);
            }
        }else{
            $this->response(['status' => false, 'message' => 'Parameter tidak cocok'], 200);
        }
    }
    
    function upload_file($username){
        $date = date('Ymd');
        $newPath = './uploads/assets/dokpend/pvrv/'.$username.'/'.$date.'/';
        if(!is_dir($newPath)){
            mkdir($newPath, 0777, TRUE);
        }
        $config = array();
        $config['upload_path']          = $newPath;
        $config['allowed_types']        = 'pdf';
        $config['max_size']             = 2000;
        $config['encrypt_name']         = FALSE;

        $this->upload->initialize($config);
    
        if ( ! $this->upload->do_upload('file') ) {
            return ['link' => base_url('images/ttd/default.png'), 'fileName' => ''];
        } else {
            $ups = $this->upload->data();

            $upname = $ups['file_name'];

            return ['link' => base_url('/uploads/assets/dokpend/pvrv/'.$username.'/'.$date.'/'.$upname), 'fileName' => $upname];
        }             
    }
}