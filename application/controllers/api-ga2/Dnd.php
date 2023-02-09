<?php defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Dnd extends RestController {
    
    public function __construct() {
        parent::__construct();
        $this->load->helper("url");
        $this->load->library(array('upload', 'image_lib'));
    }
    public function index_get(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->get();

            // Validation
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "PICP" && $user->user_role != "Admin PICP"  && $user->user_role != "ADH") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);
            // End Validation

            $pic    = null;
            $search = null;
            if(!empty($param['pic'])) $pic = $param['pic'];
            if(!empty($param['search'])) $search = $param['search'];
            $dnds = $this->queryGetListDnD($pic, $param['limit'], $param['page'], $search);

            if($dnds == null)  $this->response(['status' => false, 'message' => 'Data tidak ditemukan!'], 200);
            $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'pagination' => $dnds['pagination'], 'data' => $dnds['data']], 200);
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
            if($user->user_role != "PICP" && $user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            $project = $this->queryGeneralInfo($param['projectNo']);
            if($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);

            if($project->project_pic != $user->user_no && $user->user_role != "Admin PICP")  $this->response(['status' => false, 'message' => 'Project bukan bagian otoritas anda!'], 200);
            // End Validation

            $tors = $this->queryGetTorList($param['projectNo']);
            $formTender = $DB2->select('tender_no, tender_id, tender_name_file, tender_pic, submitted_date')->get_where('transaction_tender', ['project_no' => $param['projectNo']])->row();
            $working = $this->queryGetWorking($param['projectNo']);
            $weekly = $this->queryGetWeekly($param['projectNo']);
            if($working != null){
                if($working->contract_end < date("Y-m-d")){
                    $project->status_pengerjaan = "Over";
                }else if($weekly != null){
                    if($weekly->vendor_weekly_statreport == 1){
                        $project->status_pengerjaan = "On Schedule";
                    }else if($weekly->vendor_weekly_statreport == 2){
                        $project->status_pengerjaan = "Late";
                    }
                }else{
                    $project->status_pengerjaan = "On Schedule";
                }
            }else{
                $project->status_pengerjaan = "-";
            }  
            $project->si_amount = null;

            $this->response(['status' => true, 'message' => ['project' => $project, 'tors' => $tors, 'form_tender' => $formTender]], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function uploadTor_post(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->post();

            // Validation
            if(empty($param['projectNo']) || empty($param['totUpload']) || empty($param['currUpload']) || empty($param['torType']) || empty($_FILES['file'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "PICP" && $user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            $project = $DB2->get_where('master_project', ['project_no' => $param['projectNo']])->result();
            if($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);
            if($project[0]->project_stat != '1') $this->response(['status' => false, 'message' => 'Project belum diajukan!'], 200);

            if($project[0]->project_pic != $user->user_no && $user->user_role != "Admin PICP")  $this->response(['status' => false, 'message' => 'Project bukan bagian otoritas anda!'], 200);

            $torType = $DB2->get_where('master_tor', ['tor_no' => $param['torType']])->row();
            if(empty($torType->tor_no))  $this->response(['status' => false, 'message' => 'Tor type tidak terdaftar!'], 200);
            // End Validation

            if($param['currUpload'] <= $param['totUpload']){
                $uploadTor = $this->uploadTor(explode('.', $_FILES['file']['name'])[0], $torType, $param['projectNo']);
                if($uploadTor['status'] == false) $this->response(['status' => false, 'message' => $uploadTor['msg']], 200);
            }else{
                $this->response(['status' => false, 'message' => 'Status upload telah melebihi total upload'], 200);
            }
            
            $formData['project_no']         = $param['projectNo'];
            $formData['tor_no']             = $param['torType'];
            $formData['document_name']      = $uploadTor['name'];
            $formData['document_link']      = $uploadTor['link'];
            $formData['submitted_date']     = date('Y-m-d H:i:s');
            $DB2->insert('transaction_tor_document', $formData);

            $this->response(['status' => true, 'message' => 'Data berhasil terupload'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function formTender_post(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->post();

            // Validation
            if(empty($param['projectNo']) || empty($param['tenderId']) || empty($param['name']) || empty($param['location']) || empty($param['tenderAreaCode'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            if(empty($param['tenderTypeNo']) || empty($param['budget']) || empty($param['divisionNo']) || empty($param['departmentNo']) || empty($param['pic'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            if(empty($param['start']) || empty($param['end']) || empty($param['lead']) || empty($param['divParticipant']) || empty($param['telp'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            if(empty($param['picEmail']) || empty($param['approveEmail']) || empty($param['description'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "PICP" && $user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            $project = $DB2->get_where('master_project', ['project_no' => $param['projectNo']])->result();
            if($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);
            if($project[0]->project_stat != '1') $this->response(['status' => false, 'message' => 'Project belum terpropose!'], 200);

            $tenderArea = $DB2->get_where('master_tender_area', ['tender_area_code' => $param['tenderAreaCode'], 'tender_area_status' => '1'])->result();
            if($tenderArea == null) $this->response(['status' => false, 'message' => 'Area tender tidak terdaftar atau dalam status tidak aktif!'], 200);

            $tenderType = $DB2->get_where('master_tender_type', ['tender_type_no' => $param['tenderTypeNo'], 'tender_type_status' => '1'])->result();
            if($tenderType == null) $this->response(['status' => false, 'message' => 'Tipe tender tidak terdaftar atau dalam status tidak aktif!'], 200);
            
            $division = $DB2->get_where('master_division', ['division_no' => $param['divisionNo'], 'division_status' => '1'])->result();
            if($division == null) $this->response(['status' => false, 'message' => 'Divisi tidak terdaftar atau dalam status tidak aktif!'], 200);

            $department = $DB2->get_where('master_department', ['department_no' => $param['departmentNo'], 'department_status' => '1'])->result();
            if($department == null) $this->response(['status' => false, 'message' => 'Department tidak terdaftar atau dalam status tidak aktif!'], 200);
            
            if($project[0]->project_pic != $user->user_no && $user->user_role != "Admin PICP")  $this->response(['status' => false, 'message' => 'Project bukan bagian otoritas anda!'], 200);
            // End Validation

            $formData['project_no']                     = $param['projectNo'];
            $formData['tender_id']                      = $param['tenderId'];
            $formData['tender_name']                    = $param['name'];
            $formData['tender_name_file']               = 'FRT_'.str_replace('/', '', $param['projectNo']);
            $formData['tender_location']                = $param['location'];
            $formData['tender_area_code']               = $param['tenderAreaCode'];
            $formData['tender_type_no']                 = $param['tenderTypeNo'];
            $formData['tender_budget']                  = $param['budget'];
            $formData['division_no']                    = $param['divisionNo'];
            $formData['department_no']                  = $param['departmentNo'];
            $formData['tender_pic']                     = $param['pic'];
            $formData['tender_start']                   = $param['start'];
            $formData['tender_end']                     = $param['end'];
            $formData['tender_lead']                    = $param['lead'];
            $formData['tender_division_participant']    = $param['divParticipant'];
            $formData['tender_telp']                    = $param['telp'];
            $formData['tender_pic_email']               = $param['picEmail'];
            $formData['tender_approve_email']           = $param['approveEmail'];
            $formData['tender_description']             = $param['description'];
            $formData['submitted_date']                 = date('Y-m-d H:i:s');
            $DB2->insert('transaction_tender', $formData);

            $this->response(['status' => true, 'message' => 'Data berhasil tersimpan'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    
    public function approve_put(){
        try{
            $DB2 = $this->load->database('gaSys2', true);
            $jwt = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param = $this->put();

            // Validation
            if(empty($param['projectNo'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            $project = $DB2->get_where('master_project', ['project_no' => $param['projectNo']])->result();
            if($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);

            $formTender = $DB2->get_where('transaction_tender', ['project_no' => $param['projectNo']])->result();
            $tor        = $DB2->get_where('transaction_tor_document', ['project_no' => $param['projectNo']])->result();
            if($formTender == null || $tor == null) $this->response(['status' => false, 'message' => 'Dokumen tor atau form request tender belum tersubmit!'], 200);
            // End Validation

            $DB2->where('project_no', $param['projectNo'])->update('master_project', ['project_stat_app' => "1", "approved_date" => date('Y-m-d H:i:s'), 'project_nudge_dnd' => '0']);
            $this->response(['status' => true, 'message' => 'Berhasil approve project!'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function multiApprove_put(){
        try{
            $DB2 = $this->load->database('gaSys2', true);
            $jwt = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param = $this->put();

            // Validation
            if(empty($param['projectNo'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            $projectTemp = array();
            foreach ($param['projectNo'] as $item) {
                $temp       = $DB2->get_where('master_project', ['project_no' => $item])->row();
                if(empty($temp->project_no)){
                    $this->response(['status' => false, 'message' => 'Terdapat project yang tidak terdaftar!'], 200);
                    break;
                }

                $formTender = $DB2->get_where('transaction_tender', ['project_no' => $item])->result();
                $tor        = $DB2->get_where('transaction_tor_document', ['project_no' => $item])->result();
                if($formTender == null || $tor == null){
                    $this->response(['status' => false, 'message' => 'Terdapat project yang belum mensubmit dokumen tor atau form request tender!'], 200);
                    break;
                }

                array_push($projectTemp, $temp);
            }
            // End Validation
            $approvedDate = date('Y-m-d H:i:s');
            foreach ($projectTemp as $item) {
                $DB2->where('project_no', $item->project_no)->update('master_project', ['project_stat_app' => "1", 'approved_date' => $approvedDate, 'project_nudge_dnd' => '0']);
            }
            $this->response(['status' => true, 'message' => 'Berhasil multi approve project!'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function cancel_post(){
        try{
            $DB2 = $this->load->database('gaSys2', true);
            $jwt = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param = $this->post();

            // Validation
            if(empty($param['projectNo'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            $project = $DB2->get_where('master_project', ['project_no' => $param['projectNo']])->result();
            if($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);
            // End Validation
            
            if(!empty($_FILES['evidence'])){
                $uploadEvi = $this->uploadEvidence(explode('.', $_FILES['evidence']['name'])[0]);
                if($uploadEvi['status'] == false) $this->response(['status' => false, 'message' => $uploadEvi['msg']], 200);;
                $formData['cancel_evidence'] = $uploadEvi['link'];
            }else{
                $formData['cancel_evidence'] = NULL;
            }

            $formData['project_no']     = $param['projectNo'];
            $formData['confirmed_date']  = date('Y-m-d H:i:s');
            !empty($param['remark']) || $param['remark'] != '' ? $formData['cancel_remark'] = $param['remark'] : $formData['cancel_remark'] = NULL;
            

            $DB2->insert('transaction_cancel_project', $formData);
            $DB2->where('project_no', $param['projectNo'])->update('master_project', ['project_stat_app' => "2"]);
            $this->response(['status' => true, 'message' => 'Berhasil cancel project!'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function uploadEvidence($fileName){
        $path = 'uploads/project/cancel';
        $conf['upload_path']    = $path;
        $conf['allowed_types']  = "jpg|jpeg|png";
        $conf['max_size']       = 2048;
        $conf['file_name']      = str_replace(' ', '_', $fileName);

        $this->upload->initialize($conf);
        if($this->upload->do_upload('evidence')){
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
    public function uploadTor($fileName, $torType, $projectNo){
        $nameFile = $torType->tor_name." - ".str_replace('/', '', $projectNo);
        $path = 'uploads/project/tor';
        $conf['upload_path']    = $path;
        $conf['allowed_types']  = "pdf";
        $conf['max_size']       = 2048;
        $conf['file_name']      = $nameFile;

        $this->upload->initialize($conf);
        if($this->upload->do_upload('file')){
            $file = $this->upload->data();
            return [
                    'status'=> true,
                    'msg'   => 'Data berhasil terupload',
                    'name'  => $nameFile,
                    'link'  => site_url($path."/".$file['file_name'])
                ];
        }else{
            return [
                'status'=> false,
                'msg'   => $this->upload->display_errors(),
            ];
        }
    }
    public function queryGetTorList($projectNo){
        $DB2    = $this->load->database('gaSys2', true);
        return $DB2->query("
            SELECT  
                ttd.document_no ,
                mt.tor_no as tor_type,
                mt.tor_name ,
                ttd.document_name ,
                ttd.document_link 
            FROM transaction_tor_document ttd , master_tor mt 
            WHERE 
                project_no = '".$projectNo."'
                AND ttd.tor_no = mt.tor_no 
            ORDER BY mt.tor_name ASC
        ")->result();
    }
    public function queryGetListDnD($pic, $limit, $page, $searchFil){
        $DB2 = $this->load->database('gaSys2', true);
        $search = "";
        if($pic != null) $pic = "mp.project_pic = '".$pic."' AND " ;
        if($searchFil != null || $searchFil != ""){
            $search = '
                (mp.project_no LIKE "%'.$searchFil.'%"
                OR mp.project_name LIKE "%'.$searchFil.'%"
                OR mp.project_area LIKE "%'.$searchFil.'%"
                OR mu.user_initials LIKE "%'.$searchFil.'%") AND
            ';
        }

        $datas = $DB2->query("
            SELECT
                mp.project_no ,
                mp.project_name ,
                mp.project_area ,
                IF(ttd.document_no IS NULL, 0, 1) as file_tor,
                IF(td.tender_no IS NULL, 0, 1) as form_tender,
                mu.user_initials,
                mp.approved_date ,
                mp.project_stat_app ,
                mp.project_nudge_dnd
            FROM master_project mp 
                LEFT JOIN transaction_tor_document ttd 
                    ON mp.project_no = ttd.project_no 
                LEFT JOIN transaction_tender td
                    ON mp.project_no = td.project_no
                LEFT JOIN master_user mu
                    ON mp.project_pic = mu.user_no
            WHERE 
                ".$search."
                ".$pic."
                mp.project_stat  = '1'
            GROUP BY mp.project_no 
            ORDER BY mp.proposed_date DESC
            LIMIT ".$limit."
            OFFSET ".(($page-1)*$limit)."
        ")->result();

        $allData = $DB2->query("
            SELECT
                mp.project_no ,
                mp.project_name ,
                mp.project_area ,
                IF(ttd.document_no IS NULL, 0, 1) as file_tor,
                IF(td.tender_no IS NULL, 0, 1) as form_tender,
                mu.user_initials,
                mp.approved_date ,
                mp.project_stat_app,
                mp.project_nudge_dnd
            FROM master_project mp 
                LEFT JOIN transaction_tor_document ttd 
                    ON mp.project_no = ttd.project_no 
                LEFT JOIN transaction_tender td
                    ON mp.project_no = td.project_no
                LEFT JOIN master_user mu
                    ON mp.project_pic = mu.user_no
            WHERE 
                ".$search."
                ".$pic."
                mp.project_stat  = '1'
            GROUP BY mp.project_no 
            ORDER BY mp.proposed_date DESC
        ")->result();

        $pagination['limit']        = $limit;
        $pagination['page']         = $page;
        $pagination['total_page']   = ceil((count($allData) / $limit));
        $pagination['total_data']   = count($allData);
        return ['pagination' => $pagination, 'data' => $datas];
    }

    public function queryGeneralInfo($projectNo){
        $DB2 = $this->load->database('gaSys2', true);
        return $DB2->query("
            SELECT 
                mp.project_no,
                mp.project_pic,
                mp.project_area,
                mp.project_name,
                mp.project_status,
                mp.project_type,
                mp.project_category,                
                mp.project_building_type,          
                mp.project_soil_type,
                ve.vendor_name,         
                mp.project_vendor_type,
                mp.project_totalvalue
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
