<?php defined('BASEPATH') or exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Project extends RestController
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper("url");
        $this->load->library(array('upload', 'image_lib'));
    }

    public function index_get()
    {
        try {
            $DB2        = $this->load->database('gaSys2', true);
            $jwt        = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $pagination = null;

            // Validation
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if ($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if ($user->user_role != "PICP" && $user->user_role != "Admin PICP" && $user->user_role != "ADH") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);
            // End Validation

            if (!empty($this->get('pic'))) $DB2->where('project_pic', $this->get('pic'));
            if (!empty($this->get('status'))) $DB2->where('project_stat', $this->get('status'));
            if (!empty($this->get('search')) || $this->get('search') != "") $DB2->or_like('project_no', $this->get('search'), 'both')->or_like('project_area', $this->get('search'), 'both')->or_like('project_name', $this->get('search'), 'both')->or_like('project_category', $this->get('search'), 'both')->or_like('user_initials', $this->get('search'), 'both');
            if (!empty($this->get('limit')) && !empty($this->get('page'))) {
                $limit  = $this->get('limit');
                $page   = $this->get('page');

                $projects   = $DB2->order_by('project_stat ASC, proposed_date DESC')->get('v_project', $limit, ($page - 1) * $limit)->result();

                if (!empty($this->get('search')) || $this->get('search') != "") $DB2->or_like('project_no', $this->get('search'), 'both')->or_like('project_area', $this->get('search'), 'both')->or_like('project_name', $this->get('search'), 'both')->or_like('project_category', $this->get('search'), 'both')->or_like('user_initials', $this->get('search'), 'both');
                $allData    = $DB2->order_by('project_stat ASC, proposed_date DESC')->get('v_project')->result();

                $pagination['limit']        = $limit;
                $pagination['page']         = $page;
                $pagination['total_page']   = ceil((count($allData) / $limit));
                $pagination['total_data']   = count($allData);
            } else {
                $projects   = $DB2->order_by('project_stat ASC, proposed_date DESC')->get('v_project')->result();
            }

            if ($projects != null) {
                $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'pagination' => $pagination, 'data' => $projects], 200);
            } else {
                $this->response(['status' => true, 'message' => 'Data tidak ditemukan'], 200);
            }
        } catch (Exception $exp) {
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function edit_put()
    {
        try {
            $DB2 = $this->load->database('gaSys2', true);
            $jwt = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param = $this->put();

            if (empty($param['projectNo']) || empty($param['area']) || empty($param['projName']) || empty($param['projStatus']) || empty($param['projType']) || empty($param['projCategory']) || empty($param['projTotal'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok'], 200);
            $project = $DB2->get_where('master_project', ['project_no' => $param['projectNo']])->result();
            if ($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);

            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if ($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if ($user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            $area = $DB2->get_where('master_area', ['area_code' => $param['area'], 'area_status' => '1'])->result();
            if ($area == null) $this->response(['status' => false, 'message' => 'Area tidak terdaftar atau dalam status tidak aktif!'], 200);

            $projStatus = $DB2->get_where('master_project_status', ['status_name' => $param['projStatus'], 'status_status' => '1'])->result();
            if ($projStatus == null) $this->response(['status' => false, 'message' => 'Project status tidak terdaftar atau dalam status tidak aktif!'], 200);

            $projType = $DB2->get_where('master_project_type', ['type_name' => $param['projType'], 'type_status' => '1'])->result();
            if ($projType == null) $this->response(['status' => false, 'message' => 'Project type tidak terdaftar atau dalam status tidak aktif!'], 200);

            $projCategory = $DB2->get_where('master_project_category', ['category_name' => $param['projCategory'], 'category_status' => '1'])->result();
            if ($projCategory == null) $this->response(['status' => false, 'message' => 'Project category tidak terdaftar atau dalam status tidak aktif!'], 200);

            if (!is_numeric($param['projTotal'])) $this->response(['status' => false, 'message' => 'Project total harus bertipe angka!'], 200);

            $lastProjectNo = $DB2->order_by('project_no', 'DESC')->like('project_no', 'MB')->limit(1)->get('master_project')->result();
            $projectNo = 1;
            if ($lastProjectNo != null) {
                $projectYear    = explode('/', $lastProjectNo[0]->project_no)[0];
                $projectNo      = (int) explode('/', $lastProjectNo[0]->project_no)[2] + 1;
                if (((int)date('Y') + 1) != $projectYear) $projectNo = 1;
            }

            $formData['project_area']           = $param['area'];
            $formData['project_name']           = $param['projName'];
            $formData['project_status']         = $param['projStatus'];
            $formData['project_type']           = $param['projType'];
            $formData['project_category']       = $param['projCategory'];
            $formData['project_totalvalue']     = $param['projTotal'];
            if (!empty($param['typeMS']) != null) {
                $formData['project_ms_type'] = $param['typeMS'];
            } else {
                $formData['project_ms_type'] = null;
            }
            if (!empty($param['projMS']) != null) {
                $formData['project_ms'] = $param['projMS'];
            } else {
                $formData['project_ms'] = null;
            }


            $DB2->where('project_no', $param['projectNo'])->update('master_project', $formData);

            $this->response(['status' => true, 'message' => 'Data berhasil diubah!'], 200);
        } catch (Exception $exp) {
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function detail_get()
    {
        try {
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->get();

            // Validation
            if (empty($param['projectNo'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok'], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if ($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if ($user->user_role != "PICP" && $user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);
            // End Validation

            // $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $param['projectNo']], 200);
            $project = $DB2->get_where('master_project', ['project_no' => $param['projectNo']])->row();
            if (!empty($project->project_no)) {
                $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $project], 200);
            } else {
                $this->response(['status' => true, 'message' => 'Data tidak ditemukan'], 200);
            }
        } catch (Exception $exp) {
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function projectTermin_get()
    {
        try {
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->get();

            // Validation
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if ($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if ($user->user_role != "PICP" && $user->user_role != "Admin PICP" && $user->user_role != "ADH") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            $project = $this->queryGeneralInfo($param['projectNo']);
            if ($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);
            $termin     = $this->getTermin($param['projectNo']);
            if ($termin != null) {
                $active = true;
                
                $status = false;
                $transDP = $DB2->get_where('transaction_cop', ['project_no' => $param['projectNo'], 'cop_type' => 'COP DP', 'cop_stat_approve' => '1'])->result();
                if($transDP == null){
                    if($active == true){
                        $status = true;
                        $active = false;
                    }
                }

                $dataTermin['dp'] = array(
                    'percentage' => $termin['contract_dp'],
                    'amount' => round($termin['contract_dp'] / 100 * $project->project_kontrak),
                    'status' => $status
                );
                $value = explode(";", $termin['contract_progress']);
                $transProgress = $DB2->get_where('transaction_cop', ['project_no' => $param['projectNo'], 'cop_type' => 'COP PROGRESS', 'cop_stat_approve' => '1'])->result();
                $dataTermin['progress'] = array();
                $index = 0;
                foreach ($value as $item) {
                    $percent = explode("_", $item);
                    $status = false;
                    if(empty($transProgress[$index++])){
                        if($active == true){
                            $status = true;
                            $active = false;
                        }
                    }

                    array_push(
                        $dataTermin['progress'],
                        array(
                            'progress' => $percent[1],
                            'percentage' => $percent[0],
                            'amount_progress' => round($percent[1] / 100 * $project->project_kontrak),
                            'status' => $status
                        )
                    );
                }

                $transRetency = $DB2->get_where('transaction_cop', ['project_no' => $param['projectNo'], 'cop_type' => 'COP RETENCY', 'cop_stat_approve' => '1'])->result();
                $status = false; 
                if($transRetency == null ){
                    if($active == true){
                        $status = true;
                        $active = false;
                    }
                }
                $dataTermin['retency'] = array(
                    'percentage' => $termin['contract_retensi'],
                    'amount' => round($termin['contract_retensi'] / 100 * $project->project_kontrak),
                    'status' => $status
                );
            }
            $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $dataTermin], 200);
        } catch (Exception $exp) {
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }

    public function listPIC_get()
    {
        try {
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->get();
            // Validation
            if (empty($param['projectNo'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok'], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if ($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if ($user->user_role != "PICP" && $user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            $project = $DB2->get_where('master_project', ['project_no' => $param['projectNo']])->row();
            if (empty($project->project_no)) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);
            // End Validation

            $PICs = $DB2->select(['user_no', 'user_name', 'user_area', 'user_role'])->get_where('master_user', ['user_role' => 'PICP', 'user_area' => $project->project_area])->result();

            if ($PICs != null) {
                $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $PICs], 200);
            } else {
                $PICADMs = $DB2->select(['user_no', 'user_name', 'user_area', 'user_role'])->get_where('master_user', ['user_role' => 'PICP', 'user_area' => "ADM"])->result();
                if ($PICADMs != null) {
                    $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $PICADMs], 200);
                } else {
                    $this->response(['status' => true, 'message' => 'Data tidak ditemukan'], 200);
                }
            }
        } catch (Exception $exp) {
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }

    public function setStatus_put()
    {
        try {
            $DB2 = $this->load->database('gaSys2', true);
            $jwt = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param = $this->put();

            // Validation
            if (empty($param['projectNo']) || empty($param['status'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok'], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if ($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if ($user->user_role != "PICP" && $user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            $project = $DB2->get_where('master_project', ['project_no' => $param['projectNo']])->result();
            if ($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);

            if ($param['status'] != '0' && $param['status'] != '1' && $param['status'] && '2') $this->response(['status' => false, 'message' => 'Status tidak valid!'], 200);
            // End Validation

            $DB2->where('project_no', $param['projectNo'])->update('master_project', ['project_stat' => $param['status']]);
            $this->response(['status' => true, 'message' => 'Berhasil mengubah status project!'], 200);
        } catch (Exception $exp) {
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }

    public function setPIC_put()
    {
        try {
            $DB2 = $this->load->database('gaSys2', true);
            $jwt = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param = $this->put();

            // Validation
            if (empty($param['projectNo']) || empty($param['username'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok'], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if ($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if ($user->user_role != "PICP" && $user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            $project = $DB2->get_where('master_project', ['project_no' => $param['projectNo']])->result();
            if ($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);

            $pic = $DB2->get_where('master_user', ['user_no' => $param['username'], 'user_role' => 'PICP'])->result();
            if ($pic == null) $this->response(['status' => false, 'message' => 'User tidak terdaftar!'], 200);
            // End Validation

            $DB2->where('project_no', $param['projectNo'])->update('master_project', ['project_pic' => $param['username']]);
            $this->response(['status' => true, 'message' => 'Berhasil mengubah PIC project!'], 200);
        } catch (Exception $exp) {
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }

    public function propose_put()
    {
        try {
            $DB2 = $this->load->database('gaSys2', true);
            $jwt = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param = $this->put();

            // Validation
            if (empty($param['projectNo'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok'], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if ($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if ($user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            $project = $DB2->get_where('master_project', ['project_no' => $param['projectNo']])->result();
            if ($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);
            if ($project[0]->project_pic == null || $project[0]->project_pic == '') $this->response(['status' => false, 'message' => 'Project PIC belum terpilih!'], 200);

            // End Validation
            $date = date('Y-m-d H:i:s');
            $DB2->where('project_no', $param['projectNo'])->update('master_project', ['project_stat' => "1", "proposed_date" => $date]);
            // if(explode('/', $param['projectNo'])[1] == 'MB'){
            //     $DB2->where('project_no', $param['projectNo'])->update('master_project', ['project_stat' => "1", "proposed_date" => $date]);
            // }else{
            //     $DB2->where('project_no', $param['projectNo'])->update('master_project', 
            //     [
            //         'project_stat' => "1", "proposed_date" => $date,
            //         'project_stat_app' => "1", "approved_date" => $date,
            //         'project_stat_submit' => "1", "submited_date" => $date
            //     ]);
            // }
            $this->response(['status' => true, 'message' => 'Berhasil propose project!'], 200);
        } catch (Exception $exp) {
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }

    public function cancel_put()
    {
        try {
            $DB2 = $this->load->database('gaSys2', true);
            $jwt = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param = $this->put();

            // Validation
            if (empty($param['projectNo'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok'], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if ($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if ($user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            $project = $DB2->get_where('master_project', ['project_no' => $param['projectNo']])->result();
            if ($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);
            // End Validation

            $DB2->where('project_no', $param['projectNo'])->update('master_project', ['project_stat' => "2"]);
            $this->response(['status' => true, 'message' => 'Berhasil cancel project!'], 200);
        } catch (Exception $exp) {
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }

    public function multiPropose_put()
    {
        try {
            $DB2 = $this->load->database('gaSys2', true);
            $jwt = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param = $this->put();

            // Validation
            if (empty($param['projectNo'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok'], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if ($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if ($user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            $projectTemp = array();
            foreach ($param['projectNo'] as $item) {
                $temp = $DB2->get_where('master_project', ['project_no' => $item])->row();
                if (empty($temp->project_no)) {
                    $this->response(['status' => false, 'message' => 'Terdapat project yang tidak terdaftar!'], 200);
                    break;
                } else if ($temp->project_pic == NULL || $temp->project_pic == '') {
                    $this->response(['status' => false, 'message' => 'Terdapat project yang belum memiliki PIC!'], 200);
                    break;
                }
                array_push($projectTemp, $temp);
            }
            // End Validation
            $proposeDate = date('Y-m-d H:i:s');
            $DB2->where('project_no', $item->project_no)->update('master_project', ['project_stat' => "1", "proposed_date" => $proposeDate]);
            foreach ($projectTemp as $item) {
                $DB2->where('project_no', $item->project_no)->update('master_project', ['project_stat' => "1", "proposed_date" => $proposeDate]);
                // if(explode('/', $item->project_no)[1] == 'MB'){
                // }else{
                //     $DB2->where('project_no', $item->project_no)->update('master_project', 
                //     [
                //         'project_stat' => "1", "proposed_date" => $proposeDate,
                //         'project_stat_app' => "1", "approved_date" => $proposeDate,
                //         'project_stat_submit' => "1", "submited_date" => $proposeDate
                //     ]);
                // }
            }
            $this->response(['status' => true, 'message' => 'Berhasil multi propose project!'], 200);
        } catch (Exception $exp) {
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }

    public function multiCancel_put()
    {
        try {
            $DB2 = $this->load->database('gaSys2', true);
            $jwt = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param = $this->put();

            // Validation
            if (empty($param['projectNo'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok'], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if ($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if ($user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            $projectTemp = array();
            foreach ($param['projectNo'] as $item) {
                $temp = $DB2->get_where('master_project', ['project_no' => $item])->row();
                if (empty($temp->project_no)) {
                    $this->response(['status' => false, 'message' => 'Terdapat project yang tidak terdaftar!'], 200);
                    break;
                }
                array_push($projectTemp, $temp);
            }
            // End Validation

            foreach ($projectTemp as $item) {
                $DB2->where('project_no', $item->project_no)->update('master_project', ['project_stat' => "2"]);
            }
            $this->response(['status' => true, 'message' => 'Berhasil multi cancel project!'], 200);
        } catch (Exception $exp) {
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }

    public function upload_post()
    {
        try {
            $DB2 = $this->load->database('gaSys2', true);
            $jwt = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param = $this->post();

            if (empty($param['area']) || empty($param['projName']) || empty($param['projStatus']) || empty($param['projType']) || empty($param['projCategory']) || empty($param['projTotal'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok'], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if ($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if ($user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            $area = $DB2->get_where('master_area', ['area_code' => $param['area'], 'area_status' => '1'])->result();
            if ($area == null) $this->response(['status' => false, 'message' => 'Area tidak terdaftar atau dalam status tidak aktif!'], 200);

            $projStatus = $DB2->get_where('master_project_status', ['status_name' => $param['projStatus'], 'status_status' => '1'])->result();
            if ($projStatus == null) $this->response(['status' => false, 'message' => 'Project status tidak terdaftar atau dalam status tidak aktif!'], 200);

            $projType = $DB2->get_where('master_project_type', ['type_name' => $param['projType'], 'type_status' => '1'])->result();
            if ($projType == null) $this->response(['status' => false, 'message' => 'Project type tidak terdaftar atau dalam status tidak aktif!'], 200);

            $projCategory = $DB2->get_where('master_project_category', ['category_name' => $param['projCategory'], 'category_status' => '1'])->result();
            if ($projCategory == null) $this->response(['status' => false, 'message' => 'Project category tidak terdaftar atau dalam status tidak aktif!'], 200);

            // if (!is_numeric($param['projContract'])) $this->response(['status' => false, 'message' => 'Project contract harus bertipe angka!'], 200);
            if (!is_numeric($param['projTotal'])) $this->response(['status' => false, 'message' => 'Project total harus bertipe angka!'], 200);

            $lastProjectNo = $DB2->order_by('project_no', 'DESC')->like('project_no', 'MB')->limit(1)->get('master_project')->result();
            $projectNo = 1;
            if ($lastProjectNo != null) {
                $projectYear    = explode('/', $lastProjectNo[0]->project_no)[0];
                $projectNo      = (int) explode('/', $lastProjectNo[0]->project_no)[2] + 1;
                if (((int)date('Y') + 1) != $projectYear) $projectNo = 1;
            }

            $formData['project_no']             = ((int)date('Y') + 1) . '/' . 'MB/' . sprintf('%03d', $projectNo);;
            $formData['project_area']           = $param['area'];
            $formData['project_name']           = $param['projName'];
            $formData['project_status']         = $param['projStatus'];
            $formData['project_type']           = $param['projType'];
            $formData['project_category']       = $param['projCategory'];
            $formData['project_kontrak']        = $param['projContract'];
            $formData['project_totalvalue']     = $param['projTotal'];

            $picArea = $DB2->query('
                SELECT *
                FROM master_user mu 
                WHERE mu.user_role = "PICP" AND mu.user_area = "' . $param['area'] . '"
                ORDER BY RAND()
                LIMIT 1
            ')->row();

            $formData['project_pic'] = !empty($picArea->user_no) ? $picArea->user_no : NULL;
            $DB2->insert('master_project', $formData);

            $this->response(['status' => true, 'message' => 'Data berhasil disimpan!'], 200);
        } catch (Exception $exp) {
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }

    public function uploadMulti_post()
    {
        try {
            $DB2 = $this->load->database('gaSys2', true);
            $jwt = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));

            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if ($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if ($user->user_role != "PICP" && $user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            $fileUpload = $this->upload_excel();
            if ($fileUpload['status'] == false) {
                $this->response(['status' => false, 'message' => $fileUpload['msg']], 200);
            }

            $sheet      = \PhpOffice\PhpSpreadsheet\IOFactory::load($fileUpload['link']);
            $arrSheet   = $sheet->getActiveSheet()->toArray();
            $highestRow = $sheet->getActiveSheet()->getHighestRow();

            $dataStore   = array();
            $status      = true;
            $msg         = array();
            $no = 1;

            $lastProjectNo = $DB2->order_by('project_no', 'DESC')->like('project_no', 'MB')->limit(1)->get('master_project')->result();
            $projectNo = 1;
            if ($lastProjectNo != null) {
                $projectYear    = explode('/', $lastProjectNo[0]->project_no)[0];
                $projectNo      = (int) explode('/', $lastProjectNo[0]->project_no)[2] + 1;
                if (((int)date('Y') + 1) != $projectYear) $projectNo = 1;
            }

            for ($i = 8; $i <= $highestRow; $i++) {
                if ($arrSheet[$i][1] == null) break;

                $temp['project_no']         = ((int)date('Y') + 1) . '/' . 'MB/' . sprintf('%03d', $projectNo++);
                $temp['project_area']       = $arrSheet[$i][1];
                $temp['project_name']       = $arrSheet[$i][2];
                $temp['project_status']     = $arrSheet[$i][3];
                $temp['project_type']       = $arrSheet[$i][4];
                $temp['project_category']   = $arrSheet[$i][5];
                $temp['project_totalvalue'] = str_replace(',', '', $arrSheet[$i][6]);
                $temp['project_kontrak']    = str_replace(',', '', $arrSheet[$i][7]);
                $temp['project_mb']         = str_replace(',', '', $arrSheet[$i][8]);

                $checkCell = $this->cellValidation($temp, $i);
                if ($checkCell['status'] == false) {
                    array_push($msg, $checkCell['msg']);
                    $status = false;
                }
                array_push($dataStore, $temp);
            }

            if ($status == true) {
                foreach ($dataStore as $item) {
                    $picArea = $DB2->query('
                        SELECT *
                        FROM master_user mu 
                        WHERE mu.user_role = "PICP" AND mu.user_area = "' . $item['project_area'] . '"
                        ORDER BY RAND()
                        LIMIT 1
                    ')->row();

                    $item['project_pic'] = !empty($picArea->user_no) ? $picArea->user_no : NULL;
                    $DB2->insert('master_project', $item);
                }
                $this->response(['status' => true, 'message' => 'Data berhasil disimpan'], 200);
            } else {
                $this->response(['status' => false, 'message' => $msg], 200);
            }
        } catch (Exception $exp) {
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }

    public function uploadSA_post()
    {
        try {
            $DB2 = $this->load->database('gaSys2', true);
            $jwt = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param = $this->post();

            if (empty($param['area']) || empty($param['projName']) || empty($param['projStatus']) || empty($param['projType']) || empty($param['projCategory']) || empty($param['projTotal'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok'], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if ($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if ($user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            $area = $DB2->get_where('master_area', ['area_code' => $param['area'], 'area_status' => '1'])->result();
            if ($area == null) $this->response(['status' => false, 'message' => 'Area tidak terdaftar atau dalam status tidak aktif!'], 200);

            $projStatus = $DB2->get_where('master_project_status', ['status_name' => $param['projStatus'], 'status_status' => '1'])->result();
            if ($projStatus == null) $this->response(['status' => false, 'message' => 'Project status tidak terdaftar atau dalam status tidak aktif!'], 200);

            $projType = $DB2->get_where('master_project_type', ['type_name' => $param['projType'], 'type_status' => '1'])->result();
            if ($projType == null) $this->response(['status' => false, 'message' => 'Project type tidak terdaftar atau dalam status tidak aktif!'], 200);

            $projCategory = $DB2->get_where('master_project_category', ['category_name' => $param['projCategory'], 'category_status' => '1'])->result();
            if ($projCategory == null) $this->response(['status' => false, 'message' => 'Project category tidak terdaftar atau dalam status tidak aktif!'], 200);

            if (!is_numeric($param['projContract'])) $this->response(['status' => false, 'message' => 'Project contract harus bertipe angka!'], 200);
            if (!is_numeric($param['projTotal'])) $this->response(['status' => false, 'message' => 'Project total harus bertipe angka!'], 200);

            $lastProjectNo = $DB2->order_by('project_no', 'DESC')->like('project_no', 'SA')->limit(1)->get('master_project')->result();
            $projectNo = 1;
            if ($lastProjectNo != null) {
                $projectYear    = explode('/', $lastProjectNo[0]->project_no)[0];
                $projectNo      = (int) explode('/', $lastProjectNo[0]->project_no)[2] + 1;
                if (date('Y') != $projectYear) $projectNo = 1;
            }

            $formData['project_no']             = date('Y') . '/SA/' . sprintf('%03d', $projectNo);;
            $formData['project_area']           = $param['area'];
            $formData['project_name']           = $param['projName'];
            $formData['project_status']         = $param['projStatus'];
            $formData['project_type']           = $param['projType'];
            $formData['project_category']       = $param['projCategory'];
            $formData['project_kontrak']        = $param['projContract'];
            $formData['project_totalvalue']     = $param['projTotal'];

            $picArea = $DB2->query('
                SELECT *
                FROM master_user mu 
                WHERE mu.user_role = "PICP" AND mu.user_area = "' . $param['area'] . '"
                ORDER BY RAND()
                LIMIT 1
            ')->row();

            $formData['project_pic'] = !empty($picArea->user_no) ? $picArea->user_no : NULL;
            $DB2->insert('master_project', $formData);

            $this->response(['status' => true, 'message' => 'Data berhasil disimpan!', 'project_no' => $formData['project_no']], 200);
        } catch (Exception $exp) {
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }

    public function uploadFS_post()
    {
        try {
            $DB2 = $this->load->database('gaSys2', true);
            $jwt = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param = $this->post();

            if (empty($param['area']) || empty($param['projName']) || empty($param['projStatus']) || empty($param['projType']) || empty($param['projCategory']) || empty($param['projTotal'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok'], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if ($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if ($user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            $area = $DB2->get_where('master_area', ['area_code' => $param['area'], 'area_status' => '1'])->result();
            if ($area == null) $this->response(['status' => false, 'message' => 'Area tidak terdaftar atau dalam status tidak aktif!'], 200);

            $projStatus = $DB2->get_where('master_project_status', ['status_name' => $param['projStatus'], 'status_status' => '1'])->result();
            if ($projStatus == null) $this->response(['status' => false, 'message' => 'Project status tidak terdaftar atau dalam status tidak aktif!'], 200);

            $projType = $DB2->get_where('master_project_type', ['type_name' => $param['projType'], 'type_status' => '1'])->result();
            if ($projType == null) $this->response(['status' => false, 'message' => 'Project type tidak terdaftar atau dalam status tidak aktif!'], 200);

            $projCategory = $DB2->get_where('master_project_category', ['category_name' => $param['projCategory'], 'category_status' => '1'])->result();
            if ($projCategory == null) $this->response(['status' => false, 'message' => 'Project category tidak terdaftar atau dalam status tidak aktif!'], 200);

            if (!is_numeric($param['projContract'])) $this->response(['status' => false, 'message' => 'Project contract harus bertipe angka!'], 200);
            if (!is_numeric($param['projTotal'])) $this->response(['status' => false, 'message' => 'Project total harus bertipe angka!'], 200);

            $lastProjectNo = $DB2->order_by('project_no', 'DESC')->like('project_no', 'FS')->limit(1)->get('master_project')->result();
            $projectNo = 1;
            if ($lastProjectNo != null) {
                $projectYear    = explode('/', $lastProjectNo[0]->project_no)[0];
                $projectNo      = (int) explode('/', $lastProjectNo[0]->project_no)[2] + 1;
                if (date('Y') != $projectYear) $projectNo = 1;
            }

            $formData['project_no']             = date('Y') . '/FS/' . sprintf('%03d', $projectNo);;
            $formData['project_area']           = $param['area'];
            $formData['project_name']           = $param['projName'];
            $formData['project_status']         = $param['projStatus'];
            $formData['project_type']           = $param['projType'];
            $formData['project_category']       = $param['projCategory'];
            $formData['project_kontrak']        = $param['projContract'];
            $formData['project_totalvalue']     = $param['projTotal'];

            $picArea = $DB2->query('
                SELECT *
                FROM master_user mu 
                WHERE mu.user_role = "PICP" AND mu.user_area = "' . $param['area'] . '"
                ORDER BY RAND()
                LIMIT 1
            ')->row();

            $formData['project_pic'] = !empty($picArea->user_no) ? $picArea->user_no : NULL;
            $DB2->insert('master_project', $formData);

            $this->response(['status' => true, 'message' => 'Data berhasil disimpan!', 'project_no' => $formData['project_no']], 200);
        } catch (Exception $exp) {
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }

    public function uploadSADoc_post()
    {
        try {
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->post();

            // Validation
            if (empty($param['projectNo']) || empty($param['totUpload']) || empty($param['currUpload']) || empty($param['saType']) || empty($_FILES['file'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok'], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if ($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if ($user->user_role != "PICP" && $user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            $project = $DB2->get_where('master_project', ['project_no' => $param['projectNo']])->result();
            if ($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);
            if (explode('/', $project[0]->project_no)[1] != 'SA') $this->response(['status' => false, 'message' => 'Project bukan bertipe Special Approval!'], 200);

            if ($project[0]->project_pic != $user->user_no && $user->user_role != "Admin PICP")  $this->response(['status' => false, 'message' => 'Project bukan bagian otoritas anda!'], 200);

            $saType = $DB2->get_where('master_sa_type', ['sa_type_no' => $param['saType']])->row();
            if (empty($saType->sa_type_no))  $this->response(['status' => false, 'message' => 'SA type tidak terdaftar!'], 200);
            // End Validation

            if ($param['currUpload'] <= $param['totUpload']) {
                $uploadSA = $this->uploadSA(explode('.', $_FILES['file']['name'])[0]);
                if ($uploadSA['status'] == false) $this->response(['status' => false, 'message' => $uploadSA['msg']], 200);
            } else {
                $this->response(['status' => false, 'message' => 'Status upload telah melebihi total upload'], 200);
            }

            $formData['project_no']             = $param['projectNo'];
            $formData['sa_type_no']             = $param['saType'];
            $formData['sa_document_name']       = $uploadSA['name'];
            $formData['sa_document_link']       = $uploadSA['link'];
            $formData['submitted_date']         = date('Y-m-d H:i:s');
            $DB2->insert('transaction_sa_document', $formData);

            $this->response(['status' => true, 'message' => 'Data berhasil terupload'], 200);
        } catch (Exception $exp) {
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }

    public function uploadFSDoc_post()
    {
        try {
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->post();

            // Validation
            if (empty($param['projectNo']) || empty($param['totUpload']) || empty($param['currUpload']) || empty($param['fsType']) || empty($_FILES['file'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok'], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if ($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if ($user->user_role != "PICP" && $user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            $project = $DB2->get_where('master_project', ['project_no' => $param['projectNo']])->result();
            if ($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);
            if (explode('/', $project[0]->project_no)[1] != 'FS') $this->response(['status' => false, 'message' => 'Project bukan bertipe FS!'], 200);

            if ($project[0]->project_pic != $user->user_no && $user->user_role != "Admin PICP")  $this->response(['status' => false, 'message' => 'Project bukan bagian otoritas anda!'], 200);

            $fsType = $DB2->get_where('master_fs_type', ['fs_type_no' => $param['fsType']])->row();
            if (empty($fsType->fs_type_no))  $this->response(['status' => false, 'message' => 'FS type tidak terdaftar!'], 200);
            // End Validation

            if ($param['currUpload'] <= $param['totUpload']) {
                $uploadFS = $this->uploadFS(explode('.', $_FILES['file']['name'])[0]);
                if ($uploadFS['status'] == false) $this->response(['status' => false, 'message' => $uploadFS['msg']], 200);
            } else {
                $this->response(['status' => false, 'message' => 'Status upload telah melebihi total upload'], 200);
            }

            $formData['project_no']             = $param['projectNo'];
            $formData['fs_type_no']             = $param['fsType'];
            $formData['fs_document_name']       = $uploadFS['name'];
            $formData['fs_document_link']       = $uploadFS['link'];
            $formData['submitted_date']         = date('Y-m-d H:i:s');
            $DB2->insert('transaction_fs_document', $formData);

            $this->response(['status' => true, 'message' => 'Data berhasil terupload'], 200);
        } catch (Exception $exp) {
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }

    public function cellValidation($param, $row)
    {
        $status = true;
        $msg = array();
        $DB2 = $this->load->database('gaSys2', true);
        // checking area
        $area = $DB2->get_where('master_area', ['area_code' => $param['project_area']])->result();
        if ($area == null) {
            $status = false;
            array_push($msg, 'Kode Area B' . ($row + 1) . ' tidak terdaftar!');
        }
        // checking nama
        if ($param['project_name'] == null) {
            $status = false;
            array_push($msg, 'Nama Project C' . ($row + 1) . ' tidak boleh kosong!');
        }
        // checking status
        if ($param['project_status'] == null) {
            $status = false;
            array_push($msg, 'Status Project D' . ($row + 1) . ' tidak boleh kosong!');
        } else {
            $projStatus = $DB2->get_where('master_project_status', ['status_name' => $param['project_status']])->result();
            if ($projStatus == null) {
                $status = false;
                array_push($msg, 'Status Project D' . ($row + 1) . ' tidak terdaftar!');
            }
        }
        // checking type
        if ($param['project_type'] == null) {
            $status = false;
            array_push($msg, 'Tipe Project E' . ($row + 1) . ' tidak boleh kosong!');
        } else {
            $projType = $DB2->get_where('master_project_type', ['type_name' => $param['project_type']])->result();
            if ($projType == null) {
                $status = false;
                array_push($msg, 'Tipe Project E' . ($row + 1) . ' tidak terdaftar!');
            }
        }
        // checking category
        if ($param['project_category'] == null) {
            $status = false;
            array_push($msg, 'Category Project F' . ($row + 1) . ' tidak boleh kosong!');
        } else {
            $projCategory = $DB2->get_where('master_project_category', ['category_name' => $param['project_category']])->result();
            if ($projCategory == null) {
                $status = false;
                array_push($msg, 'Category Project F' . ($row + 1) . ' tidak terdaftar!');
            }
        }

        // checking total value
        if ($param['project_totalvalue'] == null) {
            $status = false;
            array_push($msg, 'Nilai Total Pekerjaan G' . ($row + 1) . ' tidak boleh kosong!');
        } else {
            if (!is_numeric($param['project_totalvalue'])) {
                $status = false;
                array_push($msg, 'Nilai Total Pekerjaan G' . ($row + 1) . ' harus bertipe angka!');
            }
        }

        return ['status' => $status, 'msg' => $msg];
    }

    public function upload_excel()
    {
        $conf['upload_path']    = "./uploads/project/fileUploaded";
        $conf['allowed_types']  = "xls|xlsx";
        $conf['max_size']       = 2048;
        $conf['file_name']      = time();
        $conf['encrypt_name']   = TRUE;

        $this->upload->initialize($conf);
        if ($this->upload->do_upload('file')) {
            $file = $this->upload->data();
            return [
                'status' => true,
                'msg'   => 'Data berhasil terupload',
                'link'  => './uploads/project/fileUploaded/' . $file['file_name']
            ];
        } else {
            return [
                'status' => false,
                'msg'   => $this->upload->display_errors(),
            ];
        }
    }
    public function uploadSA($fileName)
    {
        $path = 'uploads/project/sa';
        $conf['upload_path']    = $path;
        $conf['allowed_types']  = "pdf";
        $conf['max_size']       = 2048;
        $conf['file_name']      = str_replace(' ', '_', $fileName);

        $this->upload->initialize($conf);
        if ($this->upload->do_upload('file')) {
            $file = $this->upload->data();
            return [
                'status' => true,
                'msg'   => 'Data berhasil terupload',
                'name'  => $fileName,
                'link'  => site_url($path . "/" . $file['file_name'])
            ];
        } else {
            return [
                'status' => false,
                'msg'   => $this->upload->display_errors(),
            ];
        }
    }
    public function uploadFS($fileName)
    {
        $path = 'uploads/project/fs';
        $conf['upload_path']    = $path;
        $conf['allowed_types']  = "pdf";
        $conf['max_size']       = 2048;
        $conf['file_name']      = str_replace(' ', '_', $fileName);

        $this->upload->initialize($conf);
        if ($this->upload->do_upload('file')) {
            $file = $this->upload->data();
            return [
                'status' => true,
                'msg'   => 'Data berhasil terupload',
                'name'  => $fileName,
                'link'  => site_url($path . "/" . $file['file_name'])
            ];
        } else {
            return [
                'status' => false,
                'msg'   => $this->upload->display_errors(),
            ];
        }
    }

    public function queryGeneralInfo($projectNo)
    {
        $DB2 = $this->load->database('gaSys2', true);
        return $DB2->query("
            SELECT 
                mp.*,
                ve.vendor_name    
            FROM 
                master_project mp
            LEFT JOIN master_vendor ve ON mp.vendor_no=ve.vendor_no
            WHERE 
                project_no = '" . $projectNo . "'
        ")->row();
    }

    public function getTermin($projectNo)
    {
        $DB2    = $this->load->database('gaSys2', true);
        return $DB2->query("
            SELECT  
                contract_dp ,
                contract_progress,
                contract_retensi
            FROM transaction_contract 
            WHERE 
                project_no = '" . $projectNo . "'
        ")->row_array();
    }
}
