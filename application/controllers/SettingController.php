<?php
defined('BASEPATH') or exit('No direct script access allowed');

class SettingController extends CI_Controller
{
    public function vSetting(){
        $this->load->view('template/admin/header');
		$this->load->view('template/admin/sidebar');
		$this->load->view('template/admin/topbar');
		$this->load->view('admin/setting');
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