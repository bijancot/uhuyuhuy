<?php defined('BASEPATH') OR exit('No direct script access allowed');
require_once(FCPATH . '/vendor/autoload.php');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Complaint extends RestController {
    
    public function __construct() {
        parent::__construct();
        $this->load->helper("url");
        $this->load->library(array('upload', 'image_lib'));
    }

    public function index_post(){
        try{
            $DB2    = $this->load->database('gaSys2', true);
            $jwt    = JWT::decode($this->input->get_request_header('Authorization'), new Key($this->config->item('SECRET_KEY'), 'HS256'));
            $param  = $this->post();
            // Validation
            if(empty($param['name']) || empty($param['position']) || empty($param['company'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            if(empty($param['email']) || empty($param['phone']) || empty($param['city']) || empty($param['projectName'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            if(empty($param['subject']) || empty($param['message'])) $this->response(['status' => false, 'message' => 'Parameter tidak cocok' ], 200);
            
            $vendor = $DB2->get_where('master_vendor', ['vendor_no' => $jwt->vendor_no])->row();
            if($jwt->vendor_session != $vendor->vendor_session || $vendor->vendor_session == null) $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
            
            // End Validation
            $email['to']        = 'dimasdwi340@gmail.com';
            $email['subject']   = 'New Complaint';
            $email['message']   = $this->htmlComplaint($param);
            $this->send($email);

            $this->response(['status' => true, 'message' => 'Berhasil mengrim complaint!'], 200);
        }catch(Exception $exp){
            $this->response(['status' => false, 'message' => 'Login terlebih dahulu!'], 401);
        }
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
            // echo 'Exception when calling TransactionalEmailsApi->sendTransacEmail: ', $e->getMessage(), PHP_EOL;
        }
    }

    public function htmlComplaint($param){
        $html = '
            <h3>Complaint Form</h3>
            <br>
            <table border="0" style="border-collapse: collapse;"> 
                <tr>
                    <td style="width: 50%">Name</td>
                    <td style="width: 50%">: '.$param['name'].'</td>
                </tr>
                <tr>
                    <td style="width: 50%">Position</td>
                    <td style="width: 50%">: '.$param['position'].'</td>
                </tr>
                <tr>
                    <td style="width: 50%">Company</td>
                    <td style="width: 50%">: '.$param['company'].'</td>
                </tr>
                <tr>
                    <td style="width: 50%">Email</td>
                    <td style="width: 50%">: '.$param['email'].'</td>
                </tr>
                <tr>
                    <td style="width: 50%">Phone</td>
                    <td style="width: 50%">: '.$param['phone'].'</td>
                </tr>
                <tr>
                    <td style="width: 50%">City</td>
                    <td style="width: 50%">: '.$param['city'].'</td>
                </tr>                           
                <tr>
                    <td style="width: 50%">Project Name</td>
                    <td style="width: 50%">: '.$param['projectName'].'</td>
                </tr>
                <tr>
                    <td style="width: 50%">Subject</td>
                    <td style="width: 50%">: '.$param['subject'].'</td>
                </tr>
                <tr>
                    <td style="width: 50%">Message</td>
                    <td style="width: 50%">: '.$param['message'].'</td>
                </tr>
            </table>
        ';

        return $html;
    }
    
}