<?php defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;

class Transaction extends RestController {
    public function __construct() {
        parent::__construct();
        $this->load->helper("url");
        $this->load->model("ContentPdf");
        $this->load->library(array('notification', 'pdfgenerator'));
    }
    public function index_get($username){
        $param = $this->get();
        if(!empty($param['limit']) && $param['isApproval'] != ''){
            $user = $this->db->get_where('USERS', ['USER_USERS' => $username])->result();
            if($user != null){
                $this->db->order_by('TS_TRANS', 'DESC');
                $limit = $param['limit'] != '-1'? $param['limit'] : '';
                if($param['isApproval'] == "false"){
                    $trans = $this->db->get_where('V_TRANSACTION', ['ID_USERS' => $user[0]->ID_USERS], $limit)->result();
                }else{
                    $limitQuery = $limit != '' ? 'LIMIT '.$limit : ''; 
                    $trans      = $this->db->query("
                        SELECT * FROM V_TRANSACTION_APPROVAL 
                        WHERE ROLE_APP = '".$user[0]->ROLE_USERS."' AND 
                        (CONFIRM_STATE_TRANS = '".$user[0]->ROLE_USERS."' OR 
                        ISAPPROVE_APP IS NOT NULL) AND STAT_TRANS NOT IN('0', '3') ORDER BY TS_TRANS DESC ".$limitQuery."")->result();
                    
                    $x = 0;
                    if($trans != null){
                        foreach ($trans as $item) {
                            $transNew[$x]['ID_TRANS']               = $item->ID_TRANS;
                            $transNew[$x]['ID_MAPPING']             = $item->ID_MAPPING;
                            $transNew[$x]['ID_USERS']               = $item->ID_USERS;
                            $transNew[$x]['NAMA_USERS']             = $item->NAMA_USERS;
                            $transNew[$x]['NAMA_FORM']              = $item->NAMA_FORM;
                            $transNew[$x]['PATH_TRANS']             = $item->PATH_TRANS;
                            $transNew[$x]['TS_TRANS']               = $item->TS_TRANS;
                            $transNew[$x]['FLAG_TRANS']             = $item->FLAG_TRANS;
                            $transNew[$x]['CONFIRM_STATE_TRANS']    = $item->CONFIRM_STATE_TRANS;
                            $transNew[$x]['STAT_TRANS']             = $item->ISAPPROVE_APP;
                            $x++;
                        }
                        $trans = $transNew;
                    }
                }

                if($trans != null){
                    $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $trans], 200);
                }else{
                    $this->response(['status' => false, 'message' => 'Data tidak ditemukan'], 200);
                }
            }else{
                $this->response(['status' => false, 'message' => 'Data user tidak ditemukan'], 200);
            }
        }else{
            $this->response(['status' => false, 'message' => 'Parameter tidak cocok'], 200);
        }
    }
    public function detail_get(){
        $param = $this->get();
        if(!empty($param['idTrans']) && !empty($param['username'] && !empty($param['isApproval']))){
            $trans  = $this->db->get_where('TRANSACTION', ['ID_TRANS' => $param['idTrans']])->result();
            $user   = $this->db->get_where('USERS', ['USER_USERS' => $param['username']])->result();
            if($trans != null && $user != null){
                if($param['isApproval'] == 'true'){
                    $transDetail = $this->db->get_where('V_TRANSACTION_APPROVAL_DETAIL', ['ID_TRANS' => $param['idTrans'], 'ROLE_APP' => $user[0]->ROLE_USERS])->result();
                }else{
                    $transDetail = $this->db->get_where('V_TRANSACTION_DETAIL', ['ID_TRANS' => $param['idTrans'], 'ID_USERS' => $user[0]->ID_USERS])->result();
                }
                if($transDetail != null){
                    $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $transDetail[0]], 200);
                }else{
                    $this->response(['status' => false, 'message' => 'Data tidak ditemukan'], 200);
                }
            }else{
                $this->response(['status' => false, 'message' => 'Data transaction / user tidak ditemukan'], 200);
            }
        }else{
            $this->response(['status' => false, 'message' => 'Parameter tidak cocok'], 200);
        }
    }
    public function confirm_put(){
        $param = $this->put();
        if(!empty($param['username']) && !empty($param['idTrans']) && !empty($param['isApprove'])){
            $user           = $this->db->get_where('USERS', ['USER_USERS' => $param['username']])->result();
            $transaction    = $this->db->get_where('V_TRANSACTION', ['ID_TRANS' => $param['idTrans']])->result();
            if($user != null && $transaction != null){
                if($transaction[0]->STAT_TRANS == '1' && $transaction[0]->CONFIRM_STATE_TRANS == $user[0]->ROLE_USERS){
                    if($param['isApprove'] == "1"){
                        $flow               = $this->db->get_where('FLOW', ['ID_MAPPING' => $transaction[0]->ID_MAPPING])->result_array();
                        $flowWillApprove    = $transaction[0]->FLAG_TRANS + 2; 
                        if(!empty($flow[0]['APP_'.$flowWillApprove]) && $flow[0]['APP_'.$flowWillApprove] != null){ // check if next approval
                            $this->db->where(['ID_TRANS' => $param['idTrans'], 'ROLE_APP' => $user[0]->ROLE_USERS])->update('DETAIL_APPROVAL', ['ID_USERS' => $user[0]->ID_USERS, 'ISAPPROVE_APP' => '1', 'KETERANGAN' => $param['keterangan']]);
                            
                            $orientation = 'portrait';
                            if($transaction[0]->NAMA_FORM == 'Identifikasi' || $transaction[0]->NAMA_FORM == 'Permintaan Extension dan Akses'){
                                $orientation = 'landscape';
                            }
                            $resLinkGenerated = $this->ContentPdf->generate(['idTrans' => $param['idTrans'], 'orientation' => $orientation]);
                            if(!file_exists($resLinkGenerated)){ // check if pdf generated is unsuccessfull created
                                $this->db->where(['ID_TRANS' => $param['idTrans'], 'ROLE_APP' => $user[0]->ROLE_USERS])->update('DETAIL_APPROVAL', ['ID_USERS' => null, 'ISAPPROVE_APP' => null, 'KETERANGAN' => null]);
                                $this->response(['status' => false, 'message' => 'Gagal generate form'], 200);
                            }
                            $this->db->where('ID_TRANS', $param['idTrans'])->update('TRANSACTION', ['PATH_TRANS' => base_url($resLinkGenerated)]);
                            
                            $this->db->where(['ID_TRANS' => $param['idTrans'], 'ROLE_APP' => $user[0]->ROLE_USERS])->update('DETAIL_APPROVAL', ['ID_USERS' => $user[0]->ID_USERS, 'ISAPPROVE_APP' => '1', 'KETERANGAN' => $param['keterangan']]);
                            $this->db->query('UPDATE TRANSACTION SET FLAG_TRANS = FLAG_TRANS+1 WHERE ID_TRANS = "'.$param['idTrans'].'"');
                            // notif to next user approval
                            $userReceiveNotifs = $this->db->get_where('USERS', ['ROLE_USERS' => $flow[0]['APP_'.$flowWillApprove]])->result_array();
                            $notif['title']     = 'Pengajuan Baru';
                            $notif['message']   = 'Terdapat Pengajuan Form '.$transaction[0]->NAMA_FORM;
                            $notif['regisIds']  = $userReceiveNotifs;
                            $this->notification->push($notif);
                        }else{ // check if last approval
                            $this->db->where(['ID_TRANS' => $param['idTrans'], 'ROLE_APP' => $user[0]->ROLE_USERS])->update('DETAIL_APPROVAL', ['ID_USERS' => $user[0]->ID_USERS, 'ISAPPROVE_APP' => '1', 'KETERANGAN' => $param['keterangan']]);

                            $orientation = 'portrait';
                            if($transaction[0]->NAMA_FORM == 'Identifikasi' || $transaction[0]->NAMA_FORM == 'Permintaan Extension dan Akses'){
                                $orientation = 'landscape';
                            }
                            $resLinkGenerated = $this->ContentPdf->generate(['idTrans' => $param['idTrans'], 'orientation' => $orientation]);
                            if(!file_exists($resLinkGenerated)){ // check if pdf generated is unsuccessfull created
                                $this->db->where(['ID_TRANS' => $param['idTrans'], 'ROLE_APP' => $user[0]->ROLE_USERS])->update('DETAIL_APPROVAL', ['ID_USERS' => null, 'ISAPPROVE_APP' => null, 'KETERANGAN' => null]);
                                $this->response(['status' => false, 'message' => 'Gagal generate form'], 200);
                            }
                            $this->db->where('ID_TRANS', $param['idTrans'])->update('TRANSACTION', ['PATH_TRANS' => base_url($resLinkGenerated)]);
                            
                            // if form iwo then feedback for user
                            if($transaction[0]->NAMA_FORM == 'Internal Work Order'){ 
                                $this->db->where(['ID_TRANS' => $param['idTrans'], 'ROLE_APP' => $user[0]->ROLE_USERS])->update('DETAIL_APPROVAL', ['ID_USERS' => $user[0]->ID_USERS, 'ISAPPROVE_APP' => '1', 'KETERANGAN' => $param['keterangan']]);
                                $this->db->query('UPDATE TRANSACTION SET FLAG_TRANS = FLAG_TRANS+1, STAT_TRANS = "4" WHERE ID_TRANS = "'.$param['idTrans'].'"');
                                
                                // notif applicant successfull and request feedback
                                $userReceiveNotifs = $this->db->get_where('USERS', ['ID_USERS' => $transaction[0]->ID_USERS])->result_array();
                                $notif['title']     = 'Info Pengajuan Form';
                                $notif['message']   = 'Pengajuan Form '.$transaction[0]->NAMA_FORM.' Telah Disetujui & Isikan Feedback Anda';
                                $notif['regisIds']  = $userReceiveNotifs;
                                $this->notification->push($notif);

                                $this->response(['status' => true, 'message' => 'Data berhasil disetujui'], 200);
                            } 

                            $this->db->where(['ID_TRANS' => $param['idTrans'], 'ROLE_APP' => $user[0]->ROLE_USERS])->update('DETAIL_APPROVAL', ['ID_USERS' => $user[0]->ID_USERS, 'ISAPPROVE_APP' => '1', 'KETERANGAN' => $param['keterangan']]);
                            $this->db->query('UPDATE TRANSACTION SET FLAG_TRANS = FLAG_TRANS+1, STAT_TRANS = "2" WHERE ID_TRANS = "'.$param['idTrans'].'"');
                            // notif applicant successfull
                            $userReceiveNotifs = $this->db->get_where('USERS', ['ID_USERS' => $transaction[0]->ID_USERS])->result_array();
                            $notif['title']     = 'Info Pengajuan Form';
                            $notif['message']   = 'Pengajuan Form '.$transaction[0]->NAMA_FORM.' Telah Disetujui';
                            $notif['regisIds']  = $userReceiveNotifs;
                            $this->notification->push($notif);
                        }
                        // unlink(str_replace(base_url().'/', "", $transaction[0]->PATH_TRANS));
                        $this->response(['status' => true, 'message' => 'Data berhasil disetujui'], 200);
                    }else if($param['isApprove'] == "2"){ // check if approval is rejected
                        $this->db->where('ID_TRANS', $param['idTrans'])->update('TRANSACTION', ['STAT_TRANS' => '3']);
                        $this->db->query('UPDATE TRANSACTION SET FLAG_TRANS = FLAG_TRANS+1, STAT_TRANS = "3" WHERE ID_TRANS = "'.$param['idTrans'].'"');
                        $this->db->where(['ID_TRANS' => $param['idTrans'], 'ROLE_APP' => $user[0]->ROLE_USERS])->update('DETAIL_APPROVAL', ['ID_USERS' => $user[0]->ID_USERS, 'ISAPPROVE_APP' => '0']);
                        $this->db->where('ID_TRANS', $param['idTrans'])->update('TRANSACTION', ['KETERANGAN_TRANS' => $param['keterangan']]);

                        $userReceiveNotifs = $this->db->get_where('USERS', ['ID_USERS' => $transaction[0]->ID_USERS])->result_array();
                        $notif['title']     = 'Info Pengajuan Form';
                        $notif['message']   = 'Pengajuan Form '.$transaction[0]->NAMA_FORM.' Ditolak';
                        $notif['regisIds']  = $userReceiveNotifs;
                        $this->notification->push($notif);
                        
                        $this->response(['status' => true, 'message' => 'Data berhasil ditolak'], 200);
                    }
                }else{
                    $this->response(['status' => false, 'message' => 'Anda tidak diijinkan untuk melakukan aksi ini'], 200);
                }
            }else{
                $this->response(['status' => false, 'message' => 'Data user atau transaksi tidak ditemukan'], 200);
            }
        }else{
            $this->response(['status' => false, 'message' => 'Parameter tidak cocok'], 200);
        }
    }

    public function feedbackIWO_put(){
        $param = $this->put();

        if(!empty($param['idTrans']) && !empty($param['statPekerjaan']) && !empty($param['kualPekerjaan']) && !empty($param['ketPekerjaan'])){
            $updateIWO['ID_TRANS']              = $param['idTrans'];
            $updateIWO['STATUS_PEKERJAAN']      = $param['statPekerjaan'];
            $updateIWO['KUALITAS_PEKERJAAN']    = $param['kualPekerjaan'];
            $updateIWO['KET_PEKERJAAN']         = $param['ketPekerjaan'];
            $this->db->where('ID_TRANS', $updateIWO['ID_TRANS'])->update('FORM_IWO', $updateIWO);
            $resLinkGenerated = $this->ContentPdf->generate(['idTrans' => $param['idTrans'], 'orientation' => 'portrait']);
            $this->db->where('ID_TRANS', $param['idTrans'])->update('TRANSACTION', ['PATH_TRANS' => base_url($resLinkGenerated), 'STAT_TRANS' => "2"]);

            $this->response(['status' => true, 'message' => 'Berhasil memasukkan feedback pada form IWO'], 200);
        }else{
            $this->response(['status' => false, 'message' => 'Parameter tidak cocok'], 200);
        }
    }
}
