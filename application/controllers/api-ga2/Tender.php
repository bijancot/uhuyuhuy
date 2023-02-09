<?php defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Tender extends RestController {
    
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
            if($user->user_role != "PICP" && $user->user_role != "Admin PICP" && $user->user_role != "ADH") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);
            // End Validation
            $pic = null;
            $search = null;
            if(!empty($param['pic'])) $pic = $param['pic'];
            if(!empty($param['search'])) $search = $param['search'];
            $dnds = $this->queryGetListTender($pic, $param['limit'], $param['page'], $search);

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

            $formContract           = $DB2->select('contract_no, contract_name_file, submitted_date')->get_where('transaction_contract', ['project_no' => $param['projectNo']])->row();
            $mandatoryDocs['docs']  = $this->queryGetDocTender($param['projectNo']);
            if($mandatoryDocs['docs']['fsv'] == null || $mandatoryDocs['docs']['gambar'] == null || $mandatoryDocs['docs']['bastl'] == null || $mandatoryDocs['docs']['sCurve'] == null){
                $mandatoryDocs['status']  = false;
            }else{
                $mandatoryDocs['status']  = true;
            }
            $optionalDocs = $this->queryGetDocOptional($param['projectNo']);

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

            $this->response(['status' => true, 'message' => ['project' => $project, 'form_contract' => $formContract, 'mandatory_docs' => $mandatoryDocs, 'optional_docs' => $optionalDocs]], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function uploadDocument_post(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->post();

            // Validation
            if(empty($param['projectNo']) || empty($param['totUpload']) || empty($param['currUpload']) || empty($param['docType']) || empty($_FILES['file'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "PICP" && $user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            $project = $DB2->get_where('master_project', ['project_no' => $param['projectNo']])->result();
            if($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);
            if($project[0]->project_stat_app != '1') $this->response(['status' => false, 'message' => 'Project belum disetujui!'], 200);

            if($project[0]->project_pic != $user->user_no && $user->user_role != "Admin PICP")  $this->response(['status' => false, 'message' => 'Project bukan bagian otoritas anda!'], 200);

            $docType = $DB2->get_where('master_tender_document', ['tender_document_no' => $param['docType']])->row();
            if(empty($docType->tender_document_no))  $this->response(['status' => false, 'message' => 'Document type tidak terdaftar!'], 200);
            // End Validation

            if($param['currUpload'] <= $param['totUpload']){
                $uploadDoc = $this->uploadDoc(explode('.', $_FILES['file']['name'])[0]);
                if($uploadDoc['status'] == false) $this->response(['status' => false, 'message' => $uploadDoc['msg']], 200);
            }else{
                $this->response(['status' => false, 'message' => 'Status upload telah melebihi total upload'], 200);
            }
            
            $formData['project_no']             = $param['projectNo'];
            $formData['tender_document_no']     = $param['docType'];
            $formData['document_tender_name']   = $uploadDoc['name'];
            $formData['document_tender_link']   = $uploadDoc['link'];
            $formData['submitted_date']         = date('Y-m-d H:i:s');
            $DB2->insert('transaction_tender_document', $formData);

            $this->response(['status' => true, 'message' => 'Data berhasil terupload'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function formContract_post(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->post();

            // Validation
            if(empty($param['projectNo']) || empty($param['vendorNo'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            if(empty($param['projContract']) || empty($param['dp']) || empty($param['progress']) || empty($param['retency']) || empty($param['start']) || empty($param['end']) || empty($param['maintenanceDuration'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "PICP" && $user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            $project = $DB2->get_where('master_project', ['project_no' => $param['projectNo']])->result();
            if($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);
            if($project[0]->project_stat_app != '1') $this->response(['status' => false, 'message' => 'Project belum disetujui!'], 200);

            $vendor = $DB2->get_where('master_vendor', ['vendor_no' => $param['vendorNo'], 'vendor_status' => '1'])->result();
            if($vendor == null) $this->response(['status' => false, 'message' => 'Vendor tidak terdaftar atau dalam status tidak aktif!'], 200);
            
            if($project[0]->project_pic != $user->user_no && $user->user_role != "Admin PICP")  $this->response(['status' => false, 'message' => 'Project bukan bagian otoritas anda!'], 200);
            // End Validation
            $tempProgress       = array();
            $percentBy          = (int)($param['dp'] == '' || $param['dp'] == NULL ? 0 : $param['dp']);
            $percentBy          += (int)($param['retency'] == '' || $param['retency'] == NULL ? 0 : $param['retency']);
            foreach ($param['progress'] as $item) {
                $percentBy          += (int)$item['by'];
                array_push($tempProgress, $item['percentage']."_".$item['by']);                
            }

            if($percentBy > 100) $this->response(['status' => false, 'message' => 'Total percentage pada total termin lebih dari 100%!'], 200);;
            if($percentBy <= 0) $this->response(['status' => false, 'message' => 'Total percentage pada total termin kurang dari 0%!'], 200);;

            $param['projectPIC'] = $project[0]->project_pic;

            $formData['project_no']                 = $param['projectNo'];
            $formData['vendor_no']                  = $param['vendorNo'];
            $formData['contract_name_file']         = 'FRC_'.str_replace('/', '', $param['projectNo']);
            $formData['contract_branch_spk']        = !empty($param['branchSPK']) ? $param['branchSPK'] : '-';
            $formData['contract_branch_name']       = !empty($param['branchName']) ? $param['branchName'] : '-';
            $formData['contract_branch_position']   = !empty($param['branchPosition']) ? $param['branchPosition'] : '-';
            $formData['contract_branch_proc']       = !empty($param['branchProc']) ? $param['branchProc'] : '-';
            $formData['contract_dp']                = $param['dp'];
            $formData['contract_progress']          = implode(';', $tempProgress);
            $formData['contract_retensi']           = $param['retency'];
            $formData['contract_start']             = $param['start'];
            $formData['contract_end']               = $param['end'];
            $formData['contract_duration']          = $param['maintenanceDuration'];
            $formData['submitted_date']             = date('Y-m-d H:i:s');
            $DB2->insert('transaction_contract', $formData);
            $lastId = $DB2->insert_id();
            
            $genFormContract    = $this->document->genFormContract($param['projectNo']);
            $genFormLPK         = $this->document->genPdf('LPK', $param);
            $DB2->where('contract_no', $lastId)->update('transaction_contract', ['contract_link' => $genFormContract['link'], 'lpk_link' => $genFormLPK['link']]);

            $DB2->where('project_no', $param['projectNo'])->update('master_project', ['project_kontrak' => $param['projContract'], 'project_contract_spk' => $formData['contract_branch_spk'], 'vendor_no' => $param['vendorNo']]);
            $this->response(['status' => true, 'message' => 'Data berhasil tersimpan'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function submit_put(){
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

            $formContract   = $DB2->get_where('transaction_contract', ['project_no' => $param['projectNo']])->result();
            $mandatoryDocs  = $this->queryGetDocTender($param['projectNo']);
            if($mandatoryDocs['fsv'] == null || $mandatoryDocs['gambar'] == null || $mandatoryDocs['bastl'] == null || $mandatoryDocs['sCurve'] == null){
                $mandatoryDocsStatus  = false;
            }else{
                $mandatoryDocsStatus  = true;
            }
            if($formContract == null || $mandatoryDocsStatus == false) $this->response(['status' => false, 'message' => 'Dokumen tender atau form request contract belum tersubmit!'], 200);
            // End Validation

            $DB2->where('project_no', $param['projectNo'])->update('master_project', ['project_stat_submit' => "1", "submited_date" => date('Y-m-d H:i:s'), 'project_nudge_tender' => '0']);
            $this->response(['status' => true, 'message' => 'Berhasil submit tender!'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function multiSubmit_put(){
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

                $formContract   = $DB2->get_where('transaction_contract', ['project_no' => $item])->result();
                $mandatoryDocs  = $this->queryGetDocTender($item);
                if($mandatoryDocs['fsv'] == null || $mandatoryDocs['gambar'] == null || $mandatoryDocs['bastl'] == null || $mandatoryDocs['sCurve'] == null){
                    $mandatoryDocsStatus  = false;
                }else{
                    $mandatoryDocsStatus  = true;
                }
                if($formContract == null || $mandatoryDocsStatus == false) {
                    $this->response(['status' => false, 'message' => 'Terdapat dokumen tender atau form request contract belum tersubmit!'], 200);
                    break;
                }

                array_push($projectTemp, $temp);
            }
            // End Validation
            $submitedDate = date('Y-m-d H:i:s');
            foreach ($projectTemp as $item) {
                $DB2->where('project_no', $item->project_no)->update('master_project', ['project_stat_submit' => "1", 'submited_date' => $submitedDate, 'project_nudge_tender' => '0']);
            }
            $this->response(['status' => true, 'message' => 'Berhasil multi submit project!'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function uploadDoc($fileName){
        $path = 'uploads/project/dokumenTender';
        $conf['upload_path']    = $path;
        $conf['allowed_types']  = "pdf";
        $conf['max_size']       = 2048;
        $conf['file_name']      = str_replace(' ', '_', $fileName);

        $this->upload->initialize($conf);
        if($this->upload->do_upload('file')){
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
    public function queryGetListTender($pic, $limit, $page, $searchFil){
        $DB2 = $this->load->database('gaSys2', true);
        $countMandatoryDoc = $DB2->select("COUNT(*) as total")->get_where('master_tender_document', ['tender_document_status' => '1', "tender_document_mandatory" => '1'])->row();
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
                mp.project_no,
                mp.project_name ,
                mp.project_area ,
                CONCAT(COUNT(ttd.document_tender_no), '/".$countMandatoryDoc->total."') as file_tender,
                IF(c.contract_no IS NULL, 0, 1) as form_contract,
                mu.user_initials ,
                mp.submited_date ,
                mp.project_nudge_tender
            FROM master_project mp 
                LEFT JOIN transaction_tender_document ttd 
                    ON mp.project_no = ttd.project_no COLLATE utf8mb4_unicode_ci
                LEFT JOIN transaction_contract c
                    ON mp.project_no = c.project_no COLLATE utf8mb4_unicode_ci
                LEFT JOIN master_user mu
                    ON mp.project_pic = mu.user_no 
            WHERE 
                ".$search."
                ".$pic."
                mp.project_stat_app  = '1'
            GROUP BY mp.project_no 
            ORDER BY mp.approved_date DESC
            LIMIT ".$limit."
            OFFSET ".(($page-1)*$limit)."
        ")->result();

        $allData = $DB2->query("
            SELECT
                mp.project_no,
                mp.project_name ,
                mp.project_area ,
                CONCAT(COUNT(ttd.document_tender_no), '/".$countMandatoryDoc->total."') as file_tender,
                IF(c.contract_no IS NULL, 0, 1) as form_contract,
                mu.user_initials ,
                mp.submited_date ,
                mp.project_nudge_tender
            FROM master_project mp 
                LEFT JOIN transaction_tender_document ttd 
                    ON mp.project_no = ttd.project_no COLLATE utf8mb4_unicode_ci
                LEFT JOIN transaction_contract c
                    ON mp.project_no = c.project_no COLLATE utf8mb4_unicode_ci
                LEFT JOIN master_user mu
                    ON mp.project_pic = mu.user_no 
            WHERE 
                ".$search."
                ".$pic."
                mp.project_stat_app  = '1'
            GROUP BY mp.project_no 
            ORDER BY mp.approved_date DESC
        ")->result();

        $pagination['limit']        = $limit;
        $pagination['page']         = $page;
        $pagination['total_page']   = ceil((count($allData) / $limit));
        $pagination['total_data']   = count($allData);
        return ['pagination' => $pagination, 'data' => $datas];
    }
    public function queryGetDocTender($projectNo){
        $DB2    = $this->load->database('gaSys2', true);
        $docs['fsv'] = $DB2->query("
            SELECT document_tender_no, document_tender_link
            FROM transaction_tender_document  
            WHERE project_no = '".$projectNo."' AND tender_document_no = '1'
        ")->row();
        $docs['gambar'] = $DB2->query("
            SELECT document_tender_no, document_tender_link
            FROM transaction_tender_document  
            WHERE project_no = '".$projectNo."' AND tender_document_no = '3'
        ")->row();
        $docs['bastl'] = $DB2->query("
            SELECT document_tender_no, document_tender_link
            FROM transaction_tender_document  
            WHERE project_no = '".$projectNo."' AND tender_document_no = '4'
        ")->row();
        $docs['sCurve'] = $DB2->query("
            SELECT document_tender_no, document_tender_link
            FROM transaction_tender_document  
            WHERE project_no = '".$projectNo."' AND tender_document_no = '2'
        ")->row();

        return $docs;
    }
    public function queryGetDocOptional($projectNo){
        $DB2    = $this->load->database('gaSys2', true);
        return $DB2->query("
            SELECT document_tender_no, document_tender_link
            FROM transaction_tender_document  
            WHERE 
                project_no = '".$projectNo."'
                AND tender_document_no NOT IN ('1', '10', '3', '4', '5')
        ")->result();
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
