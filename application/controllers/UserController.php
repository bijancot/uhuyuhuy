<?php
defined('BASEPATH') or exit('No direct script access allowed');

class UserController extends CI_Controller
{
    function __construct(){
        parent::__construct();
        $this->load->model('User');
        $this->load->library(array('upload', 'notification'));
    }
    public function vUser(){
        $data['listData'] = $this->User->getAll();

        $this->load->view('template/admin/header');
		$this->load->view('template/admin/sidebar');
		$this->load->view('template/admin/topbar');
		$this->load->view('admin/master_user/user', $data);
		$this->load->view('template/admin/modal');
		$this->load->view('template/admin/footer');
    }

    public function vUserEdit($id){
        $data['dataUser'] = $this->User->get(['filter' => ['ID_USERS' => $id]]);

        $this->load->view('template/admin/header');
		$this->load->view('template/admin/sidebar');
		$this->load->view('template/admin/topbar');
		$this->load->view('admin/master_user/user_edit', $data);
		$this->load->view('template/admin/modal');
		$this->load->view('template/admin/footer');
    }

    public function ajxGetData(){
        $draw   = $_POST['draw'];
        $offset = $_POST['start'];
        $limit  = $_POST['length']; // Rows display per page
        $search = $_POST['search']['value'];
        
        $user = $this->User->getDataTable(['offset' => $offset, 'limit' => $limit, 'search' => $search]);
        $datas = array();
        foreach ($user['records'] as $item) {
            if($item->ROLE_USERS == 'Admin GA' || $item->ROLE_USERS == 'Admin Debitnote'){
                continue;
            }
            
            $id     = "'".$item->ID_USERS."'";
            $nama   = "'".$item->NAMA_USERS."'";

            $datas[] = array( 
                "namaUser"  => $item->NAMA_USERS,
                "nrp"       => $item->USER_USERS,
                "role"      => $item->ROLE_USERS,
                "dept"      => $item->DEPT_USERS,
                "status"    => ($item->STAT_USERS == 0 ? 'Belum Diverifikasi' : 'Terverifikasi' ),
                "aktif"     => ($item->ISACTIVE_USERS == "1" ? 'Aktif' : 'Non Aktif' ),
                "aksi"      => '
                    <div class="btn-group" role="group">
                        <a href="' . site_url("user/edit/" . $item->ID_USERS) . '" class="btn btn-primary btn-sm rounded mr-1" data-tooltip="tooltip" data-placement="top" title="Ubah">
                            <i class="fa fa-edit"></i>
                        </a>
                        '.($item->STAT_USERS == 0 ? 
                            '<button type="button" data-toggle="modal" onclick="approve('.$id.', '.$nama.')" data-target="#mdlApprove" class="btn btn-success btn-sm mr-1 rounded mdlApprove" data-tooltip="tooltip" data-placement="top" title="Menyetujui">
                                <i class="fa fa-check"></i>
                            </button>'
                            :(
                                $item->ISACTIVE_USERS == 1 ?
                                    '<button type="button" data-toggle="modal" onclick="changeStat('.$id.', '.$nama.', 0)" data-target="#mdlChangeActive" class="btn btn-danger btn-sm mr-1 rounded mdlChangeActive" data-tooltip="tooltip" data-placement="top" title="Ubah ke Tidak Aktif">
                                        <i class="fa fa-ban"></i>
                                    </button>'
                                :
                                    '<button type="button" data-toggle="modal" onclick="changeStat('.$id.', '.$nama.', 1)" data-target="#mdlChangeActive" class="btn btn-success btn-sm mr-1 rounded mdlChangeActive" data-tooltip="tooltip" data-placement="top" title="Ubah ke Aktif">
                                        <i class="fa fa-sync-alt"></i>
                                    </button>'   
                            ))
                        .'                                                
                        <button type="button" data-toggle="modal" onclick="reset('.$id.', '.$nama.')" data-target="#mdlReset" class="btn btn-secondary btn-sm mr-1 rounded mdlRstPassUserItem" data-tooltip="tooltip" data-placement="top" title="Reset">
                            <i class="fa fa-key"></i>
                        </button>
                    </div>
                '
            );
        }

        $response = array(
            "draw" => intval($draw),
            "recordsTotal" => $user['totalRecords'],
            "recordsFiltered" => ($search != "" ? $user['totalDisplayRecords'] : $user['totalRecords']),
            "aaData" => $datas
        );

