<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Welcome extends CI_Controller
{

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/userguide3/general/urls.html
	 */
	public function index()
	{
		$this->load->view('welcome_message');
	}

	public function landing()
	{
		$this->load->view('template/header');
		$this->load->view('landing');
		$this->load->view('template/footer');
	}

	public function register()
	{
		$this->load->view('template/header');
		$this->load->view('register');
		$this->load->view('template/footer');
	}

	public function login()
	{
		$this->load->view('template/header');
		$this->load->view('login');
		$this->load->view('template/footer');
	}

	public function admin_index()
	{
		$this->load->view('template/admin/header');
		$this->load->view('template/admin/sidebar');
		$this->load->view('template/admin/topbar');
		$this->load->view('admin/index');
		$this->load->view('template/admin/modal');
		$this->load->view('template/admin/footer');
	}

	public function admin_setting()
	{
		$this->load->view('template/admin/header');
		$this->load->view('template/admin/sidebar');
		$this->load->view('template/admin/topbar');
		$this->load->view('admin/setting');
		$this->load->view('template/admin/modal');
		$this->load->view('template/admin/footer');
	}

	public function admin_week()
	{
		$this->load->view('template/admin/header');
		$this->load->view('template/admin/sidebar');
		$this->load->view('template/admin/topbar');
		$this->load->view('admin/week');
		$this->load->view('template/admin/modal');
		$this->load->view('template/admin/footer');
	}

	public function admin_user()
	{
		$this->load->view('template/admin/header');
		$this->load->view('template/admin/sidebar');
		$this->load->view('template/admin/topbar');
		$this->load->view('admin/master_user/user');
		$this->load->view('template/admin/modal');
		$this->load->view('template/admin/footer');
	}

	public function admin_user_edit()
	{
		$this->load->view('template/admin/header');
		$this->load->view('template/admin/sidebar');
		$this->load->view('template/admin/topbar');
		$this->load->view('admin/master_user/user_edit');
		$this->load->view('template/admin/modal');
		$this->load->view('template/admin/footer');
	}

	public function admin_form()
	{
		$this->load->view('template/admin/header');
		$this->load->view('template/admin/sidebar');
		$this->load->view('template/admin/topbar');
		$this->load->view('admin/master_form/form');
		$this->load->view('template/admin/modal');
		$this->load->view('template/admin/footer');
	}

	public function admin_form_edit()
	{
		$this->load->view('template/admin/header');
		$this->load->view('template/admin/sidebar');
		$this->load->view('template/admin/topbar');
		$this->load->view('admin/master_form/form_edit');
		$this->load->view('template/admin/modal');
		$this->load->view('template/admin/footer');
	}

	public function list_approval()
	{
		$this->load->view('template/admin/header');
		$this->load->view('template/admin/sidebar');
		$this->load->view('template/admin/topbar');
		$this->load->view('admin/master_form/list_approval');
		$this->load->view('template/admin/modal');
		$this->load->view('template/admin/footer');
	}

	public function transaksi()
	{
		$this->load->view('template/admin/header');
		$this->load->view('template/admin/sidebar');
		$this->load->view('template/admin/topbar');
		$this->load->view('admin/master_transaksi/transaksi');
		$this->load->view('template/admin/modal');
		$this->load->view('template/admin/footer');
	}

	public function form_control_harian()
	{
		$this->load->view('pdf_template/form_control_harian');
	}

	public function form_legalitas_catering()
	{
		$this->load->view('pdf_template/form_legalitas_catering');
	}

	public function form_hasil_test_food_catering()
	{
		$this->load->view('pdf_template/form_hasil_test_food_catering');
	}

	public function form_komplain_usulan()
	{
		$this->load->view('pdf_template/form_komplain_usulan');
	}
	public function form_ewo()
	{
		$this->load->view('pdf_template/form_external_work_order');
	}
	public function formTes()
	{
		$this->load->view('pdf_template/debit_note');
	}

	public function admin_dn_raw()
	{
		$this->load->view('template/admin_dn/header');
		$this->load->view('template/admin_dn/sidebar');
		$this->load->view('template/admin_dn/topbar');
		$this->load->view('admin_dn/master_dn/dn_raw');
		$this->load->view('template/admin_dn/footer');
	}

	public function admin_dn_raw_edit()
	{
		$this->load->view('template/admin_dn/header');
		$this->load->view('template/admin_dn/sidebar');
		$this->load->view('template/admin_dn/topbar');
		$this->load->view('admin_dn/master_dn/dn_raw_edit');
		$this->load->view('template/admin_dn/footer');
	}

	public function admin_dn_generate()
	{
		$this->load->view('template/admin_dn/header');
		$this->load->view('template/admin_dn/sidebar');
		$this->load->view('template/admin_dn/topbar');
		$this->load->view('admin_dn/master_dn/dn_generate');
		$this->load->view('template/admin_dn/footer');
	}

	public function admin_dn_approved()
	{
		$this->load->view('template/admin_dn/header');
		$this->load->view('template/admin_dn/sidebar');
		$this->load->view('template/admin_dn/topbar');
		$this->load->view('admin_dn/master_dn/dn_approved');
		$this->load->view('template/admin_dn/footer');
	}

	public function admin_dn_rejected()
	{
		$this->load->view('template/admin_dn/header');
		$this->load->view('template/admin_dn/sidebar');
		$this->load->view('template/admin_dn/topbar');
		$this->load->view('admin_dn/master_dn/dn_rejected');
		$this->load->view('template/admin_dn/footer');
	}

	public function admin_dn_dashboard()
	{
		$this->load->view('template/admin_dn/header');
		$this->load->view('template/admin_dn/sidebar');
		$this->load->view('template/admin_dn/topbar');
		$this->load->view('admin_dn/master_dn/dn_dashboard');
		$this->load->view('template/admin_dn/footer');
	}

	public function display_for_tenant()
	{
		$this->load->view('template/tenant/header');
		$this->load->view('tenant/displayfortenant');
	}
}
