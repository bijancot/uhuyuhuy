<?php defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Monitoring extends RestController {
    
    public function __construct() {
        parent::__construct();
        $this->load->helper("url");
        $this->load->library(array('upload', 'image_lib'));
    }

    public function index_get(){
        try{
            $DB2 = $this->load->database('gaSys2', true);
            $jwt = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));

            // Validation
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "PICP" && $user->user_role != "Admin PICP" && $user->user_role != "ADH" && $user->user_role != "SPV") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);
            // End Validation

            $limit  = $this->get('limit');
            $page   = $this->get('page');
            if(!empty($this->get('search')) || $this->get('search') != "") $DB2->or_like('project_no', $this->get('search'), 'both')->or_like('project_area', $this->get('search'), 'both')->or_like('project_name', $this->get('search'), 'both')->or_like('project_category', $this->get('search'), 'both')->or_like('user_initials', $this->get('search'), 'both');
            // $projects   = $DB2->select('project_no, project_name, project_area, user_initials')->order_by('project_stat_app ASC, proposed_date DESC')->get('v_monitoring', $limit, ($page-1) * $limit)->result();
            $projects   = $DB2->order_by('project_stat_app ASC, proposed_date DESC')->get('v_monitoring', $limit, ($page-1) * $limit)->result();
            if(!empty($this->get('search')) || $this->get('search') != "") $DB2->or_like('project_no', $this->get('search'), 'both')->or_like('project_area', $this->get('search'), 'both')->or_like('project_name', $this->get('search'), 'both')->or_like('project_category', $this->get('search'), 'both')->or_like('user_initials', $this->get('search'), 'both');
            // $allData   = $DB2->select('project_no, project_name, project_area, user_initials')->order_by('project_stat_app ASC, proposed_date DESC')->get('v_monitoring', $limit, ($page-1) * $limit)->result();
            $allData    = $DB2->order_by('project_stat_app ASC, proposed_date DESC')->get('v_monitoring')->result();

            $pagination['limit']        = $limit;
            $pagination['page']         = $page;
            $pagination['total_page']   = ceil((count($allData) / $limit));
            $pagination['total_data']   = count($allData);

            if($projects != null){
                $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'pagination' => $pagination, 'data' => $projects], 200);
            }else{
                $this->response(['status' => true, 'message' => 'Data tidak ditemukan'], 200);
            }
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    
    public function detail_get(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->get();

            // Validation
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "PICP" && $user->user_role != "Admin PICP" && $user->user_role != "ADH" && $user->user_role != "SPV") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            $project = $this->queryGeneralInfo($param['projectNo']);
            if($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);
            // End Validation

            $dnd_doc = $this->queryGetTorList($param['projectNo']);
            $tender_doc = $this->queryGetTenderList($param['projectNo']);
            $weekly = $this->queryGetWeeklyList($param['projectNo']);
            $bappbast = $this->queryGetBappBastList($param['projectNo']);
            $penagihan = $this->queryGetPenagihanList($param['projectNo']);
            $si = $this->queryGetSIList($param['projectNo']);

            $working = $this->queryGetWorking($param['projectNo']);
            $weeklyStat = $this->queryGetWeekly($param['projectNo']);

            $termin     = $this->getTermin($param['projectNo']);
            if($termin != null){
                $dataTermin['dp'] = array(
                    'percentage' => $termin['contract_dp'],
                    'amount' => round($termin['contract_dp']/100*$project->project_kontrak)
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
                            'amount_progress'=> round($percent[1]/100*$project->project_kontrak)
                        )
                    );
                }
                $dataTermin['retency'] = array(
                    'percentage' => $termin['contract_retensi'],
                    'amount' => round($termin['contract_retensi']/100*$project->project_kontrak)
                );
            }

            if($working != null){
                if($working->contract_end < date("Y-m-d")){
                    $project->status_pengerjaan = "Over";
                }else if($weeklyStat != null){
                    if($weeklyStat->vendor_weekly_statreport == 1){
                        $project->status_pengerjaan = "On Schedule";
                    }else if($weeklyStat->vendor_weekly_statreport == 2){
                        $project->status_pengerjaan = "Late";
                    }
                }else{
                    $project->status_pengerjaan = "On Schedule";
                }
            }else{
                $project->status_pengerjaan = "-";
            }  
            $project->si_amount = null;

            $this->response(['status' => true, 'message' => ['project' => $project, 'termin' => $dataTermin, 'weekly' => $weekly, 'bappbast' => $bappbast, 'penagihan' => $penagihan, 'si' => $si, 'dnd_doc' => $dnd_doc, 'tender_doc' => $tender_doc]], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function tag_put(){
        try{
            $DB2 = $this->load->database('gaSys2', true);
            $jwt = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param = $this->put();

            // Validation
            if(empty($param['projectNo']) || empty($param['tag'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "PICP" && $user->user_role != "Admin PICP" && $user->user_role != "ADH") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);
            
            $project = $DB2->get_where('master_project', ['project_no' => $param['projectNo']])->result();
            if($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);
            // End Validation

            $DB2->where('project_no', $param['projectNo'])->update('master_project', ['project_tag_status' => $param['tag']]);
            $this->response(['status' => true, 'message' => 'Berhasil mengubah status tag project!'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function edit_put(){
        try{
            $DB2 = $this->load->database('gaSys2', true);
            $jwt = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param = $this->put();

            // Validation
            if(empty($param['projectNo']) || empty($param['buildingType']) || empty($param['soilType']) || empty($param['vendorType'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "PICP" && $user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);
            
            $project = $DB2->get_where('master_project', ['project_no' => $param['projectNo']])->result();
            if($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);
            // End Validation

            $DB2->where('project_no', $param['projectNo'])->update('master_project', ['project_building_type' => $param['buildingType'], 'project_soil_type' => $param['soilType'], 'project_vendor_type' => $param['vendorType']]);
            $this->response(['status' => true, 'message' => 'Berhasil mengubah type building, soil, vendor!'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function edit_get(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->get();

            // Validation
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "PICP" && $user->user_role != "Admin PICP" && $user->user_role != "ADH") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            $project = $DB2->get_where('master_project', ['project_no' => $param['projectNo']])->row();
            if($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);
            // End Validation            

            $editData['project_no']             = $project->project_no;
            $editData['project_building_type']  = $project->project_building_type;
            $editData['project_soil_type']      = $project->project_soil_type;
            $editData['project_vendor_type']    = $project->project_vendor_type;
            $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $editData], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function approveWeekly_put(){
        try{
            $DB2 = $this->load->database('gaSys2', true);
            $jwt = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param = $this->put();

            // Validation
            if(empty($param['idWeekly'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "PICP" && $user->user_role != "Admin PICP" && $user->user_role != "ADH") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);
            // End Validation

            $report = $DB2->get_where('transaction_vendor_weekly', ['vendor_weekly_no' => $param['idWeekly']])->result();
            if($report == null) $this->response(['status' => false, 'message' => 'Weekly Report tidak ditemukan!'], 200);
            if($report[0]->vendor_weekly_status == 1) $this->response(['status' => false, 'message' => 'Weekly report sudah pernah disetujui!'], 200);

            $DB2->where('vendor_weekly_no', $param['idWeekly'])->update('transaction_vendor_weekly', ['vendor_weekly_status' => 1, 'vendor_weekly_remark' => NULL, 'vendor_weekly_dateapproved' => date('Y-m-d H:i:s')]);
            $DB2->where('project_no', $report[0]->project_no)->update('master_project', ['project_prog_weekly' => $report[0]->vendor_weekly_progactual]);
            $this->response(['status' => true, 'message' => 'Berhasil menyetujui weekly report!'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }   
    }

    public function holdWeekly_put(){
        try{
            $DB2 = $this->load->database('gaSys2', true);
            $jwt = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param = $this->put();

            // Validation
            if(empty($param['idWeekly'])  || empty($param['remark'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "PICP" && $user->user_role != "Admin PICP" && $user->user_role != "ADH") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);
            // End Validation

            $report = $DB2->get_where('transaction_vendor_weekly', ['vendor_weekly_no' => $param['idWeekly']])->result();
            if($report == null) $this->response(['status' => false, 'message' => 'Weekly Report tidak ditemukan!'], 200);
            if($report[0]->vendor_weekly_status == 1) $this->response(['status' => false, 'message' => 'Tidak dapat melakukan hold, weekly report sudah disetujui!'], 200);

            $DB2->where('vendor_weekly_no', $param['idWeekly'])->update('transaction_vendor_weekly', ['vendor_weekly_status' => 2, 'vendor_weekly_remark' => $param['remark']]);
            $this->response(['status' => true, 'message' => 'Berhasil melakukan hold weekly report!'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }   
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

    public function approveBappBast_put(){
        try{
            $DB2 = $this->load->database('gaSys2', true);
            $jwt = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param = $this->put();

            // Validation
            if(empty($param['idBappBast'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "PICP" && $user->user_role != "Admin PICP" && $user->user_role != "ADH") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);
            // End Validation

            $report = $DB2->get_where('transaction_vendor_bapp_bast', ['vendor_bapp_bast_no' => $param['idBappBast']])->result();
            if($report == null) $this->response(['status' => false, 'message' => 'BAPP/BAST tidak ditemukan!'], 200);
            if($report[0]->vendor_bapp_bast_status == 1) $this->response(['status' => false, 'message' => 'BAPP/BAST sudah pernah disetujui!'], 200);

            $DB2->where('vendor_bapp_bast_no', $param['idBappBast'])->update('transaction_vendor_bapp_bast', ['vendor_bapp_bast_status' => 1, 'vendor_bapp_bast_remark' => NULL, 'vendor_bapp_bast_dateapproved' => date('Y-m-d H:i:s')]);
            $this->response(['status' => true, 'message' => 'Berhasil menyetujui BAPP/BAST!'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }   
    }

    public function holdBappBast_put(){
        try{
            $DB2 = $this->load->database('gaSys2', true);
            $jwt = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param = $this->put();

            // Validation
            if(empty($param['idBappBast'])  || empty($param['remark'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "PICP" && $user->user_role != "Admin PICP" && $user->user_role != "ADH") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);
            // End Validation

            $report = $DB2->get_where('transaction_vendor_bapp_bast', ['vendor_bapp_bast_no' => $param['idBappBast']])->result();
            if($report == null) $this->response(['status' => false, 'message' => 'BAPP/BAST tidak ditemukan!'], 200);
            if($report[0]->vendor_bapp_bast_status == 1) $this->response(['status' => false, 'message' => 'Tidak dapat melakukan hold, BAPP/BAST sudah disetujui!'], 200);

            $DB2->where('vendor_bapp_bast_no', $param['idBappBast'])->update('transaction_vendor_bapp_bast', ['vendor_bapp_bast_status' => 2, 'vendor_bapp_bast_remark' => $param['remark']]);
            $this->response(['status' => true, 'message' => 'Berhasil melakukan hold BAPP/BAST!'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }   
    }

    public function approvePenagihan_put(){
        try{
            $DB2 = $this->load->database('gaSys2', true);
            $jwt = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param = $this->put();

            // Validation
            if(empty($param['idPenagihan'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "PICP" && $user->user_role != "Admin PICP" && $user->user_role != "ADH") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);
            // End Validation

            $report = $DB2->get_where('transaction_vendor_penagihan_pembayaran', ['vendor_penagihan_pembayaran_no' => $param['idPenagihan']])->result();
            if($report == null) $this->response(['status' => false, 'message' => 'Pembayaran tidak ditemukan!'], 200);
            if($report[0]->vendor_penagihan_pembayaran_status == 1) $this->response(['status' => false, 'message' => 'Pembayaran sudah pernah disetujui!'], 200);

            $DB2->where('vendor_penagihan_pembayaran_no', $param['idPenagihan'])->update('transaction_vendor_penagihan_pembayaran', 
                [
                    'vendor_penagihan_pembayaran_status' => 1, 
                    'vendor_penagihan_pembayaran_remark' => NULL, 
                    'vendor_penagihan_pembayaran_dateapproved' => date('Y-m-d H:i:s')
            ]);
            $this->response(['status' => true, 'message' => 'Berhasil menyetujui Pembayaran!'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }   
    }

    public function holdPenagihan_put(){
        try{
            $DB2 = $this->load->database('gaSys2', true);
            $jwt = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param = $this->put();

            // Validation
            if(empty($param['idPenagihan'])  || empty($param['remark'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "PICP" && $user->user_role != "Admin PICP" && $user->user_role != "ADH") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);
            // End Validation

            $report = $DB2->get_where('transaction_vendor_penagihan_pembayaran', ['vendor_penagihan_pembayaran_no' => $param['idPenagihan']])->result();
            if($report == null) $this->response(['status' => false, 'message' => 'Pembayaran tidak ditemukan!'], 200);
            if($report[0]->vendor_penagihan_pembayaran_status == 1) $this->response(['status' => false, 'message' => 'Tidak dapat melakukan hold, Pembayaran sudah disetujui!'], 200);

            $DB2->where('vendor_penagihan_pembayaran_no', $param['idPenagihan'])->update('transaction_vendor_penagihan_pembayaran', ['vendor_penagihan_pembayaran_status' => 2, 'vendor_penagihan_pembayaran_remark' => $param['remark']]);
            $this->response(['status' => true, 'message' => 'Berhasil melakukan hold Pembayaran!'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }   
    }

    public function queryGetTorList($projectNo){
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
                ve.vendor_bapp_bast_progress,
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

    public function queryGetSIList($projectNo){
        $DB2    = $this->load->database('gaSys2', true);
        return $DB2->query("
            SELECT 
                ts.si_no ,
                ts.si_link ,
                ts.si_item ,
                ts.si_vol ,
                ts.si_vo_name ,
                ts.si_vo_link ,
                ts.si_perihal ,
                ts.si_date 
            FROM transaction_si ts
            WHERE ts.project_no = '".$projectNo."'
            ORDER BY ts.si_date ASC
        ")->result();
    }

    public function queryGeneralInfo($projectNo){
        $DB2 = $this->load->database('gaSys2', true);
        return $DB2->query("
            SELECT 
                mp.*,
                ve.vendor_name    
            FROM 
                master_project mp
            LEFT JOIN master_vendor ve ON mp.vendor_no=ve.vendor_no
            WHERE 
                project_no = '".$projectNo."'
        ")->row();
    }

    public function queryGetWorking($projectNo){
        $DB2 = $this->load->database('gaSys2', true);
        return $DB2->query("
            SELECT 
                contract_end
            FROM 
                transaction_contract
            WHERE 
                project_no = '".$projectNo."'
        ")->row();
    }

    public function queryGetWeekly($projectNo){
        $DB2 = $this->load->database('gaSys2', true);
        return $DB2->query("
            SELECT 
                vendor_weekly_statreport,
                vendor_weekly_date
            FROM 
                transaction_vendor_weekly
            WHERE 
                project_no = '".$projectNo."'
            ORDER BY vendor_weekly_date DESC
            LIMIT 1
        ")->row();
    }
}