        echo json_encode($response);
    }

    public function store(){
        $param['NAMA_USERS']    = $this->db->escape_str(htmlentities($_POST['NAMA_USERS']));
        $param['USER_USERS']    = $this->db->escape_str(htmlentities($_POST['USER_USERS']));
        $param['NOTELP_USERS']  = $this->db->escape_str(htmlentities($_POST['NOTELP_USERS']));
        $param['DEPT_USERS']    = $this->db->escape_str(htmlentities($_POST['DEPT_USERS']));
        $param['DIV_USERS']     = $this->db->escape_str(htmlentities($_POST['DIV_USERS']));
        $param['PASS_USERS']    = $this->db->escape_str(htmlentities($_POST['PASS_USERS']));
        $param['STAT_USERS']    = $this->db->escape_str(htmlentities($_POST['STAT_USERS']));

        $user = $this->User->get(['filter' => ['USER_USERS' => $param['USER_USERS']]]);
        if($user != null){
            $this->session->set_flashdata('error', 'Oops NRP telah terdftar!');
            redirect('user');
        }

        $imageTtd = $this->upload_image();
        if($imageTtd['status'] == false){
            $this->session->set_flashdata('error', $imageTtd['msg']);
            redirect('user');
        }

        $param['ID_USERS']      = substr(md5(time()), 0, 8);
        $param['PASS_USERS']    = hash('sha256', md5($param['PASS_USERS']));        
        $param['PATH_TTD']      = $imageTtd['link'];

        $this->User->insert($param);
        redirect('user');
    }
    public function update(){
        $param = $_POST;

        // $user = $this->User->get(['filter' => ['USER_USERS' => $param['USER_USERS']]]);
        // if($user != null){
        //     $this->session->set_flashdata('error', 'Oops NRP telah terdftar!');
        //     redirect('user/edit/'.$param['ID_USERS']);
        // }
        
        $this->User->update($param);
        redirect('user');
    }
    public function destroy(){
        $param = $_POST;
        $this->User->delete($param);
        redirect('user');
    }
    public function verif(){
        $param                  = $_POST;
        $param['STAT_USERS']    = 1;

        $this->User->update($param);

        $notif['title']     = 'Konfirmasi Akun';
        $notif['message']   = 'Selamat Akun Anda Berhasil Terverifikasi !';
        $notif['regisIds']  = $this->db->get_where('USERS', ['ID_USERS' => $param['ID_USERS'], 'ISACTIVE_USERS' => '1'])->result_array();
        $this->notification->push($notif);

        redirect('user');
    }
    public function register(){
        $this->form_validation->set_rules('NAMA_USERS', 'Nama','alpha');
        $this->form_validation->set_rules('USER_USERS', 'NRP','numeric|is_unique[USERS.USER_USERS]');
        $this->form_validation->set_rules('NOTELP_USERS', 'Telp','numeric');
        $this->form_validation->set_rules('PASS_USERS', 'Password','alpha_numeric_spaces');

        if ($this->form_validation->run()==true)
	   	{
            $imageTtd = $this->upload_image();
            if($imageTtd['status'] == false){
                $this->session->set_flashdata('error', $imageTtd['msg']);
                redirect('register');
            }

            $param                  = $_POST;
            // $param['skdfsd']    = $this->db->escape(htmlentities($_POST['NAMA_USERS']));
            $param['NAMA_USERS']    = $this->db->escape_str(htmlentities($_POST['NAMA_USERS']));
            $param['USER_USERS']    = $this->db->escape_str(htmlentities($_POST['USER_USERS']));
            $param['NOTELP_USERS']  = $this->db->escape_str(htmlentities($_POST['NOTELP_USERS']));
            $param['DEPT_USERS']    = $this->db->escape_str(htmlentities($_POST['DEPT_USERS']));
            $param['DIV_USERS']     = $this->db->escape_str(htmlentities($_POST['DIV_USERS']));
            $param['PASS_USERS']    = hash('256', md5($this->db->escape_str(htmlentities($_POST['PASS_USERS']))));
            $param['STAT_USERS']    = $this->db->escape_str(htmlentities($_POST['STAT_USERS']));
            $param['ID_USERS']      = substr(md5(time()), 0, 8);
            $param['ROLE_USERS']    = 'Staff';        
            $param['PATH_TTD']      = $imageTtd['link'];


            $this->User->insert($param);
            $this->session->set_flashdata('success_register','Proses Pendaftaran User Berhasil');

            redirect('register');
        }else
		{
            // $this->session->set_flashdata('error','NRP telah digunakan');
            $this->session->set_flashdata('error',validation_errors());
			redirect('register');
		}
    }
    public function resetPassword(){
        $param = $_POST;
        $param['PASS_USERS']    = hash('sha256', md5('123ut456'));
        $this->User->update($param);

        $notif['status'] = 'Success';
        $notif['message'] = 'Berhasil reset password.';

        redirect('user', $this->session->set_flashdata('notif', ['status' => 'Success', 'message' => 'Berhasil reset password.']));
    }
    function upload_image(){
        $config['upload_path'] = './images/ttd/'; //path folder
        $config['allowed_types'] = 'jpg|png|jpeg|bmp'; //type yang dapat diakses bisa anda sesuaikan
        $config['encrypt_name'] = TRUE; //Enkripsi nama yang terupload
 
        $this->upload->initialize($config);
        if(!empty($_FILES['imageTtd']['name'])){
 
            if ($this->upload->do_upload('imageTtd')){
                $gbr = $this->upload->data();
                //Compress Image
                $config['image_library']='gd2';
                $config['source_image']='./images/ttd/'.$gbr['file_name'];
                $config['create_thumb']= FALSE;
                $config['maintain_ratio']= true;
                // $config['quality']= '100%';
                $config['width']= 600;
                // $config['height']= 400;
                $config['new_image']= './images/ttd/'.$gbr['file_name'];
                $this->load->library('image_lib', $config);
                $this->image_lib->resize();
 
                $gambar=$gbr['file_name'];

                return ['status' => true, 'link' => base_url('images/ttd/'.$gambar)];
            }else{
                return ['status' => false, 'msg' => $this->upload->display_errors()];
            }
                      
        }else{
            return ['status' => false, 'msg' => "Upload image is required"];
        }         
    }
    public function changeActive(){
        $param = $_POST;
        $this->User->update($param);
        redirect('user');
    }
}