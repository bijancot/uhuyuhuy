<?php
defined('BASEPATH') or exit('No direct script access allowed');

class DashboardController extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();
		if (empty($this->session->userdata('ROLE_USERS')) || $this->session->userdata('ROLE_USERS') != 'Admin GA') {
			redirect('login');
		};
		$this->load->model('Form');
	}
	public function vDashboard()
	{
		$data['jumlahForm']	= $this->Form->getJmlForm();
		$data['jumlahUser']	= $this->Form->getJmlUser();
		$data['transDone']	= $this->Form->getTransDone();
		$data['transNot']	= $this->Form->getTransNot();

		$this->load->view('template/admin/header');
		$this->load->view('template/admin/sidebar');
		$this->load->view('template/admin/topbar');
		$this->load->view('admin/index', $data);
		$this->load->view('template/admin/modal');
		$this->load->view('template/admin/berandafooter', $data);
		$this->load->view('template/admin/footer');
	}
}
