<?php defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;

class FormICGS extends RestController {
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
                $idICGS         = 'ICGS_'.md5(time()."icgs");
                
                $storeTransaksi['ID_TRANS']         = $idTrans;
                $storeTransaksi['ID_USERS']         = $param['idUser'];
                $storeTransaksi['ID_MAPPING']       = $param['idMapping'];
                $this->db->insert('TRANSACTION', $storeTransaksi);
                
                $storeICGS['ID_GENSET']         = $idICGS;
                $storeICGS['ID_TRANS']          = $idTrans;
                $storeICGS['TGL_GENSET']        = $param['tgl'];
                $storeICGS['LOKASI_GENSET']     = $param['lokasi'];
                $storeICGS['ENGINE_GENSET']     = $param['engine'];
                $storeICGS['MODEL_GENSET']      = $param['engineModel'];
                $storeICGS['SERIAL_GENSET']     = $param['serial'];
                $storeICGS['GENO_GENSET']       = $param['geno'];
                $storeICGS['SERIAL2_GENSET']    = $param['serial2'];
                $storeICGS['HM_GENSET']         = $param['hourMeter'];
                $storeICGS['ENGINE_OIL']        = $param['egnOil'][0]['status'].";".$param['egnOil'][0]['catatan'];
                $storeICGS['ENGINE_OP']         = $param['egnOilPress'][0]['status'].";".$param['egnOilPress'][0]['catatan'];
                $storeICGS['RADIATOR']          = $param['radiator'][0]['status'].";".$param['radiator'][0]['catatan'];
                $storeICGS['RADIATOR_HOSE']     = $param['radiatorHose'][0]['status'].";".$param['radiatorHose'][0]['catatan'];
                $storeICGS['FAN_BELT']          = $param['fanBelt'][0]['status'].";".$param['fanBelt'][0]['catatan'];
                $storeICGS['BATTERY']           = $param['battery'][0]['status'].";".$param['battery'][0]['catatan'];
                $storeICGS['ELECTROLYT']        = $param['electrolyt'][0]['status'].";".$param['electrolyt'][0]['catatan'];
                $storeICGS['STARTER_MOTOR']     = $param['starterMotor'][0]['status'].";".$param['starterMotor'][0]['catatan'];
                $storeICGS['OIL_PRESS']         = $param['oilPressIndicator'][0]['status'].";".$param['oilPressIndicator'][0]['catatan'];
                $storeICGS['AMP_METER']         = $param['ampereMeter'][0]['status'].";".$param['ampereMeter'][0]['catatan'];
                $storeICGS['FREQ_METER']        = $param['freqMeter'][0]['status'].";".$param['freqMeter'][0]['catatan'];
                $storeICGS['VOLT_METER']        = $param['voltMeter'][0]['status'].";".$param['voltMeter'][0]['catatan'];
                $storeICGS['RELAY']             = $param['relay'][0]['status'].";".$param['relay'][0]['catatan'];
                $storeICGS['MCB']               = $param['mcb'][0]['status'].";".$param['mcb'][0]['catatan'];
                $storeICGS['TERMINAL']          = $param['terminal'][0]['status'].";".$param['terminal'][0]['catatan'];
                $storeICGS['EMERGENCY_STOP']    = $param['emergency'][0]['status'].";".$param['emergency'][0]['catatan'];
                $storeICGS['RUANGAN']           = $param['ruangan'][0]['status'].";".$param['ruangan'][0]['catatan'];
                $storeICGS['RUNNING_TEST']      = $param['runningTest'][0]['status'].";".$param['runningTest'][0]['catatan'];
                $storeICGS['PROB_IDENT']        = $param['probIdentification'];
                $storeICGS['ROOT_CAUSE']        = $param['rootCause'];
                $storeICGS['CORRECTIVE_ACT']    = $param['correctAct'];
                $storeICGS['PREVENT_ACT']       = $param['preventAct'];
                $storeICGS['DEADLINE']          = $param['deadLine'];
                $storeICGS['STATUS_GENSET']     = $param['status'];                
                $storeICGS['COND_GENSET']       = $param['condGenset'];
                $this->db->insert('FORM_GENSET', $storeICGS);
                
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
            $genset = $this->db->get_where('FORM_GENSET', ['ID_TRANS' => $param['idTrans']])->result();
            $user   = $this->db->get_where('USERS', ['ID_USERS' => $param['idUser']])->result();
            if($user != null && $genset != null){
                $file = $this->upload_image($user[0]->USER_USERS);
                $this->db->where('ID_TRANS', $param['idTrans'])->update('FORM_GENSET', ['PIC' => $file]);

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
        $newPath = './uploads/assets/pic/genset/'.$username.'/';
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

                return base_url('/uploads/assets/pic/genset/'.$username.'/'.$gambar);
            }
                      
        }else{
            return base_url('images/ttd/default.png');
        }
    }
}
