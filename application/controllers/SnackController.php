<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class SnackController extends CI_Controller {
	function __construct(){
        parent::__construct();
        $this->load->model('Snack');
    }
    public function vSnack($id)
    {
		$data['listSnack'] = $this->Snack->getData(['filter' => ['DETAIL_SNACK.ID_SNACK' => $id]]);				
        $data['title_pdf'] = 'PEMBELIAN SNACK';	
		$username = 'bambang';
		
        $this->load->library('pdfgenerator');        
		       
        $file_pdf = "$username".'_PEMBELIAN SNACK_'.date('m-d-Y');
		
        $paper = 'A4';
        $orientation = "portrait";
        
		$html = $this->load->view('pdf_template/form_snack',$data, true);	    
        
        $this->pdfgenerator->generate($html, $file_pdf,$paper,$orientation);
    }
}