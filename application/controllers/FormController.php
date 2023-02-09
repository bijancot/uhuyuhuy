<?php
defined('BASEPATH') or exit('No direct script access allowed');

class FormController extends CI_Controller
{
    function __construct(){
        parent::__construct();
        if(empty($this->session->userdata('ROLE_USERS')) || $this->session->userdata('ROLE_USERS') != 'Admin GA'){
            redirect('login');
        }
        $this->load->model('Form');
    }
    public function vForm(){
        $datas['tables'] = $this->Form->getTables();
        $datas['forms'] = $this->Form->getAll();

        $this->load->view('template/admin/header');
		$this->load->view('template/admin/sidebar');
		$this->load->view('template/admin/topbar');
		$this->load->view('admin/master_form/form', $datas);
		$this->load->view('template/admin/modal');
		$this->load->view('template/admin/footer');
    }

    public function vFormEdit($id){
        $datas['tables'] = $this->Form->getTables();
        $datas['form'] = $this->Form->get(['filter' => ['ID_MAPPING' => $id]]);

        $this->load->view('template/admin/header');
		$this->load->view('template/admin/sidebar');
		$this->load->view('template/admin/topbar');
		$this->load->view('admin/master_form/form_edit', $datas);
		$this->load->view('template/admin/modal');
		$this->load->view('template/admin/footer');
    }

    public function ajxGetData(){
        $draw   = $_POST['draw'];
        $offset = $_POST['start'];
        $limit  = $_POST['length']; // Rows display per page
        $search = $_POST['search']['value'];
        
        $form = $this->Form->getDataTable(['offset' => $offset, 'limit' => $limit, 'search' => $search]);
        $datas = array();
        foreach ($form['records'] as $item) {
            $datas[] = array( 
                "namaTable" => $item->NAMA_TABEL,
                "noDoc"     => $item->NO_DOC,
                "namaForm"  => $item->NAMA_FORM,
                "sectForm"  => $item->SECTION_FORM,
                "aksi"      => '
                    <div class="btn-group" role="group">
                        <a href="' . site_url('form/edit/' . $item->ID_MAPPING) . '" class="btn btn-primary btn-sm rounded" data-tooltip="tooltip" data-placement="top" title="Ubah">
                            <i class="fa fa-edit"></i>
                        </a>
                        <!-- Setting flow -->
                        <a href="' . site_url('form/flow/' . $item->ID_MAPPING) . '" class="btn btn-info btn-sm rounded mx-1" data-tooltip="tooltip" data-placement="top" title="Pengaturan">
                            <i class="fa fa-cog"></i>
                        </a>
                        <button type="button" data-toggle="modal" data-id="' . $item->ID_MAPPING . '" data-name="' . $item->NAMA_FORM . '" data-target="#mdlDelete" class="btn btn-danger btn-sm rounded mdlDelete" data-tooltip="tooltip" data-placement="top" title="Hapus">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                '
            );
        }

        $response = array(
            "draw" => intval($draw),
            "recordsTotal" => $form['totalRecords'],
            "recordsFiltered" => ($search != "" ? $form['totalDisplayRecords'] : $form['totalRecords']),
            "aaData" => $datas
        );

        echo json_encode($response);
    }

    public function store(){        
        $datas = $_POST;
        $datas['ID_MAPPING']             = 'MAPP_'.substr(md5(time()), 0, 45);
        $datas['PATH_TEMPLATE_PDF']      = 'pdf_template/'.$datas['PATH_TEMPLATE_PDF'];

        $retId = $this->Form->insert($datas);
        redirect('form');
    }
    public function update(){
        $datas = $_POST;
        
        $this->Form->update($datas);
        redirect('form');
    }
    public function destroy(){
        $datas = $_POST;
        $this->Form->delete($datas);
        redirect('form');
    }

    public function vFlow($id){
        $datas['flows'] = $this->Form->getFlow(['filter' => ['ID_MAPPING' => $id]]);

        $this->load->view('template/admin/header');
		$this->load->view('template/admin/sidebar');
		$this->load->view('template/admin/topbar');
		$this->load->view('admin/master_form/list_approval', $datas);
		$this->load->view('template/admin/modal');
		$this->load->view('template/admin/footer');
    }

    public function updateFlow(){
        $datas = $_POST;
        
        $this->Form->updateFlow($datas);
        redirect('form/flow/'.$datas['ID_MAPPING']);
    }

    public function deleteFlow(){
        $datas = $_POST;
        
        $this->Form->deleteFlow($datas);
        redirect('form/flow/'.$datas['ID_MAPPING']);
    }

    public function editFlow(){
        $datas = $_POST;
        
        $this->Form->editFlow($datas);
        redirect('form/flow/'.$datas['ID_MAPPING']);
    }
}