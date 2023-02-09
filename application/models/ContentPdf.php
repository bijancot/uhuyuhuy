<?php 
defined('BASEPATH') or exit('No direct script access allowed');

class ContentPdf extends CI_Model{
    function __construct(){
        parent::__construct();
        $this->load->library('pdfgenerator');
        $this->load->library('datefunction');
    }

    public function generate($param){
        $trans      = $this->db->get_where('V_TRANSACTION', ['ID_TRANS' => $param['idTrans']])->result();
        $mapping    = $this->db->get_where('MAPPING', ['ID_MAPPING' => $trans[0]->ID_MAPPING])->result();
        $user       = $this->db->get_where('USERS', ['ID_USERS' => $trans[0]->ID_USERS])->result();
        $approvals  = $this->db->get_where('V_APPROVAL_SIGNATURE', ['ID_TRANS' => $param['idTrans']])->result();
        
        $data['list']                   = $this->get(['table' => $mapping[0]->NAMA_TABEL, 'idTrans' => $param['idTrans']]);				
        $data['title_pdf']              = $mapping[0]->NAMA_FORM;
        $data['user']                   = $user[0];	
        $data['approvals']              = $approvals;
        $data['noDoc']                  = $mapping[0]->NO_DOC;
        $data['getMonth']               = $this->datefunction->getMonth();
        $data['getMonthRomawi']         = $this->datefunction->getMonthRomawi();
		       
        $file_pdf = $trans[0]->NAMA_USERS.'_'.$mapping[0]->NAMA_FORM.'_'.time();
        $path_pdf = 'uploads/transaction/'.$user[0]->USER_USERS.'/'.$file_pdf.'.pdf';
		
        $paper = 'A4';
        $orientation = $param['orientation'];
        
		$html = $this->load->view($mapping[0]->PATH_TEMPLATE_PDF, $data, true);	    

        $resPdf = $this->pdfgenerator->generate($html, $file_pdf,$paper,$orientation);
        if(!is_dir('./uploads/transaction/'.$user[0]->USER_USERS)){
            mkdir('./uploads/transaction/'.$user[0]->USER_USERS, 0777, TRUE);
        }
        file_put_contents($path_pdf, $resPdf);
        return $path_pdf;
    }

    public function get($param){
        $detailForms = $this->db->get_where('V_'.$param['table'].'_DETAIL', ['ID_TRANS' => $param['idTrans']])->result();
        return $detailForms;
    }
}