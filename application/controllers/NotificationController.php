<?php
defined('BASEPATH') or exit('No direct script access allowed');

class NotificationController extends CI_Controller
{
    function __construct(){
        parent::__construct();
        $this->load->model('User');
        $this->load->library(array('upload', 'notification'));
    }
    public function getTransactionAll(){
        $datas['notifs']         = $this->db->order_by('TS_TRANS', 'desc')->get_where('V_TRANSACTION', ['IS_SEEN' => '0'], '5')->result();
        $datas['notifCount']     = $this->db->get_where('V_TRANSACTION', ['IS_SEEN' => '0'])->num_rows();
        
		$this->load->view('template/admin/notif', $datas);
	}
    public function readTransaction($id){
        $this->db->where('ID_TRANS', $id)->update('TRANSACTION', ['IS_SEEN' => '1']);
        redirect('transaction');
    }
    public function readTransactionAll(){
        $this->db->update('TRANSACTION', ['IS_SEEN' => '1']);
        redirect('transaction');
    }
    public function ajxReadTransactionAll(){
        $this->db->update('TRANSACTION', ['IS_SEEN' => '1']);
        echo json_encode(true);
    }

    public function getDebitnoteAll(){
        $datas['notifs']         = $this->db->where_in('STAT_DEBITNOTE', ['2','3'])->order_by('TSUPDATE_APP', 'desc')->get_where('V_DEBITNOTE_APPROVAL_GET', ['IS_SEEN' => '0'], '5')->result();
        $datas['notifCount']     = $this->db->where_in('STAT_DEBITNOTE', ['2','3'])->get_where('V_DEBITNOTE_APPROVAL_GET', ['IS_SEEN' => '0'])->num_rows();
        
		$this->load->view('template/admin_dn/notif', $datas);
	}
    public function readDebitnote($id){
        $this->db->where('ID_TRANS', $id)->update('DEBITNOTE', ['IS_SEEN' => '1']);
        redirect('transaction');
    }
    public function readDebitnoteAll($status){
        $this->db->where(['STAT_DEBITNOTE' => $status])->update('DEBITNOTE', ['IS_SEEN' => '1']);

        if($status == '2'){
            redirect('debitnote/approved');
        }else if($status == '3'){
            redirect('debitnote/rejected');
        }
    }
    public function ajxReadDebitnoteAll(){
        $datas = $_POST;
        $this->db->where(['STAT_DEBITNOTE' => $datas['STAT_DEBITNOTE']])->update('DEBITNOTE', ['IS_SEEN' => '1']);
        echo json_encode(true);
    }
}