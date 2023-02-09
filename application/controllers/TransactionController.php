<?php
defined('BASEPATH') or exit('No direct script access allowed');

class TransactionController extends CI_Controller
{
    function __construct(){
        parent::__construct();
        if(empty($this->session->userdata('ROLE_USERS')) || $this->session->userdata('ROLE_USERS') != 'Admin GA'){
            redirect('login');
        }
        $this->load->model('Transaction');
        $this->load->library('notification');
    }
    public function vTrans(){
        $datas['trans'] = $this->Transaction->getAll();

        $this->load->view('template/admin/header');
		$this->load->view('template/admin/sidebar');
		$this->load->view('template/admin/topbar');
		$this->load->view('admin/master_transaksi/transaksi', $datas);
		$this->load->view('template/admin/modal');
		$this->load->view('template/admin/footer');
    }
    public function ajxGetData(){
        $draw   = $_POST['draw'];
        $offset = $_POST['start'];
        $limit  = $_POST['length']; // Rows display per page
        $search = $_POST['search']['value'];
        
        $trans = $this->Transaction->getDataTable(['offset' => $offset, 'limit' => $limit, 'search' => $search]);
        $datas = array();
        $no = 1;
        foreach ($trans['records'] as $item) {
            $approvalBtn = '';
            if ($item->STAT_TRANS == '0') {
                $status = 'Belum Diverifikasi';
                $approvalBtn = '
                        <button type="button" data-toggle="modal" data-target="#mdlApprove" data-id="' . $item->ID_TRANS . '" class="btn btn-success btn-sm mx-1 rounded mdlApprove" data-tooltip="tooltip" data-placement="top" title="Menyetujui">
                            <i class="fa fa-check"></i>
                        </button>
                        <button type="button" data-toggle="modal" data-target="#mdlReject" data-id="' . $item->ID_TRANS . '" class="btn btn-danger btn-sm rounded mdlReject" data-tooltip="tooltip" data-placement="top" title="Tolak">
                            <i class="fa fa-times"></i>
                        </button>
                    ';
            } else if ($item->STAT_TRANS == '1') {
                $status = 'Terverifikasi';
            } else if ($item->STAT_TRANS == '2') {
                $status = 'Selesai';
            } else if ($item->STAT_TRANS == '3') {
                $status = 'Ditolak';
            } else if($item->STAT_TRANS == '4'){
                $status = 'Menunggu Feedback';
            }
            $date = date_create($item->TS_TRANS);

            $datas[] = array( 
                "no"        => $no,
                "namaUser"  => $item->NAMA_USERS,
                "namaForm"  => $item->NAMA_FORM,
                "tgl"       => date_format($date, 'j M Y H:i:s'),
                "ket"       => $item->KETERANGAN_TRANS,
                "flag"      => $item->FLAG_TRANS,
                "status"    => $status,
                "aksi"      => '
                    <div class="btn-group" role="group">
                        <button type="button" data-toggle="modal" data-target="#mdlView" data-src="' . $item->PATH_TRANS . '" class="btn btn-primary btn-sm rounded mdlView" data-tooltip="tooltip" data-placement="top" title="Detail">
                            <i class="fa fa-eye"></i>
                        </button>
                        ' . $approvalBtn . '
                    </div>
                '
            );
            $no++;
        }

        $response = array(
            "draw" => intval($draw),
            "recordsTotal" => $trans['totalRecords'],
            "recordsFiltered" => ($search != "" ? $trans['totalDisplayRecords'] : $trans['totalRecords']),
            "aaData" => $datas
        );

        echo json_encode($response);
    }

    public function store(){
    }
    public function update(){
    }
    public function destroy(){
    }

    public function approve(){
        $param                  = $_POST;
        $param['STAT_TRANS']    = '1';
        $this->Transaction->update($param);

        $transaction        = $this->Transaction->get(['filter' => ['ID_TRANS' => $param['ID_TRANS']]]);
        $notif['title']     = 'Pengajuan Baru';
        $notif['message']   = 'Terdapat Pengajuan Form '.$transaction[0]->NAMA_FORM;
        $notif['regisIds']  = $this->db->get_where('USERS', ['ROLE_USERS' => $transaction[0]->CONFIRM_STATE_TRANS])->result_array();
        $this->notification->push($notif);
        
        $notif['title']     = 'Info Pengajuan Form';
        $notif['message']   = 'Pengajuan Form '.$transaction[0]->NAMA_FORM.' Telah Diverifikasi';
        $notif['regisIds']  = $this->db->get_where('USERS', ['ID_USERS' => $transaction[0]->ID_USERS])->result_array();
        $this->notification->push($notif);
        redirect('transaction');
    }
    public function reject(){
        $param                  = $_POST;
        $param['STAT_TRANS']    = '3';
        $this->Transaction->update($param);

        $transaction        = $this->Transaction->get(['filter' => ['ID_TRANS' => $param['ID_TRANS']]]);
        $notif['title']     = 'Info Pengajuan Form';
        $notif['message']   = 'Pengajuan Form '.$transaction[0]->NAMA_FORM.' Ditolak';
        $notif['regisIds']  = $this->db->get_where('USERS', ['ID_USERS' => $transaction[0]->ID_USERS])->result_array();
        $this->notification->push($notif);
        redirect('transaction');
    }
}