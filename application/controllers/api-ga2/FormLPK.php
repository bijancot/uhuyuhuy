<?php defined('BASEPATH') or exit('No direct script access allowed');
// require APPPATH . '/libraries/REST_Controller.php';
use chriskacerguis\RestServer\RestController;

class FormLPK extends RestController
{
    function __construct()
    {
        parent::__construct();
        $this->load->helper("url");
        $this->load->library(array('upload', 'image_lib'));
        $this->load->library('pdfgenerator');
        $this->load->library('datefunction');
    }

    public function index_get()
    {
        $this->generate();
    }

    public function generate()
    {
        // $trans      = $this->db->get_where('V_TRANSACTION', ['ID_TRANS' => $param['idTrans']])->result();
        // $mapping    = $this->db->get_where('MAPPING', ['ID_MAPPING' => $trans[0]->ID_MAPPING])->result();
        // $user       = $this->db->get_where('USERS', ['ID_USERS' => $trans[0]->ID_USERS])->result();
        // $approvals  = $this->db->get_where('V_APPROVAL_SIGNATURE', ['ID_TRANS' => $param['idTrans']])->result();

        // $data['list']                   = $this->get(['table' => $mapping[0]->NAMA_TABEL, 'idTrans' => $param['idTrans']]);				
        // $data['title_pdf']              = "Lembar Pengesahan Kontrak";
        // $data['user']                   = $user[0];	
        // $data['approvals']              = $approvals;
        // $data['noDoc']                  = "FORM 017/PROS-MFP-MLK3-015";
        // $data['getMonth']               = $this->datefunction->getMonth();
        // $data['getMonthRomawi']         = $this->datefunction->getMonthRomawi();

        $namaUser = "Zidan Department Head";
        $file_pdf = $namaUser . "_LPK" . '_' . time();
        $path_pdf = 'uploads/project/lpk/' . $file_pdf . '.pdf';

        $paper = 'A4';
        $orientation = 'portrait';

        $html = $this->load->view("pdf_template/form_LPK", '', true);

        $resPdf = $this->pdfgenerator->generate($html, $file_pdf, $paper, $orientation);
        if (!is_dir('./uploads/project/lpk/')) {
            mkdir('./uploads/project/lpk/', 0777, TRUE);
        }
        file_put_contents($path_pdf, $resPdf);
        return $path_pdf;
    }
}
