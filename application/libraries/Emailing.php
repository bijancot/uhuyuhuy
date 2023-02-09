<?php
    require_once(FCPATH . '/vendor/autoload.php');
    class Emailing {
        public function send($param){
            // Configure API key authorization: api-key
            $config = SendinBlue\Client\Configuration::getDefaultConfiguration()->setApiKey('api-key', 'YOUR_API_KEY');

            // Uncomment below line to configure authorization using: partner-key
            // $config = SendinBlue\Client\Configuration::getDefaultConfiguration()->setApiKey('partner-key', 'YOUR_API_KEY');

            $apiInstance = new SendinBlue\Client\Api\TransactionalEmailsApi(
                // If you want use custom http client, pass your client which implements `GuzzleHttp\ClientInterface`.
                // This is optional, `GuzzleHttp\Client` will be used as default.
                new GuzzleHttp\Client(),
                $config
            );
            
            $sendSmtpEmailSender = new \SendinBlue\Client\Model\SendSmtpEmailSender();
            $sendSmtpEmailSender['name']    = "PT United Tractors TBK";
            $sendSmtpEmailSender['email']   = "admgeneralaffairs@unitedtractors.com";

            $sendSmtpEmail = new \SendinBlue\Client\Model\SendSmtpEmail();
            $sendSmtpEmail['sender'] =  $sendSmtpEmailSender;
            $sendSmtpEmail['to'] = "ilham.sagitaputra@gmail.com";
            $sendSmtpEmail['htmlContent'] = "<h1>Masuk</h1>";
            $sendSmtpEmail['headers'] = array('X-Mailin-custom'=>'custom_header_1:custom_value_1|custom_header_2:custom_value_2');

        }
    }
?>