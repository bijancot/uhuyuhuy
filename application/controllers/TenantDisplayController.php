<?php
    class TenantDisplayController extends CI_Controller{
        public function __construct(){
            parent::__construct();
            $this->load->model('TenantDisplay');
        }

        public function display($id = null, $stat = null){
            if($id != null && ($stat == '1' || $stat == '2')){
                $datas['company']       = $this->TenantDisplay->get(['ID_TENANT' => $id]);          
                $datas['debitnotes']    = $this->TenantDisplay->getAll(['ID_TENANT' => $id], $stat == '1' ? ['4', '5'] : ['6']);
                $datas['stat']          = $stat;
                $this->load->view('template/tenant/header');
                $this->load->view('tenant/displayfortenant', $datas);
            }
        }
    }
?>