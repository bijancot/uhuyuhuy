<?php
defined('BASEPATH') or exit('No direct script access allowed');

class WeekController extends CI_Controller
{
    public function __construct(){
        parent::__construct();
        if(empty($this->session->userdata('ROLE_USERS')) || $this->session->userdata('ROLE_USERS') != 'Admin GA'){
            redirect('login');
        }
    }
    public function vWeek(){
        $this->load->view('template/admin/header');
		$this->load->view('template/admin/sidebar');
		$this->load->view('template/admin/topbar');
		$this->load->view('admin/week');
		$this->load->view('template/admin/modal');
		$this->load->view('template/admin/footer');
    }

    public function store(){

    }
    public function update(){

    }
    public function destroy(){

    }
}