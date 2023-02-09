<?php
class Generatepdf {
    public function generate($param){
        $this->load->library('pdfgenerator');
        $this->load->model('Snack');

        $data['listSnack'] = $this->Snack->getData(['filter' => ['DETAIL_SNACK.ID_SNACK' => $param['idTrans']]]);				
        $data['title_pdf'] = 'PEMBELIAN SNACK';	
		$username = 'bambang'; 
		       
        $file_pdf = "$username".'_PEMBELIAN SNACK_'.date('m-d-Y');
		
        $paper = 'A4';
        $orientation = "portrait";
        
		$html = $this->load->view('pdf_template/form_snack',$data, true);	    
        
        $this->pdfgenerator->generate($html, $file_pdf,$paper,$orientation);
    }
}
