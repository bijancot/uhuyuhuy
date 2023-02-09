<?php defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Vendor extends RestController {
    
    public function __construct() {
        parent::__construct();
        $this->load->helper(array('url', 'download'));
        $this->load->library(array('upload', 'image_lib', 'pdfgenerator'));
    }

    public function index_get(){
        try{
            $DB2 = $this->load->database('gaSys2', true);
            $jwt = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));

            // Validation
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "PICP" && $user->user_role != "Admin PICP" && $user->user_role != "ADH") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);
            // End Validation

            $vendors = $DB2->order_by('vendor_no ASC')->get('master_vendor')->result();

            if($vendors != null){
                $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $vendors], 200);
            }else{
                $this->response(['status' => true, 'message' => 'Data tidak ditemukan'], 200);
            }
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }

    public function index_post(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->post();

            // Validation
            if(empty($param['vendorName']) || empty($param['vendorAddress']) || empty($param['vendorPIC'])|| empty($param['vendorEmail']) || empty($param['vendorPhone']) || empty($param['vendorSign'])|| empty($param['vendorCs'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            $vendors = $DB2->get_where('master_vendor', ['vendor_name' => $param['vendorName']])->result();
            if($vendors != null) $this->response(['status' => false, 'message' => 'Nama Vendor telah terdaftar!'], 200);
             // End Validation
            
            $storeVendor['vendor_name']             = $param['vendorName'];
            $storeVendor['vendor_address']           = $param['vendorAddress'];
            $storeVendor['vendor_contract_pic']     = $param['vendorPIC'];
            $storeVendor['vendor_email']            = $param['vendorEmail'];
            $storeVendor['vendor_phone']            = $param['vendorPhone'];
            $storeVendor['vendor_contract_sign']    = $param['vendorSign'];
            $storeVendor['vendor_cs_position']      = $param['vendorCs'];

            $DB2->insert('master_vendor', $storeVendor);

            $this->response(['status' => true, 'message' => 'Data berhasil ditambahkan!'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }

    public function changeStatus_put(){
        try{
            $DB2 = $this->load->database('gaSys2', true);
            $jwt = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param = $this->put();

            // Validation
            if(empty($param['idVendor'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            $vdr = $DB2->get_where('master_vendor', ['vendor_no' => $param['idVendor']])->result();
            if($vdr == null) $this->response(['status' => false, 'message' => 'vendor tidak terdaftar!'], 200);

            if($vdr[0]->vendor_status == 0){
                $param['status'] = 1;
            }else{
                $param['status'] = 0;
            };
            // End Validation

            $DB2->where('vendor_no', $param['idVendor'])->update('master_vendor', ['vendor_status' => $param['status']]);
            $this->response(['status' => true, 'message' => 'Berhasil mengubah status vendor!'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }

    public function detail_get($id){
        try{
            $DB2 = $this->load->database('gaSys2', true);
            $jwt = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));

            // Validation
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);
            // End Validation

            $vendor = $DB2->get_where('master_vendor', ['vendor_no' => $id])->row();
            if(!empty($vendor)){
                $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $vendor], 200);
            }else{
                $this->response(['status' => true, 'message' => 'Data tidak ditemukan'], 200);
            }
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
   
    public function index_put(){
        try{
            $DB2 = $this->load->database('gaSys2', true);
            $jwt = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param = $this->put();

            // Validation
            if(empty($param['idVendor'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);
            // End Validation

            $storeVendor['vendor_no']               = $param['idVendor'];
            $storeVendor['vendor_name']             = $param['vendorName'];
            $storeVendor['vendor_address']          = $param['vendorAddress'];
            $storeVendor['vendor_contract_pic']     = $param['vendorPIC'];
            $storeVendor['vendor_email']            = $param['vendorEmail'];
            $storeVendor['vendor_phone']            = $param['vendorPhone'];
            $storeVendor['vendor_contract_sign']    = $param['vendorSign'];
            $storeVendor['vendor_cs_position']      = $param['vendorCs'];            

            $DB2->where('vendor_no', $param['idVendor'])->update('master_vendor', $storeVendor);
            $this->response(['status' => true, 'message' => 'Berhasil mengubah data!'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function login_post(){
        $DB2 = $this->load->database('gaSys2', true);
        $param = $this->post();
        // Validation
        if(empty($param['email']) || empty($param['password'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
        $vendor = $DB2->get_where('master_vendor', ['vendor_email' => $param['email']])->result();
        if($vendor == null) $this->response(['status' => false, 'message' => 'Data tidak ditemukan' ], 200);
        // End Validation

        $resLogin = $DB2->get_where(
            'master_vendor', 
            ['vendor_email' => $param['email'], 'vendor_password' => hash('sha256', md5($param['password']))]
        )->result();

        if($resLogin != null){
            $sess = md5($param['email'].rand(100, 999));
            $DB2->where('vendor_email', $param['email'])->update('master_vendor', ['vendor_session' => $sess]);

            $vendor = array();
            $vendor['vendor_no']            = $resLogin[0]->vendor_no;
            $vendor['vendor_name']          = $resLogin[0]->vendor_name;
            $vendor['vendor_email']         = $resLogin[0]->vendor_email;
            $vendor['vendor_phone']         = $resLogin[0]->vendor_phone;
            $vendor['vendor_session']       = $sess;
            $vendor['iat']                  = time();
            $vendor['exp']                  = time() + 86400;

            $jwt = JWT::encode($vendor, $this->config->item('SECRET_KEY'), 'HS256');
            $this->response(['status' => true, 'message' => 'Data berhasil ditemukan' , 'data' => ['jwt' => $jwt]], 200);
        }else{
            $this->response(['status' => false, 'message' => 'Email atau password salah' ], 200);
        }
    }

    public function project_get(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->get();

            // Validation
            $vendor = $DB2->get_where('master_vendor', ['vendor_no' => $jwt->vendor_no])->row();
            if($jwt->vendor_session != $vendor->vendor_session || $vendor->vendor_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            // End Validation
            $projects = $this->queryGetProject($jwt->vendor_no, $param['limit'], $param['page']);

            if($projects['data'] == null)  $this->response(['status' => false, 'message' => 'Data tidak ditemukan!'], 200);
            $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'pagination' => $projects['pagination'], 'data' => $projects['data']], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }

    public function projectDetail_get(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->get();

            // Validation
            $vendor = $DB2->get_where('master_vendor', ['vendor_no' => $jwt->vendor_no])->row();
            if($jwt->vendor_session != $vendor->vendor_session || $vendor->vendor_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);

            $project = $DB2->get_where('master_project', ['project_no' => $param['projectNo']])->result();
            if($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);
            // End Validation

            $dnd_doc    = $this->queryGetTorList($param['projectNo']);
            $tender_doc = $this->queryGetTenderList($param['projectNo']);
            $weekly     = $this->queryGetWeeklyList($param['projectNo']);
            $bappbast   = $this->queryGetBappBastList($param['projectNo']);
            $penagihan  = $this->queryGetPenagihanList($param['projectNo']);
            $termin     = $this->getTermin($param['projectNo']);
            

            if($termin != null){
                $dataTermin['dp'] = array(
                    'percentage' => $termin['contract_dp'],
                    'amaount' => round($termin['contract_dp']/100*$project[0]->project_kontrak)
                );
                $value = explode(";", $termin['contract_progress']);
                $dataTermin['progress'] = array();
                foreach($value as $item){
                    $percent = explode("_", $item); 
                    array_push(
                        $dataTermin['progress'],
                        array(
                            'progress'=> $percent[1],
                            'percentage'=> $percent[0],
                            'amount_progress'=> round($percent[1]/100*$project[0]->project_kontrak)
                        )
                    );
                }
                $dataTermin['retency'] = array(
                    'percentage' => $termin['contract_retensi'],
                    'amount' => round($termin['contract_retensi']/100*$project[0]->project_kontrak)
                );
            }            

            $this->response(['status' => true, 'message' => ['project' => $project[0], 'termin' => $dataTermin,'weekly' => $weekly, 'bappbast' => $bappbast, 'penagihan' => $penagihan, 'dnd_doc' => $dnd_doc, 'tender_doc' => $tender_doc]], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }

    public function news_get(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->get();

            // Validation
            $vendor = $DB2->get_where('master_vendor', ['vendor_no' => $jwt->vendor_no])->row();
            if($jwt->vendor_session != $vendor->vendor_session || $vendor->vendor_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            // End Validation
            $news = $this->queryGetNews($param['limit'], $param['page']);

            if($news['data'] == null)  $this->response(['status' => false, 'message' => 'Data tidak ditemukan!'], 200);
            $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'pagination' => $news['pagination'], 'data' => $news['data']], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }

    public function newsDetail_get($id){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->get();

            // Validation
            $vendor = $DB2->get_where('master_vendor', ['vendor_no' => $jwt->vendor_no])->row();
            if($jwt->vendor_session != $vendor->vendor_session || $vendor->vendor_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            // End Validation
            $news = $DB2->get_where('master_news', ['news_id' => $id])->result();
            if($news == null) $this->response(['status' => false, 'message' => 'Data tidak ditemukan'], 200);
            $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $news[0]], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }

    public function faq_get(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->get();

            // Validation
            $vendor = $DB2->get_where('master_vendor', ['vendor_no' => $jwt->vendor_no])->row();
            if($jwt->vendor_session != $vendor->vendor_session || $vendor->vendor_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            // End Validation
            $faq = $DB2->get_where('master_faq', ['faq_ispublish' => '1'])->result();

            if($faq == null)  $this->response(['status' => false, 'message' => 'Data tidak ditemukan!'], 200);
            $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $faq], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
	
	public function proStand_get(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->get();

            // Validation
            $vendor = $DB2->get_where('master_vendor', ['vendor_no' => $jwt->vendor_no])->row();
            if($jwt->vendor_session != $vendor->vendor_session || $vendor->vendor_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            // End Validation
            $proStand = $DB2->get_where('master_pro_stand', ['pro_stand_publish' => '1'])->result();

            if($proStand == null)  $this->response(['status' => false, 'message' => 'Data tidak ditemukan!'], 200);
            $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $proStand], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }

    public function weekly_get(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->get();

            // Validation
            $vendor = $DB2->get_where('master_vendor', ['vendor_no' => $jwt->vendor_no])->row();
            if($jwt->vendor_session != $vendor->vendor_session || $vendor->vendor_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);            
            // End Validation
            $projects = $this->queryGetWeekly($jwt->vendor_no, $param['limit'], $param['page']);

            if($projects['data'] == null)  $this->response(['status' => false, 'message' => 'Data tidak ditemukan!'], 200);
            $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'pagination' => $projects['pagination'], 'data' => $projects['data']], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function weeklyDetail_get(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->get();

            // Validation
            if(empty($param['projectNo'])){
                $this->response(['status' => false, 'message' => 'Parameter tidak cocok!'], 200);
            }
            $vendor = $DB2->get_where('master_vendor', ['vendor_no' => $jwt->vendor_no])->row();
            if($jwt->vendor_session != $vendor->vendor_session || $vendor->vendor_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);            

            $project = $DB2->select('project_area, vendor_no, project_pic')->get_where('master_project', ['project_no' => $param['projectNo']])->result();
            if($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);
            if($jwt->vendor_no != $project[0]->vendor_no) $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses terhadap project!'], 200);
            // End Validation
            $projects   = $this->queryGetWeeklyDetail($param['projectNo'], $param['limit'], $param['page']);
            $pic        = $DB2->get_where('master_user', ['user_no' => $project[0]->project_pic])->row();
            $vendor     = $DB2->get_where('master_vendor', ['vendor_no' => $jwt->vendor_no])->row();
            
            $detail             = $this->getGeneralinfo($param['projectNo']);
            $detail['pic']      = $pic->user_name;
            $detail['konsultan']= $vendor->vendor_contract_sign;
            $termin             = $this->getTermin($param['projectNo']);
            
            if($termin != null){
                $dataTermin['dp_percentage']        = $termin['contract_dp'];
                $dataTermin['amount_dp']            =  round($termin['contract_dp']/100*$detail['project_kontrak']);
                $dataTermin['retency_percentage']   = $termin['contract_retensi'];
                $dataTermin['amount_retency']       =  round($termin['contract_retensi']/100*$detail['project_kontrak']);
                $i = 1;$value = explode(";", $termin['contract_progress']);
                foreach($value as $item){
                    $percent = explode("_", $item); 
                    $dataTermin['progress_'.$i] = $percent[1];
                    $dataTermin['percentage_'.$i] = $percent[0];
                    $dataTermin['amount_progress_'.$i] = round($percent[1]/100*$detail['project_kontrak']);
                    $i++;
                }
            }            

            if($projects['data'] == null)  $this->response(['status' => false, 'message' => 'Data tidak ditemukan!'], 200);
            $this->response([
                'status'        => true,
                'message'       => 'Data berhasil ditemukan', 
                'pagination'    => $projects['pagination'], 
                'data'          => ['termin' => $dataTermin, 'project' => $detail, 'weekly' => $projects['data']]
            ], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function weeklyInput_get(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->get();

            // Validation
            if(empty($param['projectNo'])){
                $this->response(['status' => false, 'message' => 'Parameter tidak cocok!'], 200);
            }
            $vendor = $DB2->get_where('master_vendor', ['vendor_no' => $jwt->vendor_no])->row();
            if($jwt->vendor_session != $vendor->vendor_session || $vendor->vendor_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);            

            $project = $DB2->select('project_area, vendor_no, project_pic')->get_where('master_project', ['project_no' => $param['projectNo']])->result();
            if($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);
            if($jwt->vendor_no != $project[0]->vendor_no) $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses terhadap project!'], 200);
            // End Validation
            $pic        = $DB2->get_where('master_user', ['user_no' => $project[0]->project_pic])->row();
            $vendor     = $DB2->get_where('master_vendor', ['vendor_no' => $jwt->vendor_no])->row();
            $lastTrans  = $DB2->order_by('vendor_weekly_date', 'DESC')->get_where('transaction_vendor_weekly', ['project_no' => $param['projectNo']], 1)->result();

            $detail['kode_area']        = $project[0]->project_area;
            $detail['pic']              = $pic->user_name;
            $detail['konsultan']        = $vendor->vendor_contract_sign;
            $detail['lastProgPlan']     = ($lastTrans != null ? $lastTrans[0]->vendor_weekly_progplan : 0);
            $detail['lastProgActual']   = ($lastTrans != null ? $lastTrans[0]->vendor_weekly_progactual : 0);

            $this->response([
                'status'        => true,
                'message'       => 'Data berhasil ditemukan', 
                'data'          => $detail
            ], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function weekly_post(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->post();

            // Validation
            if(empty($param['projectNo']) || empty($param['week']) || empty($param['progPlan']) || empty($param['progActual']) || empty($param['statReport'])){
                $this->response(['status' => false, 'message' => 'Parameter tidak cocok!'], 200);
            }

            $vendor = $DB2->get_where('master_vendor', ['vendor_no' => $jwt->vendor_no])->row();
            if($jwt->vendor_session != $vendor->vendor_session || $vendor->vendor_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            
            $project = $DB2->get_where('master_project', ['project_no' => $param['projectNo']])->result();
            if($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);
            if($jwt->vendor_no != $project[0]->vendor_no) $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses terhadap project!'], 200);

            
            if((float) $param['progActual'] < (float) $project[0]->project_prog_weekly){
                $this->response(['status' => false, 'message' => 'Progress actual tidak valid!'], 200);
            }
            // End Validation
            $uploadReport = $this->uploadReport(explode('.', $_FILES['docReport']['name'])[0]);
            if($uploadReport['status'] == false) $this->response(['status' => false, 'message' => $uploadReport['msg']], 200);
            
            $uploadProgress = $this->uploadProgress(explode('.', $_FILES['docProgress']['name'])[0]);
            if($uploadProgress['status'] == false) $this->response(['status' => false, 'message' => $uploadProgress['msg']], 200);

            $formData['project_no']                 = $param['projectNo'];
            $formData['vendor_weekly_week']         = $param['week'];
            $formData['vendor_weekly_progplan']     = $param['progPlan'];
            $formData['vendor_weekly_progactual']   = $param['progActual'];
            $formData['vendor_weekly_statreport']   = $param['statReport'];
            $formData['vendor_weekly_docreport']    = $uploadReport['link'];
            $formData['vendor_weekly_docprogress']  = $uploadProgress['link'];
            $formData['vendor_weekly_status']       = 0;
            $formData['vendor_weekly_date']         = date('Y-m-d H:i:s');
            $DB2->insert('transaction_vendor_weekly', $formData);

            $this->response(['status' => true, 'message' => 'Data berhasil terupload'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }

    public function bappBastType_get(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->get();

            // Validation
            $vendor = $DB2->get_where('master_vendor', ['vendor_no' => $jwt->vendor_no])->row();
            if($jwt->vendor_session != $vendor->vendor_session || $vendor->vendor_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);            
            // End Validation
            $type = $DB2->order_by('bappast_type_no ASC')->get('master_bapp_bast_type')->result();

            if($type != null){
                $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $type], 200);
            }else{
                $this->response(['status' => true, 'message' => 'Data tidak ditemukan'], 200);
            }
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }

    public function bappBast_get(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->get();

            // Validation
            $vendor = $DB2->get_where('master_vendor', ['vendor_no' => $jwt->vendor_no])->row();
            if($jwt->vendor_session != $vendor->vendor_session || $vendor->vendor_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);            
            // End Validation
            $projects = $this->queryGetBappBast($jwt->vendor_no, $param['limit'], $param['page']);

            if($projects['data'] == null)  $this->response(['status' => false, 'message' => 'Data tidak ditemukan!'], 200);
            $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'pagination' => $projects['pagination'], 'data' => $projects['data']], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }

    public function bappBastDetail_get(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->get();

            // Validation
            if(empty($param['projectNo'])){
                $this->response(['status' => false, 'message' => 'Parameter tidak cocok!'], 200);
            }
            $vendor = $DB2->get_where('master_vendor', ['vendor_no' => $jwt->vendor_no])->row();
            if($jwt->vendor_session != $vendor->vendor_session || $vendor->vendor_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);            

            $project = $DB2->select('project_area, vendor_no, project_pic')->get_where('master_project', ['project_no' => $param['projectNo']])->result();
            if($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);
            if($jwt->vendor_no != $project[0]->vendor_no) $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses terhadap project!'], 200);
            // End Validation
            $projects   = $this->queryGetBappBastDetail($param['projectNo'], $param['limit'], $param['page']);
            $pic        = $DB2->get_where('master_user', ['user_no' => $project[0]->project_pic])->row();
            $vendor     = $DB2->get_where('master_vendor', ['vendor_no' => $jwt->vendor_no])->row();

            $detail             = $this->getGeneralinfo($param['projectNo']);
            $detail['pic']      = $pic->user_name;
            $detail['konsultan']= $vendor->vendor_contract_sign;

            if($projects['data'] == null)  $this->response(['status' => false, 'message' => 'Data tidak ditemukan!'], 200);
            $this->response([
                'status'        => true,
                'message'       => 'Data berhasil ditemukan', 
                'pagination'    => $projects['pagination'], 
                'data'          => ['project' => $detail, 'bappbast' => $projects['data']]
            ], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function bappBastInput_get(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->get();

            // Validation
            if(empty($param['projectNo'])){
                $this->response(['status' => false, 'message' => 'Parameter tidak cocok!'], 200);
            }
            $vendor = $DB2->get_where('master_vendor', ['vendor_no' => $jwt->vendor_no])->row();
            if($jwt->vendor_session != $vendor->vendor_session || $vendor->vendor_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);            

            $project = $DB2->select('project_area, vendor_no, project_pic')->get_where('master_project', ['project_no' => $param['projectNo']])->result();
            if($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);
            if($jwt->vendor_no != $project[0]->vendor_no) $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses terhadap project!'], 200);
            // End Validation
            $pic        = $DB2->get_where('master_user', ['user_no' => $project[0]->project_pic])->row();
            $vendor     = $DB2->get_where('master_vendor', ['vendor_no' => $jwt->vendor_no])->row();
            $lastTrans  = $DB2->order_by('vendor_bapp_bast_date', 'DESC')->get_where('transaction_vendor_bapp_bast', ['project_no' => $param['projectNo']], 1)->result();

            $detail['kode_area']        = $project[0]->project_area;
            $detail['pic']              = $pic->user_name;
            $detail['konsultan']        = $vendor->vendor_contract_sign;
            $detail['lastProgress']     = ($lastTrans != null ? $lastTrans[0]->vendor_bapp_bast_progress : 0);

            $this->response([
                'status'        => true,
                'message'       => 'Data berhasil ditemukan', 
                'data'          => $detail
            ], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function bappBast_post(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->post();

            // Validation
            if(empty($param['projectNo']) || empty($param['typeNo']) || empty($param['progress']) || empty($_FILES['docReport']['name'])){
                $this->response(['status' => false, 'message' => 'Parameter tidak cocok!'], 200);
            }

            $vendor = $DB2->get_where('master_vendor', ['vendor_no' => $jwt->vendor_no])->row();
            if($jwt->vendor_session != $vendor->vendor_session || $vendor->vendor_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            
            $project = $DB2->get_where('master_project', ['project_no' => $param['projectNo']])->result();
            if($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);
            if($jwt->vendor_no != $project[0]->vendor_no) $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses terhadap project!'], 200);

            $lastTrans  = $DB2->order_by('vendor_bapp_bast_date', 'DESC')->get_where('transaction_vendor_bapp_bast', ['project_no' => $param['projectNo']], 1)->result();
            if($lastTrans != null){
                $progress         = (float)$lastTrans[0]->vendor_bapp_bast_progress;
                $progressInput    = (float)$param['progress'];

                if($progressInput < $progress){
                    $this->response(['status' => false, 'message' => 'Progress tidak valid!'], 200);
                }
            }
            // End Validation
            if($_FILES['docBAPPBAST']['name'] != null){
                $uploadFile = $this->uploadFile(explode('.', $_FILES['docBAPPBAST']['name'])[0], 'docBAPPBAST', 'bapp-bast/mandatory');
                if($uploadFile['status'] == false) $this->response(['status' => false, 'message' => $uploadFile['msg']], 200);
                $formData['vendor_bapp_bast_docname'] = $uploadFile['name'];
                $formData['vendor_bapp_bast_document'] = $uploadFile['link'];
            }
            if($_FILES['docReport']['name'] != null){
                $uploadFile = $this->uploadFile(explode('.', $_FILES['docReport']['name'])[0], 'docReport', 'bapp-bast/mandatory');
                if($uploadFile['status'] == false) $this->response(['status' => false, 'message' => $uploadFile['msg']], 200);
                $formData['vendor_bapp_bast_docreport'] = $uploadFile['link'];
            }
            if($_FILES['docApproval']['name'] != null){
                $uploadFile = $this->uploadFile(explode('.', $_FILES['docApproval']['name'])[0], 'docApproval', 'bapp-bast/other');
                if($uploadFile['status'] == false) $this->response(['status' => false, 'message' => $uploadFile['msg']], 200);
                $formData['vendor_bapp_bast_docapproval'] = $uploadFile['link'];
            }
            if($_FILES['docChecklist']['name'] != null){
                $uploadFile = $this->uploadFile(explode('.', $_FILES['docChecklist']['name'])[0], 'docChecklist', 'bapp-bast/other');
                if($uploadFile['status'] == false) $this->response(['status' => false, 'message' => $uploadFile['msg']], 200);
                $formData['vendor_bapp_bast_docchecklist'] = $uploadFile['link'];
            }            
            if($_FILES['docMaintenance']['name'] != null){
                $uploadFile = $this->uploadFile(explode('.', $_FILES['docMaintenance']['name'])[0], 'docMaintenance', 'bapp-bast/other');
                if($uploadFile['status'] == false) $this->response(['status' => false, 'message' => $uploadFile['msg']], 200);
                $formData['vendor_bapp_bast_docmaintenance'] = $uploadFile['link'];
            }            

            $formData['project_no']                                         = $param['projectNo'];
            $formData['vendor_bapp_bast_type']                              = $param['typeNo'];
            $formData['vendor_bapp_bast_progress']                          = $param['progress'];
            $formData['vendor_bapp_bast_status']                            = 0;
            $formData['vendor_bapp_bast_date']                              = date('Y-m-d H:i:s');
            $DB2->insert('transaction_vendor_bapp_bast', $formData);

            $DB2->where('project_no', $param['projectNo'])->update('master_project', ['project_prog_bapp_bast' => $param['progress']]);
            $this->response(['status' => true, 'message' => 'Data berhasil terupload'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function bappBastDropdown_get(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->get();

            // Validation
            if(empty($param['projectNo'])){
                $this->response(['status' => false, 'message' => 'Parameter tidak cocok!'], 200);
            }

            $vendor = $DB2->get_where('master_vendor', ['vendor_no' => $jwt->vendor_no])->row();
            if($jwt->vendor_session != $vendor->vendor_session || $vendor->vendor_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            
            $project = $DB2->get_where('master_project', ['project_no' => $param['projectNo']])->result();
            if($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);
            if($jwt->vendor_no != $project[0]->vendor_no) $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses terhadap project!'], 200);
            // End Validation

            $progTermin = $DB2->get_where('transaction_contract', ['project_no' => $param['projectNo']])->row();
            $termins = explode(';', $progTermin->contract_progress);
            $data = [];
            foreach ($termins as $termin) {
                $percent = explode('_', $termin)[0];
                if((float) $percent < (float) $project[0]->project_prog_weekly){
                    $temp['title']  = 'BAPP ('.$percent.'%)';
                    $temp['typeNo']   = '1';
                    $data[] = $temp;
                }else {
                    break;
                }
            }

            if($project[0]->project_prog_weekly >= 100){
                $temp['title']  = 'BAST';
                $temp['typeNo']   = '2';
                $data[] = $temp;
            }
            $this->response(['status' => true, 'message' => 'Berhasil mendapatkan data!', 'data' => $data], 200);

        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function bappBastGenerate_post(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->post();

            // Validation
            if(empty($param['projectNo']) || empty($param['docNo']) || empty($param['docType']) || empty($param['tglPembuatan']) || empty($param['nama'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok!'], 200);
            if(empty($param['badanUsaha']) || empty($param['jabatan']) || empty($param['alamat'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok!'], 200);
            if($param['docType'] == '1'){
                if(empty($param['mulaiPengerjaan']) || empty($param['akhirPengerjaan'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok']);
            }else if($param['docType'] == '2'){
                if(empty($param['pasal'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok'], 200);
            }else{
                $this->response(['status' => false, 'message' => 'Document Type Tidak Cocok!'], 200);
            }
            
            $vendor = $DB2->get_where('master_vendor', ['vendor_no' => $jwt->vendor_no])->row();
            if($jwt->vendor_session != $vendor->vendor_session || $vendor->vendor_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);            
            $project = $DB2->get_where('master_project', ['project_no' => $param['projectNo']])->result();
            if($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);
            $param['project'] = $project;
            // End Validation
            
            if($param['docType'] == '1'){
                $resGen = $this->document->genPdf('BAPP', $param);
            }else if($param['docType'] == '2'){
                $resGen = $this->document->genPdf('BAST', $param);
            }

            $this->response(['status' => true, 'message' => 'Berhasil generate BAPP/BAST!', 'link' => $resGen['link']], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function bappBastSubmit_post(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->post();

            // Validation
            if(empty($param['projectNo']) || empty($param['docType']) || empty($_FILES['docReport']['name'] || empty($_FILES['docBAPPBAST']['name']))) $this->response(['status' => false, 'message' => 'Paramter tidak cocok!'], 200);
            
            $vendor = $DB2->get_where('master_vendor', ['vendor_no' => $jwt->vendor_no])->row();
            if($jwt->vendor_session != $vendor->vendor_session || $vendor->vendor_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);            
            $project = $DB2->get_where('master_project', ['project_no' => $param['projectNo']])->result();
            if($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);
            $param['project'] = $project;
            // End Validation

            if($_FILES['docBAPPBAST']['name'] != null){
                $uploadFile = $this->uploadFile(explode('.', $_FILES['docBAPPBAST']['name'])[0], 'docBAPPBAST', 'bapp-bast/mandatory');
                if($uploadFile['status'] == false) $this->response(['status' => false, 'message' => $uploadFile['msg']], 200);
                $formData['vendor_bapp_bast_docname'] = $uploadFile['name'];
                $formData['vendor_bapp_bast_document'] = $uploadFile['link'];
            }
            if($_FILES['docReport']['name'] != null){
                $uploadFile = $this->uploadFile(explode('.', $_FILES['docReport']['name'])[0], 'docReport', 'bapp-bast/mandatory');
                if($uploadFile['status'] == false) $this->response(['status' => false, 'message' => $uploadFile['msg']], 200);
                $formData['vendor_bapp_bast_docreport'] = $uploadFile['link'];
            }
            if(!empty($_FILES['docApproval']['name']) != null){
                $uploadFile = $this->uploadFile(explode('.', $_FILES['docApproval']['name'])[0], 'docApproval', 'bapp-bast/other');
                if($uploadFile['status'] == false) $this->response(['status' => false, 'message' => $uploadFile['msg']], 200);
                $formData['vendor_bapp_bast_docapproval'] = $uploadFile['link'];
            }
            if(!empty($_FILES['docChecklist']['name']) != null){
                $uploadFile = $this->uploadFile(explode('.', $_FILES['docChecklist']['name'])[0], 'docChecklist', 'bapp-bast/other');
                if($uploadFile['status'] == false) $this->response(['status' => false, 'message' => $uploadFile['msg']], 200);
                $formData['vendor_bapp_bast_docchecklist'] = $uploadFile['link'];
            }            
            if(!empty($_FILES['docMaintenance']['name']) != null){
                $uploadFile = $this->uploadFile(explode('.', $_FILES['docMaintenance']['name'])[0], 'docMaintenance', 'bapp-bast/other');
                if($uploadFile['status'] == false) $this->response(['status' => false, 'message' => $uploadFile['msg']], 200);
                $formData['vendor_bapp_bast_docmaintenance'] = $uploadFile['link'];
            }            

            $formData['project_no']                                         = $param['projectNo'];
            $formData['vendor_bapp_bast_type']                              = $param['docType'];
            $formData['vendor_bapp_bast_date']                              = date('Y-m-d H:i:s');
            $DB2->insert('transaction_vendor_bapp_bast', $formData);

            $formType = "";
            if($param['docType'] == '1'){
                $formType = "4";
            }else if($param['docType'] == '2'){
                $formType = "5";
            }else if($param['docType'] == '3'){
                $formType = "6";
            }

            $formData2['approval_id']            = 'TRANS_'.md5(time()."trans");
            $formData2['mapping_id']             = $formType;
            $formData2['project_no']             = $param['projectNo'];
            $formData2['approval_path']          = $formData['vendor_bapp_bast_document'];
            $formData2['approval_filename']      = $formData['vendor_bapp_bast_docname'];
            $formData2['approval_date']          = date('Y-m-d H:i:s');
            $formData2['approval_send_vendor']   = "2";
            $formData2['approval_stat_propose']  = "1";
            $formData2['approval_status']        = "1";
            $formData2['approval_flag']          = "1";
            $DB2->insert('transaction_approval', $formData2);
            
            $mapping = $DB2->get_where('master_mapping', ['mapping_id' => $formType])->result_array();
            for($i = 1; $i <= 7; $i++){
                if(!empty($mapping[0]['mapping_app_'.$i]) && $mapping[0]['mapping_app_'.$i] != null){
                    $DB2->insert('transaction_approval_detail', ['approval_id' => $formData2['approval_id'], 'approval_detail_role' => $mapping[0]['mapping_app_'.$i]]);
                }
            }

            $this->response(['status' => true, 'message' => 'Berhasil submit data!'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function penagihan_get(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->get();

            // Validation
            $vendor = $DB2->get_where('master_vendor', ['vendor_no' => $jwt->vendor_no])->row();
            if($jwt->vendor_session != $vendor->vendor_session || $vendor->vendor_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);            
            // End Validation
            $projects = $this->queryGetPenagihan($jwt->vendor_no, $param['limit'], $param['page']);

            if($projects['data'] == null)  $this->response(['status' => false, 'message' => 'Data tidak ditemukan!'], 200);
            $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'pagination' => $projects['pagination'], 'data' => $projects['data']], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function penagihanDetail_get(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->get();

            // Validation
            if(empty($param['projectNo'])){
                $this->response(['status' => false, 'message' => 'Parameter tidak cocok!'], 200);
            }
            $vendor = $DB2->get_where('master_vendor', ['vendor_no' => $jwt->vendor_no])->row();
            if($jwt->vendor_session != $vendor->vendor_session || $vendor->vendor_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);            

            $project = $DB2->select('project_area, vendor_no, project_pic')->get_where('master_project', ['project_no' => $param['projectNo']])->result();
            if($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);
            if($jwt->vendor_no != $project[0]->vendor_no) $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses terhadap project!'], 200);
            // End Validation
            $projects   = $this->queryGetPenagihanDetail($param['projectNo'], $param['limit'], $param['page']);
            $pic        = $DB2->get_where('master_user', ['user_no' => $project[0]->project_pic])->row();
            $vendor     = $DB2->get_where('master_vendor', ['vendor_no' => $jwt->vendor_no])->row();

            $detail             = $this->getGeneralinfo($param['projectNo']);
            $detail['pic']      = $pic->user_name;
            $detail['konsultan']= $vendor->vendor_contract_sign;

            if($projects['data'] == null)  $this->response(['status' => false, 'message' => 'Data tidak ditemukan!'], 200);
            $this->response([
                'status'        => true,
                'message'       => 'Data berhasil ditemukan', 
                'pagination'    => $projects['pagination'], 
                'data'          => ['project' => $detail, 'penagihan' => $projects['data']]
            ], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function penagihanInput_get(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->get();

            // Validation
            if(empty($param['projectNo'])){
                $this->response(['status' => false, 'message' => 'Parameter tidak cocok!'], 200);
            }
            $vendor = $DB2->get_where('master_vendor', ['vendor_no' => $jwt->vendor_no])->row();
            if($jwt->vendor_session != $vendor->vendor_session || $vendor->vendor_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);            

            $project = $DB2->select('project_area, vendor_no, project_pic')->get_where('master_project', ['project_no' => $param['projectNo']])->result();
            if($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);
            if($jwt->vendor_no != $project[0]->vendor_no) $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses terhadap project!'], 200);
            // End Validation
            $pic        = $DB2->get_where('master_user', ['user_no' => $project[0]->project_pic])->row();
            $vendor     = $DB2->get_where('master_vendor', ['vendor_no' => $jwt->vendor_no])->row();
            $lastTrans  = $DB2->order_by('vendor_penagihan_pembayaran_date', 'DESC')->get_where('transaction_vendor_penagihan_pembayaran', ['project_no' => $param['projectNo']], 1)->result();

            $detail['kode_area']        = $project[0]->project_area;
            $detail['pic']              = $pic->user_name;
            $detail['konsultan']        = $vendor->vendor_contract_sign;
            $detail['lastProgActual']   = ($lastTrans != null ? $lastTrans[0]->vendor_penagihan_pembayaran_progactual : 0);

            $this->response([
                'status'        => true,
                'message'       => 'Data berhasil ditemukan', 
                'data'          => $detail
            ], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function penagihan_post(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->post();

            // Validation
            if(empty($param['projectNo']) || empty($param['noContract']) || empty($param['progActual']) || empty($param['workType']) || empty($param['paymentContract'])){
                $this->response(['status' => false, 'message' => 'Parameter tidak cocok!'], 200);
            }

            $vendor = $DB2->get_where('master_vendor', ['vendor_no' => $jwt->vendor_no])->row();
            if($jwt->vendor_session != $vendor->vendor_session || $vendor->vendor_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            
            $project = $DB2->get_where('master_project', ['project_no' => $param['projectNo']])->result();
            if($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);
            if($jwt->vendor_no != $project[0]->vendor_no) $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses terhadap project!'], 200);

            $lastTrans  = $DB2->order_by('vendor_penagihan_pembayaran_date', 'DESC')->get_where('transaction_vendor_penagihan_pembayaran', ['project_no' => $param['projectNo']], 1)->result();
            if($lastTrans != null){
                $progActual         = (float)$lastTrans[0]->vendor_penagihan_pembayaran_progactual;
                $progActualInput    = (float)$param['progActual'];

                if($progActualInput < $progActual){
                    $this->response(['status' => false, 'message' => 'Progress actual tidak valid!'], 200);
                }
            }
            // End Validation
            if($_FILES['docBAPPBAST']['name'] != null){
                $uploadFile = $this->uploadFile(explode('.', $_FILES['docBAPPBAST']['name'])[0], 'docBAPPBAST', 'penagihan/bapp-bast');
                if($uploadFile['status'] == false) $this->response(['status' => false, 'message' => $uploadFile['msg']], 200);
                $formData['vendor_penagihan_pembayaran_bapp_bast'] = $uploadFile['link'];
            }
            if($_FILES['docCop']['name'] != null){
                $uploadFile = $this->uploadFile(explode('.', $_FILES['docCop']['name'])[0], 'docCop', 'penagihan/cop');
                if($uploadFile['status'] == false) $this->response(['status' => false, 'message' => $uploadFile['msg']], 200);
                $formData['vendor_penagihan_pembayaran_cop_doc'] = $uploadFile['link'];
            }
            if($_FILES['docPO']['name'] != null){
                $uploadFile = $this->uploadFile(explode('.', $_FILES['docPO']['name'])[0], 'docPO', 'penagihan/po');
                if($uploadFile['status'] == false) $this->response(['status' => false, 'message' => $uploadFile['msg']], 200);
                $formData['vendor_penagihan_pembayaran_po'] = $uploadFile['link'];
            }
            if($_FILES['docFaktur']['name'] != null){
                $uploadFile = $this->uploadFile(explode('.', $_FILES['docFaktur']['name'])[0], 'docFaktur', 'penagihan/faktur');
                if($uploadFile['status'] == false) $this->response(['status' => false, 'message' => $uploadFile['msg']], 200);
                $formData['vendor_penagihan_pembayaran_faktur'] = $uploadFile['link'];
            }
            if($_FILES['docIssue']['name'] != null){
                $uploadFile = $this->uploadFile(explode('.', $_FILES['docIssue']['name'])[0], 'docIssue', 'penagihan/issue');
                if($uploadFile['status'] == false) $this->response(['status' => false, 'message' => $uploadFile['msg']], 200);
                $formData['vendor_penagihan_pembayaran_issue'] = $uploadFile['link'];
            }
            

            $formData['project_no']                                         = $param['projectNo'];
            $formData['vendor_penagihan_pembayaran_nama']                   = $param['vendorName'];
            $formData['vendor_penagihan_pembayaran_kontrak_no']             = $param['noContract'];
            $formData['vendor_penagihan_pembayaran_type']                   = $param['workType'];
            $formData['vendor_penagihan_pembayaran_kontrak_pembayaran']     = $param['paymentContract'];
            $formData['vendor_penagihan_pembayaran_progactual']             = $param['progActual'];
            $formData['vendor_penagihan_pembayaran_progtagih']              = $param['progTagih'];
            $formData['vendor_penagihan_pembayaran_ke']                     = $param['paymentTo'];
            $formData['vendor_penagihan_pembayaran_cop']                    = $param['copNo'];
            $formData['vendor_penagihan_pembayaran_cop_date']               = $param['copDate'];
            $formData['vendor_penagihan_pembayaran_pajak']                  = $param['tax'];
            $formData['vendor_penagihan_pembayaran_totamount']              = $param['totAmount'];
            $formData['vendor_penagihan_pembayaran_status']                 = 0;
            $formData['vendor_penagihan_pembayaran_date']                   = date('Y-m-d H:i:s');
            $DB2->insert('transaction_vendor_penagihan_pembayaran', $formData);

            $DB2->where('project_no', $param['projectNo'])->update('master_project', ['project_prog_penagihan' => $param['progActual']]);
            $this->response(['status' => true, 'message' => 'Data berhasil terupload'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }


    public function queryGetProject($vendorNo, $limit, $page){
        $DB2 = $this->load->database('gaSys2', true);
        $datas = $DB2->query("
            SELECT
                mp.project_no,
                mv.vendor_no ,
                mp.project_area ,
                mp.project_stat_vendor ,
                mp.project_name ,
                mp.project_contract_spk ,
                mp.project_progress ,
                mp.project_prog_weekly ,
                mp.project_tag_status
            FROM 
                master_project mp,
                master_vendor mv 
            WHERE 
                mp.vendor_no = '".$vendorNo."'
                AND mp.vendor_no = mv.vendor_no 
            LIMIT ".$limit."
            OFFSET ".(($page-1)*$limit)."
        ")->result();

        $allData = $DB2->query("
            SELECT
                mp.project_no,
                mv.vendor_no ,
                mp.project_area ,
                mp.project_stat_vendor ,
                mp.project_progress 
            FROM 
                master_project mp,
                master_vendor mv 
            WHERE 
                mp.vendor_no = '".$vendorNo."'
                AND mp.vendor_no = mv.vendor_no 
        ")->result();

        $pagination['limit']        = $limit;
        $pagination['page']         = $page;
        $pagination['total_page']   = ceil((count($allData) / $limit));
        $pagination['total_data']   = count($allData);
        return ['pagination' => $pagination, 'data' => $datas];
    }
    public function queryGetWeekly($vendorNo, $limit, $page){
        $DB2 = $this->load->database('gaSys2', true);
        $datas = $DB2->query("
            SELECT
                mp.project_no,
                mv.vendor_no ,
                mp.project_area ,
                mp.project_prog_weekly ,
                mp.project_stat_weekly ,
                mp.project_name ,
                mp.project_contract_spk ,
                mp.project_tag_status
            FROM 
                master_project mp,
                master_vendor mv 
            WHERE 
                mp.vendor_no = '".$vendorNo."'
                AND mp.vendor_no = mv.vendor_no 
            LIMIT ".$limit."
            OFFSET ".(($page-1)*$limit)."
        ")->result();

        $allData = $DB2->query("
            SELECT
                mp.project_no,
                mv.vendor_no ,
                mp.project_area ,
                mp.project_progress ,
                mp.project_prog_weekly ,
                mp.project_stat_weekly 
            FROM 
                master_project mp,
                master_vendor mv 
            WHERE 
                mp.vendor_no = '".$vendorNo."'
                AND mp.vendor_no = mv.vendor_no 
        ")->result();

        $pagination['limit']        = $limit;
        $pagination['page']         = $page;
        $pagination['total_page']   = ceil((count($allData) / $limit));
        $pagination['total_data']   = count($allData);
        return ['pagination' => $pagination, 'data' => $datas];
    }
    public function queryGetWeeklyDetail($projectNo, $limit, $page){
        $DB2 = $this->load->database('gaSys2', true);
        $datas = $DB2->query("
            SELECT 
                tvw.vendor_weekly_no ,
                tvw.vendor_weekly_week ,
                tvw.vendor_weekly_progactual ,
                tvw.vendor_weekly_status,
                tvw.vendor_weekly_remark,
                tvw.vendor_weekly_date,
                tvw.vendor_weekly_dateapproved,
                tvw.vendor_weekly_docreport
            FROM 
                transaction_vendor_weekly tvw ,
                master_project mp
            WHERE 
                tvw.project_no = '".$projectNo."'
                AND tvw.project_no = mp.project_no COLLATE utf8mb4_unicode_ci
            ORDER BY tvw.vendor_weekly_date DESC
            LIMIT ".$limit."
            OFFSET ".(($page-1)*$limit)."
        ")->result();

        $allData = $DB2->query("
            SELECT 
                tvw.vendor_weekly_no ,
                tvw.vendor_weekly_week ,
                tvw.vendor_weekly_progplan ,
                tvw.vendor_weekly_progactual ,
                tvw.vendor_weekly_status 
            FROM 
                transaction_vendor_weekly tvw ,
                master_project mp
            WHERE 
                tvw.project_no = '".$projectNo."'
                AND tvw.project_no = mp.project_no COLLATE utf8mb4_unicode_ci
            ORDER BY tvw.vendor_weekly_date DESC
        ")->result();

        $pagination['limit']        = $limit;
        $pagination['page']         = $page;
        $pagination['total_page']   = ceil((count($allData) / $limit));
        $pagination['total_data']   = count($allData);
        return ['pagination' => $pagination, 'data' => $datas];
    }
    public function queryGetBappBast($vendorNo, $limit, $page){
        $DB2 = $this->load->database('gaSys2', true);
        $datas = $DB2->query("
            SELECT
                mp.project_no,
                mv.vendor_no ,
                mp.project_area ,
                mp.project_prog_bapp_bast ,
                mp.project_stat_bapp_bast,
                mp.project_name,
                mp.project_contract_spk ,
                mp.project_tag_status
            FROM 
                master_project mp,
                master_vendor mv 
            WHERE 
                mp.vendor_no = '".$vendorNo."'
                AND mp.vendor_no = mv.vendor_no 
            LIMIT ".$limit."
            OFFSET ".(($page-1)*$limit)."
        ")->result();

        $allData = $DB2->query("
            SELECT
                mp.project_no,
                mv.vendor_no ,
                mp.project_area ,
                mp.project_progress ,
                mp.project_prog_bapp_bast ,
                mp.project_stat_bapp_bast 
            FROM 
                master_project mp,
                master_vendor mv 
            WHERE 
                mp.vendor_no = '".$vendorNo."'
                AND mp.vendor_no = mv.vendor_no 
        ")->result();

        $pagination['limit']        = $limit;
        $pagination['page']         = $page;
        $pagination['total_page']   = ceil((count($allData) / $limit));
        $pagination['total_data']   = count($allData);
        return ['pagination' => $pagination, 'data' => $datas];
    }
    public function queryGetBappBastDetail($projectNo, $limit, $page){
        $DB2 = $this->load->database('gaSys2', true);
        $datas = $DB2->query("
            SELECT 
                tvbb.vendor_bapp_bast_no ,
                tvbb.vendor_bapp_bast_docname ,
                tvbb.vendor_bapp_bast_progress ,
                tvbb.vendor_bapp_bast_remark  ,
                tvbb.vendor_bapp_bast_date ,
                tvbb.vendor_bapp_bast_status ,
                tvbb.vendor_bapp_bast_dateapproved,
                tvbb.vendor_bapp_bast_document
            FROM 
                transaction_vendor_bapp_bast tvbb ,
                master_project mp
            WHERE 
                tvbb.project_no = '".$projectNo."'
                AND tvbb.project_no = mp.project_no COLLATE utf8mb4_unicode_ci
            ORDER BY tvbb.vendor_bapp_bast_date DESC
            LIMIT ".$limit."
            OFFSET ".(($page-1)*$limit)."
        ")->result();

        $allData = $DB2->query("
            SELECT 
                tvbb.vendor_bapp_bast_no ,
                tvbb.vendor_bapp_bast_docname ,
                tvbb.vendor_bapp_bast_progress ,
                tvbb.vendor_bapp_bast_remark  ,
                tvbb.vendor_bapp_bast_date ,
                tvbb.vendor_bapp_bast_status
            FROM 
                transaction_vendor_bapp_bast tvbb ,
                master_project mp
            WHERE 
                tvbb.project_no = '".$projectNo."'
                AND tvbb.project_no = mp.project_no COLLATE utf8mb4_unicode_ci
            ORDER BY tvbb.vendor_bapp_bast_date DESC
        ")->result();

        $pagination['limit']        = $limit;
        $pagination['page']         = $page;
        $pagination['total_page']   = ceil((count($allData) / $limit));
        $pagination['total_data']   = count($allData);
        return ['pagination' => $pagination, 'data' => $datas];
    }
    public function queryGetPenagihan($vendorNo, $limit, $page){
        $DB2 = $this->load->database('gaSys2', true);
        $datas = $DB2->query("
            SELECT
                mp.project_no,
                mv.vendor_no ,
                mp.project_area ,
                mp.project_prog_penagihan ,
                mp.project_stat_penagihan ,
                mp.project_name,
                mp.project_contract_spk ,
                mp.project_tag_status
            FROM 
                master_project mp,
                master_vendor mv 
            WHERE 
                mp.vendor_no = '".$vendorNo."'
                AND mp.vendor_no = mv.vendor_no 
            LIMIT ".$limit."
            OFFSET ".(($page-1)*$limit)."
        ")->result();

        $allData = $DB2->query("
            SELECT
                mp.project_no,
                mv.vendor_no ,
                mp.project_area ,
                mp.project_progress ,
                mp.project_prog_penagihan ,
                mp.project_stat_penagihan 
            FROM 
                master_project mp,
                master_vendor mv 
            WHERE 
                mp.vendor_no = '".$vendorNo."'
                AND mp.vendor_no = mv.vendor_no 
        ")->result();

        $pagination['limit']        = $limit;
        $pagination['page']         = $page;
        $pagination['total_page']   = ceil((count($allData) / $limit));
        $pagination['total_data']   = count($allData);
        return ['pagination' => $pagination, 'data' => $datas];
    }
    public function queryGetPenagihanDetail($projectNo, $limit, $page){
        $DB2 = $this->load->database('gaSys2', true);
        $datas = $DB2->query("
            SELECT 
                tvpp.vendor_penagihan_pembayaran_no ,
                tvpp.vendor_penagihan_pembayaran_ke ,
                tvpp.vendor_penagihan_pembayaran_progactual ,
                tvpp.vendor_penagihan_pembayaran_progtagih  ,
                tvpp.vendor_penagihan_pembayaran_status ,
                tvpp.vendor_penagihan_pembayaran_date,
                tvpp.vendor_penagihan_pembayaran_dateapproved,
                tvpp.vendor_penagihan_pembayaran_remark,
                tvpp.vendor_penagihan_pembayaran_totamount,
                tvpp.vendor_penagihan_pembayaran_bapp_bast,
                tvpp.vendor_penagihan_pembayaran_cop_doc,
                tvpp.vendor_penagihan_pembayaran_po,
                tvpp.vendor_penagihan_pembayaran_faktur,
                tvpp.vendor_penagihan_pembayaran_issue
            FROM 
                transaction_vendor_penagihan_pembayaran tvpp ,
                master_project mp
            WHERE 
                tvpp.project_no = '".$projectNo."'
                AND tvpp.project_no = mp.project_no COLLATE utf8mb4_unicode_ci
            ORDER BY tvpp.vendor_penagihan_pembayaran_date  DESC
            LIMIT ".$limit."
            OFFSET ".(($page-1)*$limit)."
        ")->result();

        $allData = $DB2->query("
            SELECT 
                tvpp.vendor_penagihan_pembayaran_no ,
                tvpp.vendor_penagihan_pembayaran_ke ,
                tvpp.vendor_penagihan_pembayaran_progactual ,
                tvpp.vendor_penagihan_pembayaran_progtagih  ,
                tvpp.vendor_penagihan_pembayaran_status 
            FROM 
                transaction_vendor_penagihan_pembayaran tvpp ,
                master_project mp
            WHERE 
                tvpp.project_no = '".$projectNo."'
                AND tvpp.project_no = mp.project_no COLLATE utf8mb4_unicode_ci
            ORDER BY tvpp.vendor_penagihan_pembayaran_date  DESC
        ")->result();

        $pagination['limit']        = $limit;
        $pagination['page']         = $page;
        $pagination['total_page']   = ceil((count($allData) / $limit));
        $pagination['total_data']   = count($allData);
        return ['pagination' => $pagination, 'data' => $datas];
    }
    public function queryGetNews($limit, $page){
        $DB2 = $this->load->database('gaSys2', true);
        $datas = $DB2->query("
            SELECT * FROM master_news
            WHERE news_ispublish = '1'
            ORDER BY news_date DESC
            LIMIT ".$limit."
            OFFSET ".(($page-1)*$limit)."
        ")->result();

        $allData = $DB2->query("
            SELECT * FROM master_news
            WHERE news_ispublish = '1'
        ")->result();

        $pagination['limit']        = $limit;
        $pagination['page']         = $page;
        $pagination['total_page']   = ceil((count($allData) / $limit));
        $pagination['total_data']   = count($allData);
        return ['pagination' => $pagination, 'data' => $datas];
    }
    public function uploadReport($fileName){
        $path = 'uploads/project/weekly/report';
        $conf['upload_path']    = $path;
        $conf['allowed_types']  = "pdf";
        $conf['max_size']       = 2048;
        $conf['file_name']      = str_replace(' ', '_', $fileName);

        $this->upload->initialize($conf);
        if($this->upload->do_upload('docReport')){
            $file = $this->upload->data();
            return [
                    'status'=> true,
                    'msg'   => 'Data berhasil terupload',
                    'name'  => $fileName,
                    'link'  => site_url($path."/".$file['file_name'])
                ];
        }else{
            return [
                'status'=> false,
                'msg'   => $this->upload->display_errors(),
            ];
        }
    }
    public function uploadProgress($fileName){
        $path = 'uploads/project/weekly/progress';
        $conf['upload_path']    = $path;
        $conf['allowed_types']  = "pdf";
        $conf['max_size']       = 2048;
        $conf['file_name']      = str_replace(' ', '_', $fileName);

        $this->upload->initialize($conf);
        if($this->upload->do_upload('docProgress')){
            $file = $this->upload->data();
            return [
                    'status'=> true,
                    'msg'   => 'Data berhasil terupload',
                    'name'  => $fileName,
                    'link'  => site_url($path."/".$file['file_name'])
                ];
        }else{
            return [
                'status'=> false,
                'msg'   => $this->upload->display_errors(),
            ];
        }
    }
    public function uploadFile($fileName, $file, $path){
        $path = 'uploads/project/'.$path;
        $conf['upload_path']    = $path;
        $conf['allowed_types']  = "pdf|xls|xlsx";
        $conf['max_size']       = 2048;
        $conf['file_name']      = str_replace(' ', '_', $fileName);

        $this->upload->initialize($conf);
        if($this->upload->do_upload($file)){
            $file = $this->upload->data();
            return [
                    'status'=> true,
                    'msg'   => 'Data berhasil terupload',
                    'name'  => $fileName,
                    'link'  => site_url($path."/".$file['file_name'])
                ];
        }else{
            return [
                'status'=> false,
                'msg'   => $this->upload->display_errors(),
            ];
        }
    }public function queryGetTorList($projectNo){
        $DB2    = $this->load->database('gaSys2', true);
        return $DB2->query("
            SELECT  
                ttd.document_no ,
                mt.tor_no as tor_type,
                mt.tor_name as type_name,
                ttd.document_name ,
                ttd.document_link,
                ttd.submitted_date
            FROM transaction_tor_document ttd , master_tor mt 
            WHERE 
                project_no = '".$projectNo."'
                AND ttd.tor_no = mt.tor_no 
            ORDER BY mt.tor_name ASC
        ")->result();
    }

    public function getGeneralinfo($projectNo){
        $DB2    = $this->load->database('gaSys2', true);
        return $DB2->query("
            SELECT  
                project_area ,
                project_name,
                project_status,
                project_type ,
                project_category,
                project_totalvalue
            FROM master_project 
            WHERE 
                project_no = '".$projectNo."'
        ")->row_array();
    }

    public function getTermin($projectNo){
        $DB2    = $this->load->database('gaSys2', true);
        return $DB2->query("
            SELECT  
                contract_dp ,
                contract_progress,
                contract_retensi
            FROM transaction_contract 
            WHERE 
                project_no = '".$projectNo."'
        ")->row_array();
    }

    public function queryGetTenderList($projectNo){
        $DB2    = $this->load->database('gaSys2', true);
        return $DB2->query("
            SELECT  
                ttd.document_tender_no,
                mt.tender_document_no as tender_type,
                mt.tender_document_name as type_name,
                ttd.document_tender_name,
                ttd.document_tender_link,
                ttd.submitted_date 
            FROM transaction_tender_document ttd , master_tender_document mt 
            WHERE 
                project_no = '".$projectNo."'
                AND ttd.tender_document_no = mt.tender_document_no 
            ORDER BY mt.tender_document_no ASC
        ")->result();
    }

    public function queryGetWeeklyList($projectNo){
        $DB2    = $this->load->database('gaSys2', true);
        return $DB2->query("
            SELECT  
                ve.vendor_weekly_no,
                ve.vendor_weekly_week,
                ve.vendor_weekly_status,
                ve.vendor_weekly_progactual,
                ve.vendor_weekly_remark,
                ve.vendor_weekly_docreport,
                ve.vendor_weekly_docprogress,
                ve.vendor_weekly_date,
                ve.vendor_weekly_dateapproved
            FROM transaction_vendor_weekly ve 
            WHERE 
                project_no = '".$projectNo."'
                ORDER BY ve.vendor_weekly_week ASC
        ")->result();
    }

    public function queryGetBappBastList($projectNo){
        $DB2    = $this->load->database('gaSys2', true);
        return $DB2->query("
            SELECT  
                ve.vendor_bapp_bast_no,
                bb.bappast_type_name,
                ve.vendor_bapp_bast_docname,
                ve.vendor_bapp_bast_document,
                ve.vendor_bapp_bast_date
            FROM transaction_vendor_bapp_bast ve, master_bapp_bast_type bb
            WHERE 
                project_no = '".$projectNo."' AND
                ve.vendor_bapp_bast_type = bb.bappast_type_no
                ORDER BY ve.vendor_bapp_bast_date ASC
        ")->result();
    }

    public function queryGetPenagihanList($projectNo){
        $DB2    = $this->load->database('gaSys2', true);
        return $DB2->query("
            SELECT  
                ve.vendor_penagihan_pembayaran_no,
                ve.vendor_penagihan_pembayaran_ke,
                ve.vendor_penagihan_pembayaran_status,
                ve.vendor_penagihan_pembayaran_progtagih,
                ve.vendor_penagihan_pembayaran_remark,
                ve.vendor_penagihan_pembayaran_issue,
                ve.vendor_penagihan_pembayaran_cop_doc,
                ve.vendor_penagihan_pembayaran_po,
                ve.vendor_penagihan_pembayaran_faktur,
                ve.vendor_penagihan_pembayaran_date,
                ve.vendor_penagihan_pembayaran_dateapproved
            FROM transaction_vendor_penagihan_pembayaran ve
            WHERE 
                project_no = '".$projectNo."'
                ORDER BY ve.vendor_penagihan_pembayaran_date ASC
        ")->result();
    }
}
