<?php defined('BASEPATH') or exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

require_once(FCPATH . '/vendor/autoload.php');

class Si extends RestController
{

    public function __construct()
    {
        parent::__construct();
        $this->load->helper("url");
        $this->load->library(array('upload', 'image_lib'));
    }

    public function index_post(){
        try{
            $DB2        = $this->load->database('gaSys2', true);
            $jwt        = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param      = $this->post();
            $currDate   = date('Y-m-d H:i:s');
            // $currDate   = "2022-11-01 08:00:00";

            // Validation
            if(empty($param['projectNo']) || empty($param['perihal']) || empty($param['vol']) || empty($param['alasan']) || empty($param['pengaruhBiaya'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            if(empty($param['pengaruhWaktu']) || empty($param['pengaruhScope']) || empty($_FILES['doc1']) || empty($_FILES['doc2'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            $user = $DB2->get_where('master_user', ['user_no' => $jwt->user_no])->row();
            if($jwt->user_sess != $user->user_session || $user->user_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            if($user->user_role != "PICP" && $user->user_role != "Admin PICP") $this->response(['status' => false, 'message' => 'Anda tidak memiliki hak akses!'], 200);

            $project = $DB2->get_where('master_project', ['project_no' => $param['projectNo']])->result();
            if($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);

            if($project[0]->project_pic != $user->user_no && $user->user_role != "Admin PICP")  $this->response(['status' => false, 'message' => 'Project bukan bagian otoritas anda!'], 200);
            // End Validation
            $siData = $DB2->order_by('si_date', 'desc')->limit('1')->get_where('transaction_si', ['YEAR(si_date)' => date_format(date_create($currDate), 'Y'), 'MONTH(si_date)' => date_format(date_create($currDate), 'n')])->result();
            $month  = $this->datefunction->getMonthRomawi()[date_format(date_create($currDate), 'n')];
            $year   = date_format(date_create($currDate), 'y');

            $siNo = 1;
            if($siData != null){
                $siNo = str_replace('-P', '', explode('/', $siData[0]->si_no)[1]);
                $siNo = (int) $siNo + 1;
            }

            $formData['si_no']                  = 'SI/'.sprintf('%02d', $siNo).'-P/9972/'.$month.'/'.$year;
            $formData['si_vo_name']             = sprintf('%02d', $siNo).'/PRO-VO/UT/'.$month.'/'.$year;
            $formData['project_no']             = $param['projectNo'];
            $formData['si_perihal']             = $param['perihal'];
            $formData['si_item']                = $param['item'];
            $formData['si_vol']                 = $param['vol'];
            $formData['si_alasan_perubahan']    = $param['alasan'];
            $formData['si_pengaruh_biaya']      = $param['pengaruhBiaya'] == '2' ? '0' : '1';
            $formData['si_pengaruh_waktu']      = $param['pengaruhWaktu'] == '2' ? '0' : '1';
            $formData['si_pengaruh_scope']      = $param['pengaruhScope'] == '2' ? '0' : '1';
            $formData['si_catatan_biaya']       = !empty($param['catatanBiaya']) ? $param['catatanBiaya'] : NULL;
            $formData['si_keterangan_waktu']    = !empty($param['keteranganWaktu']) ? $param['keteranganWaktu'] : NULL;
            $formData['si_keterangan_scope']    = !empty($param['keteranganScope']) ? $param['keteranganScope'] : NULL;
            $formData['si_date']                = date('Y-m-d H:i:s');

            if($_FILES['doc1']['name'] != null){
                $uploadFile = $this->uploadFile(explode('.', $_FILES['doc1']['name'])[0], 'doc1', 'docPendukung');
                if($uploadFile['status'] == false) $this->response(['status' => false, 'message' => $uploadFile['msg']], 200);
                $formData['si_doc1_name'] = $uploadFile['name'];
                $formData['si_doc1_link'] = $uploadFile['link'];
            }
            if($_FILES['doc2']['name'] != null){
                $uploadFile = $this->uploadFile(explode('.', $_FILES['doc2']['name'])[0], 'doc2', 'docPendukung');
                if($uploadFile['status'] == false) $this->response(['status' => false, 'message' => $uploadFile['msg']], 200);
                $formData['si_doc2_name'] = $uploadFile['name'];
                $formData['si_doc2_link'] = $uploadFile['link'];
            }

            
            $genSI = $this->document->genSI($formData);
            $formData['si_link'] = $genSI;
            
            $genVO = $this->document->genVO($formData);
            $formData['si_vo_link'] = $genVO;
            $DB2->insert('transaction_si', $formData);
            
            $this->response(['status' => true, 'message' => 'Data berhasil disimpan', 'data' => $formData], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }

    public function uploadFile($fileName, $file, $path){
        $path = 'uploads/project/si/'.$path;
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
    }
}
