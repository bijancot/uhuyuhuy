<?php
    require_once(FCPATH . '/vendor/autoload.php');
    class EmailingController extends CI_Controller {
        public function __construct(){
            parent::__construct();
            $this->load->model('DebitNote');
            $config = [
                'mailtype'      => 'html',
                'charset'       => 'utf-8',
                'protocol'      => 'smtp',
                'smtp_host'     => 'smtp.gmail.com',
                'smtp_user'     => '', 
                'smtp_pass'     => '', 
                'smtp_crypto'   => 'ssl',
                'smtp_port'     => 465,
                'crlf'    => "\r\n",
                'newline' => "\r\n"
            ];
            $this->load->library('email', $config);
        }

        public function sendEmail(){
            $param    = $_POST;

            $debitnote = $this->DebitNote->get(['ID_DEBITNOTE' => $param['ID_DEBITNOTE']]);
            $idTenant  = $debitnote->NOPELANGGAN_DEBITNOTE;
            
            $date = date('Y-m-d');
            $dnDateEnd = date_create($param['TGLJATUH_DEBITNOTE']);
            $dnDateEnd = date_format($dnDateEnd, 'Y-m-d');

            $status = '4';
            if($date > $dnDateEnd){
                $status = '5'; 
            }
            $this->DebitNote->update(['ID_DEBITNOTE' => $param['ID_DEBITNOTE'], 'STAT_DEBITNOTE' => $status, 'TGLPUBLISHED_DEBITNOTE' => $date]);

            $filter['EMAIL_DEBITNOTE']          = $param['EMAIL_DEBITNOTE'];
            $filter['whereIn']['table']         = 'STAT_DEBITNOTE';
            $filter['whereIn']['values']        = array('4','5');
            $filter['orderBy']                  = 'EMAIL_DEBITNOTE ASC, TGLJATUH_DEBITNOTE DESC';
            $debitNotes = $this->DebitNote->getReminder($filter);
            
            if($debitNotes != null){
                $dnEmail            = '';
                $dn['email']        = array();

                foreach ($debitNotes as $item) {
                    if($dnEmail != $item->EMAIL_DEBITNOTE){
                        $dnEmail                    = $item->EMAIL_DEBITNOTE;
                        $dn['dataHtml'][$dnEmail]   = array();
                        $dn['attach'][$dnEmail]     = array();
                        array_push($dn['email'], $dnEmail);
                    }
                    array_push($dn['dataHtml'][$dnEmail], $item);
                    array_push($dn['attach'][$dnEmail], $item->PATH_DEBITNOTE);
                }
                
                foreach ($dn['email'] as $item) {
                    $email['to']        = $item;
                    $email['subject']   = 'United Tractors: Payment Reminder';
                    $email['message']   = $this->htmlPaymentProgress($dn['dataHtml'][$item], $idTenant);
                    $email['attach']    = $dn['attach'][$item];
                    $this->send($email);
                }
            }

            redirect('debitnote/approved');
        }
        public function sendEmailMulti(){
            $param    = explode(',', $_POST['ID_DEBITNOTE']);
            $dn = $this->db->where_in('ID_DEBITNOTE', $param)->get('DEBITNOTE')->result();

            foreach($dn as $key){
                $idTenant = $key->NOPELANGGAN_DEBITNOTE;
                $date = date('Y-m-d');
                $dnDateEnd = date_create($key->TGLJATUH_DEBITNOTE);
                $dnDateEnd = date_format($dnDateEnd, 'Y-m-d');
    
                $status = '4';
                if($date > $dnDateEnd){
                    $status = '5'; 
                }
                $this->DebitNote->update(['ID_DEBITNOTE' => $key->ID_DEBITNOTE, 'STAT_DEBITNOTE' => $status, 'TGLPUBLISHED_DEBITNOTE' => $date]);
    
                $filter['EMAIL_DEBITNOTE']          = $key->EMAIL_DEBITNOTE;
                $filter['whereIn']['table']         = 'STAT_DEBITNOTE';
                $filter['whereIn']['values']        = array('4','5');
                $filter['orderBy']                  = 'EMAIL_DEBITNOTE ASC, TGLJATUH_DEBITNOTE DESC';
                $debitNotes = $this->DebitNote->getReminder($filter);
                
                if($debitNotes != null){
                    $dnEmail            = '';
                    $dn['email']        = array();
    
                    foreach ($debitNotes as $item) {
                        if($dnEmail != $item->EMAIL_DEBITNOTE){
                            $dnEmail                    = $item->EMAIL_DEBITNOTE;
                            $dn['dataHtml'][$dnEmail]   = array();
                            $dn['attach'][$dnEmail]     = array();
                            array_push($dn['email'], $dnEmail);
                        }
                        array_push($dn['dataHtml'][$dnEmail], $item);
                        array_push($dn['attach'][$dnEmail], $item->PATH_DEBITNOTE);
                    }
                    
                    foreach ($dn['email'] as $item) {
                        $email['to']        = $item;
                        $email['subject']   = 'United Tractors: Payment Reminder';
                        $email['message']   = $this->htmlPaymentProgress($dn['dataHtml'][$item], $idTenant);
                        $email['attach']    = $dn['attach'][$item];
                        $this->send($email);
                    }
                }
            }
            
            redirect('debitnote/approved');
        }

        public function paymentProgress($period){
            $date = date('Y-m-d', strtotime('-'.$period.' day'));

            $filter['TGLPUBLISHED_DEBITNOTE']   = $date;
            $filter['whereIn']['table']         = 'STAT_DEBITNOTE';
            $filter['whereIn']['values']        = array('4','5');
            $filter['orderBy']                  = 'EMAIL_DEBITNOTE ASC, TGLJATUH_DEBITNOTE DESC';
            $debitNotes = $this->DebitNote->getReminder($filter);
            if($debitNotes != null){
                $dnEmail            = '';
                $dn['email']        = array();
                $tenants            = array();

                foreach ($debitNotes as $item) {
                    if($dnEmail != $item->EMAIL_DEBITNOTE){
                        $idTenant                   = $item->NOPELANGGAN_DEBITNOTE;
                        $dnEmail                    = $item->EMAIL_DEBITNOTE;
                        $dn['dataHtml'][$dnEmail]   = array();
                        $dn['attach'][$dnEmail]     = array();
                        $tenants[$dnEmail]          = $idTenant; 
                        array_push($dn['email'], $dnEmail);
                    }
                    array_push($dn['dataHtml'][$dnEmail], $item);
                    array_push($dn['attach'][$dnEmail], $item->PATH_DEBITNOTE);
                }
                
                foreach ($dn['email'] as $item) {
                    $email['to']        = $item;
                    $email['subject']   = 'United Tractors: Payment Reminder';
                    $email['message']   = $this->htmlPaymentProgress($dn['dataHtml'][$item], $tenants[$item]);
                    $email['attach']    = $dn['attach'][$item];
                    $this->send($email);
                }
            }

        }

        public function paymentOverdue($period){
            $date = date('Y-m-d', strtotime('-'.$period.' day'));
            
            $filter['TGLJATUH_DEBITNOTE']           = $date;
            $filter['whereIn']['table']             = 'STAT_DEBITNOTE';
            $filter['whereIn']['values']            = array('4','5');
            $filter['orderBy']                      = 'EMAIL_DEBITNOTE ASC, TGLJATUH_DEBITNOTE DESC';
            $debitNotes = $this->DebitNote->getReminder($filter);

            if($debitNotes != null){
                $dnEmail            = '';
                $dn['email']        = array();
                $tenants            = array();

                foreach ($debitNotes as $item) {
                    if($dnEmail != $item->EMAIL_DEBITNOTE){
                        $idTenant                   = $item->NOPELANGGAN_DEBITNOTE;
                        $dnEmail                    = $item->EMAIL_DEBITNOTE;
                        $dn['dataHtml'][$dnEmail]   = array();
                        $dn['attach'][$dnEmail]     = array();
                        $tenants[$dnEmail]          = $idTenant; 
                        array_push($dn['email'], $dnEmail);
                    }
                    array_push($dn['dataHtml'][$dnEmail], $item);
                    array_push($dn['attach'][$dnEmail], $item->PATH_DEBITNOTE);
                }
                
                foreach ($dn['email'] as $item) {
                    $email['to']        = $item;
                    $email['subject']   = 'United Tractors: Overdue Payment Confirmation';
                    $email['message']   = $this->htmlPaymentOverdue($dn['dataHtml'][$item], $tenants[$item]);
                    $email['attach']    = $dn['attach'][$item];
                    $this->send($email);
                }
            }
            // print_r($filter);
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
                print_r($result);
            } catch (Exception $e) {
                echo 'Exception when calling TransactionalEmailsApi->sendTransacEmail: ', $e->getMessage(), PHP_EOL;
            }
        }

        public function checkTenant($name){
            $tenant = $this->db->get_where('DEBITNOTE_TENANT', ['NAMA_TENANT' => strtoupper($name)])->row();
            $noPelanggan = $this->db->get_where('DEBITNOTE', ['NAMAPERUSAHAAN_DEBITNOTE' => strtoupper($name)])->row()->NOPELANGGAN_DEBITNOTE;
            if($tenant == null){
                $this->db->insert('DEBITNOTE_TENANT', ['ID_TENANT' => $noPelanggan, 'NAMA_TENANT' => strtoupper($name)]);
            }
            return $tenant = $this->db->get_where('DEBITNOTE_TENANT', ['NAMA_TENANT' => strtoupper($name)])->row()->ID_TENANT;
        }

        public function htmlPaymentProgress($param, $idTenant){
            $html = '
                <p>Attn: Mr/Mrs PT United Tractors Tbk</p>
                <p>Dear Sir/Madam,</p>
                <br>
                <p>Warmest greetings from United Tractors,</p>
                <br>
                <p>This is a gentle reminder to infrom you that the invoices listed below will be due in the next few days: </p>
                <br>
                <table border="1" style="border-collapse: collapse;width: 100%;">
                    <tr>
                        <th style="width: 5%">No</th>
                        <th style="width: 17%">Invoice No</th>
                        <th style="width: 13%">Invoice Date</th>
                        <th style="width: 30%">Invoice Description</th>
                        <th style="width: 8%">Currency</th>
                        <th style="width: 14%">Amount</th>
                        <th style="width: 13%">Due Date</th>
                    </tr>
            ';

            $no = 1;
            foreach ($param as $item) {
                $dateInvoice = date_create($item->TGLFAKTUR_DEBITNOTE);
                $dateDueDate = date_create($item->TGLJATUH_DEBITNOTE);

                $html .= '
                    <tr>
                        <td style="text-align: center;">'.$no.'</td>
                        <td style="text-align: center;">'.$item->NOFAKTUR_DEBITNOTE.'</td>
                        <td style="text-align: center;">'.date_format($dateInvoice, 'j F Y').'</td>
                        <td> '.$item->BARANGJASA_DEBITNOTE.' </td>
                        <td style="text-align: center;">'.$item->MATAUANG.'</td>
                        <td style="text-align: center;">'.number_format($item->GRANDTOTAL_DEBITNOTE).'</td>
                        <td style="text-align: center;">'.date_format($dateDueDate, 'j F Y').'</td>
                    </tr>
                ';
                $no++;
            }
            
            $html .= '
                </table>
                <br>
                <p>To avoid any late penalties, please make the payment no later than the invoice due date.</p>
                <p>The payment should be transfer, referring the invoice number, directly to our accoutn as below:</p>
                <table>
                    <tr>
                        <td>Bank Name</td>
                        <td>:</td>
                        <td>Bank Permata</td>
                    </tr>
                    <tr>
                        <td>Bank/Virtual Account Number</td>
                        <td>:</td>
                        <td>0200330021</td>
                    </tr>
                    <tr>
                        <td>Benefeciary/Account Name</td>
                        <td>:</td>
                        <td>PT. United Tractors Tbk</td>
                    </tr>
                </table>
                <br>
                <div>If the payment has already been made please disregard this reminder and kindly inform us the proof of the payment.</div>
                <div></div>
                <div>Shall you have any question or further information, please contact us at +62 21 24579999 ext. 16053 or by email to <a href="mailto:admgeneralaffairs@unitedtractors.com">admgeneralaffairs@unitedtractors.com</a></div>
                <div>Check your Debit Note Monitoring <a href="'.site_url().'tenant/'.$idTenant.'/1">'.site_url().'tenant/'.$idTenant.'/1</a></div>
                <div>Thank you for your kind attention and coorperation.</div>
                <br>
                <div>Sincerely,</div>
                <div>Building Management</div>
                <div><b>PT United Tractors Tbk</b></div>
            ';
            return $html;
        }

        public function htmlPaymentOverdue($param, $idTenant){
            $html = '
                <p>Attn: Mr/Mrs PT United Tractors Tbk</p>
                <p>Dear Sir/Madam,</p>
                <br>
                <p>Warmest greetings from United Tractors,</p>
                <br>
                <p>We would like to inform you that according to the payment schedule, the invoices listed below has been overdue. </p>
                <br>
                <table border="1" style="border-collapse: collapse;">
                    <tr>
                        <th style="width: 5%">No</th>
                        <th style="width: 17%">Invoice No</th>
                        <th style="width: 13%">Invoice Date</th>
                        <th style="width: 30%">Invoice Description</th>
                        <th style="width: 8%">Currency</th>
                        <th style="width: 14%">Amount</th>
                        <th style="width: 13%">Due Date</th>
                    </tr>
            ';

            $no = 1;
            foreach ($param as $item) {
                $dateInvoice = date_create($item->TGLFAKTUR_DEBITNOTE);
                $dateDueDate = date_create($item->TGLJATUH_DEBITNOTE);

                $html .= '
                    <tr>
                        <td style="text-align: center;">'.$no.'</td>
                        <td style="text-align: center;">'.$item->NOFAKTUR_DEBITNOTE.'</td>
                        <td style="text-align: center;">'.date_format($dateInvoice, 'j F Y').'</td>
                        <td> '.$item->BARANGJASA_DEBITNOTE.' </td>
                        <td style="text-align: center;">'.$item->MATAUANG.'</td>
                        <td style="text-align: center;">'.number_format($item->GRANDTOTAL_DEBITNOTE).'</td>
                        <td style="text-align: center;">'.date_format($dateDueDate, 'j F Y').'</td>
                    </tr>
                ';
                $no++;
            }
            $html .= '
                </table>
                <br>
                <p>Late payment from the due date will be subject to penalites based on applicable term and condition of the agrement.</p>
                <p>To avoid increasing late penalties, please make payments immeadiately directly to our account as below:</p>
                <table>
                    <tr>
                        <td>Bank Name</td>
                        <td>:</td>
                        <td>Bank Permata</td>
                    </tr>
                    <tr>
                        <td>Bank/Virtual Account Number</td>
                        <td>:</td>
                        <td>0200330021</td>
                    </tr>
                    <tr>
                        <td>Benefeciary/Account Name</td>
                        <td>:</td>
                        <td>PT. United Tractors Tbk</td>
                    </tr>
                </table>
                <br>
                <div>If the payment has already been made please disregard this reminder and kindly inform us the proof of the payment.</div>
                <div></div>
                <div>Shall you have any question or further information, please contact us at +62 21 24579999 ext. 16053 or by email to <a href="mailto:admgeneralaffairs@unitedtractors.com">admgeneralaffairs@unitedtractors.com</a></div>
                <div>Check your Debit Note Monitoring with this link <a href="'.site_url().'tenant/'.$idTenant.'/1">'.site_url().'tenant/'.$idTenant.'/1</a></div>
                <div>Thank you for your kind attention and coorperation.</div>
                <br>
                <div>Sincerely,</div>
                <div>Building Management</div>
                <div><b>PT United Tractors Tbk</b></div>
            ';
            return $html;
        }
    }
    
?>