<?php defined('BASEPATH') OR exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;

class DebitNoteApi extends RestController {
    public function __construct() {
        parent::__construct();
        $this->load->helper("url");
        $this->load->model("DebitNote");
        $this->load->library(array('notification', 'pdfgenerator'));
    }
    public function index_get($username){
        $param = $this->get();
        if(!empty($param['limit'])){
            $user = $this->db->get_where('USERS', ['USER_USERS' => $username])->result();
            if($user != null){
                $this->db->order_by('TS_APP', 'DESC');
                $limit = $param['limit'] != '-1'? $param['limit'] : '';
                
                $debitnote = $this->db->not_like('STAT_DEBITNOTE', '0')->get_where('V_DEBITNOTE_APPROVAL_GET', ['ROLE_APP' => $user[0]->ROLE_USERS], $limit)->result();
                $x = 0;
                if($debitnote != null){
                    foreach ($debitnote as $item) {
                        if(($user[0]->ROLE_USERS == 'Department Head' && $item->STAT_DEBITNOTE != '3') || $item->ISAPPROVE_APP != NULL){
                            $debitnoteNew[$x]['ID_DEBITNOTE']           = $item->ID_DEBITNOTE;
                            $debitnoteNew[$x]['EMAIL_DEBITNOTE']        = $item->EMAIL_DEBITNOTE;
                            $debitnoteNew[$x]['PATH_DEBITNOTE']         = $item->PATH_DEBITNOTE;
                            $debitnoteNew[$x]['TS_APP']                 = $item->TS_APP;
                            $debitnoteNew[$x]['STAT_DEBITNOTE']         = $item->STAT_DEBITNOTE;                            
                            $debitnoteNew[$x]['ISAPPROVE_APP']          = $item->ISAPPROVE_APP;
                            $x++;
                        }
                    }
                    $debitnote = $debitnoteNew;
                }
                

                if($debitnote != null){
                    $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $debitnote], 200);
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
        if(!empty($param['idDebitnote']) && !empty($param['username'])){
            $debitnote  = $this->db->get_where('DEBITNOTE', ['ID_DEBITNOTE' => $param['idDebitnote']])->result();
            $user       = $this->db->get_where('USERS', ['USER_USERS' => $param['username']])->result();
            if($debitnote != null && $user != null){
                
                $debitnoteDetail = $this->db->get_where('V_DEBITNOTE_APPROVAL_DETAIL', ['ID_DEBITNOTE' => $param['idDebitnote'], 'ROLE_APP' => $user[0]->ROLE_USERS])->result();
                
                if($debitnoteDetail != null){
                    $this->response(['status' => true, 'message' => 'Data berhasil ditemukan', 'data' => $debitnoteDetail[0]], 200);
                }else{
                    $this->response(['status' => false, 'message' => 'Data tidak ditemukan'], 200);
                }
            }else{
                $this->response(['status' => false, 'message' => 'Data Debitnote / user tidak ditemukan'], 200);
            }
        }else{
            $this->response(['status' => false, 'message' => 'Parameter tidak cocok'], 200);
        }
    }

    public function confirm_put(){
        $param = $this->put();
        if(!empty($param['username']) && !empty($param['idDebitnote']) && !empty($param['isApprove'])){
            $user         = $this->db->get_where('USERS', ['USER_USERS' => $param['username']])->result();
            $debitnote    = $this->db->get_where('DEBITNOTE', ['ID_DEBITNOTE' => $param['idDebitnote']])->result();
            $debitapp    = $this->db->get_where('DEBITNOTE_APPROVAL', ['ID_DEBITNOTE' => $param['idDebitnote']])->result();
            if($user != null && $debitnote != null){
                if($debitnote[0]->STAT_DEBITNOTE == '1' &&  $debitapp[0]->ROLE_APP == $user[0]->ROLE_USERS){
                    if($param['isApprove'] == "1"){                        
                       
                        $this->db->query('UPDATE DEBITNOTE SET STAT_DEBITNOTE = "2" WHERE ID_DEBITNOTE = "'.$param['idDebitnote'].'"');
                        $this->db->where(['ID_DEBITNOTE' => $param['idDebitnote'], 'ROLE_APP' => $user[0]->ROLE_USERS])->update('DEBITNOTE_APPROVAL', ['ID_USERS' => $user[0]->ID_USERS, 'ISAPPROVE_APP' => '1', 'TSUPDATE_APP' => date('Y-m-d H:i:s')]);
                                                
                        $datas['ID_DEBITNOTE'] = $param['idDebitnote'];
                        $this->DebitNote->generate($datas);
                        $this->pusherjs->pushDebitnote();
                        $this->response(['status' => true, 'message' => 'Data berhasil disetujui'], 200);

                    }else if($param['isApprove'] == "2"){

                        $this->db->where('ID_DEBITNOTE', $param['idDebitnote'])->update('DEBITNOTE', ['STAT_DEBITNOTE' => '3']);
                        $this->db->where(['ID_DEBITNOTE' => $param['idDebitnote'], 'ROLE_APP' => $user[0]->ROLE_USERS])->update('DEBITNOTE_APPROVAL', ['ID_USERS' => $user[0]->ID_USERS, 'ISAPPROVE_APP' => '0', 'TSUPDATE_APP' => date('Y-m-d H:i:s')]);
                        
                        $this->pusherjs->pushDebitnote();
                        $this->response(['status' => true, 'message' => 'Data berhasil ditolak'], 200);
                    }
                }else{
                    $this->response(['status' => false, 'message' => 'Anda tidak diijinkan untuk melakukan aksi ini'], 200);
                }
            }else{
                $this->response(['status' => false, 'message' => 'Data user atau Debitnote tidak ditemukan'], 200);
            }
        }else{
            $this->response(['status' => false, 'message' => 'Parameter tidak cocok'], 200);
        }
    }
}
