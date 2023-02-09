<?php defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
require_once(FCPATH . '/vendor/autoload.php');

class Contract extends RestController {
    
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
            $pic = null;
            $search = null;
            if(!empty($param['pic'])) $pic = $param['pic'];
            if(!empty($param['search'])) $search = $param['search'];
            $dnds = $this->queryGetListContract($pic, $param['limit'], $param['page'], $search);

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

            $project = $DB2->get_where('master_project', ['project_no' => $param['projectNo']])->result();
            if($project == null) $this->response(['status' => false, 'message' => 'Project tidak terdaftar!'], 200);

            if($project[0]->project_pic != $user->user_no && $user->user_role != "Admin PICP")  $this->response(['status' => false, 'message' => 'Project bukan bagian otoritas anda!'], 200);
            // End Validation

            $project = $this->queryGetDetail($param['projectNo']);
            $this->response(['status' => true, 'message' => ['project' => $project]], 200);
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
            // End Validation
            $vendor = $DB2->get_where('master_vendor', ['vendor_no' => $project[0]->vendor_no])->row();

            $DB2->where('project_no', $param['projectNo'])->update('master_project', ['project_stat_monitoring' => "1", "monitoring_date" => date('Y-m-d H:i:s')]);
            if($vendor->vendor_password == null){
                $pic = $DB2->get_where('master_user', ['user_no' => $project[0]->project_pic])->row();
                $newPass = strtolower(substr(str_replace(' ', '', $vendor->vendor_name), 0, 5).rand(100, 999));

                $DB2->where('vendor_no', $vendor->vendor_no)->update('master_vendor', ['vendor_password' => hash('sha256', md5($newPass))]);

                $email['to']        = $vendor->vendor_email.";".$pic->user_email;
                $email['subject']   = 'United Tractors: Vendor Account';
                $email['message']   = $this->htmlVendor($vendor->vendor_name, $vendor->vendor_email, $newPass);
                $this->send($email);
            }

            $this->response(['status' => true, 'message' => 'Berhasil submit contract!'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
    }
    public function queryGetListContract($pic, $limit, $page, $searchFil){
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

        $datas =  $DB2->query("
            SELECT
                mu.user_initials ,
                mp.project_no ,
                mp.project_name ,
                mp.project_area ,
                mp.project_stat_monitoring ,
                mp.monitoring_date
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
                mp.project_stat_submit  = '1'
            GROUP BY mp.project_no 
            ORDER BY mp.submited_date DESC
            LIMIT ".$limit."
            OFFSET ".(($page-1)*$limit)."
        ")->result();

        $allData =  $DB2->query("
            SELECT
                mu.user_initials ,
                mp.project_no ,
                mp.project_name ,
                mp.project_area ,
                mp.project_stat_monitoring ,
                mp.monitoring_date
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
                mp.project_stat_submit  = '1'
            GROUP BY mp.project_no 
            ORDER BY mp.submited_date DESC
        ")->result();

        $pagination['limit']        = $limit;
        $pagination['page']         = $page;
        $pagination['total_page']   = ceil((count($allData) / $limit));
        $pagination['total_data']   = count($allData);
        return ['pagination' => $pagination, 'data' => $datas];
    }
    public function queryGetDetail($projectNo){
        $DB2 = $this->load->database('gaSys2', true);
        return $DB2->query("
            SELECT 
                mp.project_no ,
                mp.project_name,
                mp.project_area ,
                mp.project_status ,
                mp.project_type ,
                mp.project_category ,
                (
                	SELECT tc.contract_branch_spk
                	FROM transaction_contract tc
                	WHERE tc.project_no = mp.project_no
                ) as project_spk,
                (
                    SELECT mv.vendor_name 
                    FROM master_vendor mv 
                    WHERE mv.vendor_no = mp.vendor_no 
                ) as vendor,
                mp.project_totalvalue 
            FROM master_project mp 
            WHERE mp.project_no= '".$projectNo."'
        ")->row();
    }
    public function send($param){
        // Configure API key authorization: api-key
        $config = SendinBlue\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key', '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');

        // Uncomment below line to configure authorization using: partner-key
        // $config = SendinBlue\Client\Configuration::getDefaultConfiguration()->setApiKey('partner-key', 'YOUR_API_KEY');

        $apiInstance = new SendinBlue\Client\Api\TransactionalEmailsApi(
            // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
            // This is optional, `GuzzleHttp\Client` will be used as default.
            new GuzzleHttp\Client(),
            $config
        );
        $sendSmtpEmail = new \SendinBlue\Client\Model\SendSmtpEmail();
        
        $tos        = explode(';', $param['to']);
        $emailTos   = array();
        foreach ($tos as $item) {
            $tempEmail['email'] = $item;
            array_push($emailTos, $tempEmail);
        }
        
        if(!empty($param['attach'])){
            $attach = array();
            foreach ($param['attach'] as $item) {
                $tempAttach['url'] = $item;
                array_push($attach, $tempAttach);
            }
            $sendSmtpEmail['attachment'] = $attach;
        }

        $sendSmtpEmail['sender']        = array('name' => "Building Management PT United Tractors Tbk", 'email' => 'admgeneralaffairs@unitedtractors.com');
        $sendSmtpEmail['subject']       = $param['subject'];
        $sendSmtpEmail['to']            = $emailTos;
        $sendSmtpEmail['htmlContent']   = $param['message'];
        $sendSmtpEmail['headers']       = array('X-Mailin-custom'=>'custom_header_1:custom_value_1|custom_header_2:custom_value_2');

        try {
            $result = $apiInstance->sendTransacEmail($sendSmtpEmail);
            // print_r($result);
        } catch (Exception $e) {
            echo 'Exception when calling TransactionalEmailsApi->sendTransacEmail: ', $e->getMessage(), PHP_EOL;
        }
    }
    public function htmlVendor($vendor, $email , $pass){
        $html = '
            <p>Attn: Mr/Mrs PT United Tractors Tbk</p>
            <p>Dear Sir/Madam,</p>
            <br>
            <p>Warmest greetings from United Tractors,</p>
            <br>
            <p>We would like to inform you that vendor account, vendor account can be checked below: </p>
            <p>Vendor    : <b>'.$vendor.'</b></p>
            <p>Email    : <b>'.$email.'</b></p>
            <p>Password : <b>'.$pass.'</b></p>
            <br>
            <div>Shall you have any question or further information, please contact us at +62 21 24579999 ext. 16053 or by email to <a href="mailto:admgeneralaffairs@unitedtractors.com">admgeneralaffairs@unitedtractors.com</a></div>
            <div>Thank you for your kind attention and coorperation.</div>
            <br>
            <div>Sincerely,</div>
            <div>Building Management</div>
            <div><b>PT United Tractors Tbk</b></div>
        ';
        return $html;
    }
}
