<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AuthController extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('User');
    }
    public function vlogin()
    {
        if (!empty($this->session->userdata('ROLE_USERS'))) {
            if ($this->session->userdata('ROLE_USERS') == 'Admin GA') {
                redirect('dashboard');
            } else if ($this->session->userdata('ROLE_USERS') == 'Admin Debitnote') {
                redirect('debitnote/dashboard');
            }
        }
        $this->load->view('template/header');
        $this->load->view('login');
        $this->load->view('template/footer');
    }
    public function login()
    {
        $datas = $_POST;
        $user = $this->User->get(['filter' => ['USER_USERS' => $datas['USER_USERS']]]);

        if(isset($_COOKIE['penalty']) && $_COOKIE['penalty'] == true){
            $time_left =  ($_COOKIE["expire"]);
            $time_left = $this->penalty_remaining(date("Y-m-d H:i:s", $time_left));
            $this->session->set_flashdata('error_login', 'Terlalu banyak permintaan login<br>Coba lagi dalam '.$time_left.'!!');
            redirect('login');
        }else{
            if ($user != null) {
                if ($user[0]->ROLE_USERS == 'Admin Debitnote' || $user[0]->ROLE_USERS == 'Admin GA') {
                    $pass = hash('sha256', md5($datas['PASSWORD_USERS']));
    
                    if ($pass == $user[0]->PASS_USERS) {
                        $dataSession = array(
                            'ID_USERS'      => $user[0]->ID_USERS,
                            'NAMA_USERS'    => $user[0]->NAMA_USERS,
                            'USER_USERS'    => $user[0]->USER_USERS,
                            'ROLE_USERS'    => $user[0]->ROLE_USERS
                        );
                        $this->session->set_userdata($dataSession);
    
                        if ($user[0]->ROLE_USERS == 'Admin GA') {
                            redirect('dashboard');
                        } else {
                            redirect('debitnote/dashboard');
                        }
                    } else {
                        $attempt = $this->session->userdata('attempt');
                        $attempt++;
                        $this->session->set_userdata('attempt', $attempt);
                
                        if ($attempt == 3) {
                            $attempt = 0;
                            $this->session->set_userdata('attempt', $attempt);
                
                            setcookie("penalty", true, time() + 300);
                            setcookie("expire", time() + 300, time() + 300);
                
                            $this->session->set_flashdata('error_login', 'Terlalu banyak permintaan login<br>Harap tunggu selama 5 menit !!');
                            redirect('login');
                
                        } else {
                            $this->session->set_flashdata('error_login', 'Username/Password tidak cocok!<br><i><b>Kesempatan login - '.(3-$attempt).'</b></i>');
                            redirect('login');
                        }
                    }
                } else {
                    redirect('login', $this->session->set_flashdata('error_login', 'Anda tidak memiliki hak akses!'));
                }
            }
            $attempt = $this->session->userdata('attempt');
            $attempt++;
            $this->session->set_userdata('attempt', $attempt);
    
            if ($attempt == 3) {
                $attempt = 0;
                $this->session->set_userdata('attempt', $attempt);
    
                setcookie("penalty", true, time() + 300);
                setcookie("expire", time() + 300, time() + 300);
    
                $this->session->set_flashdata('error_login', 'Terlalu banyak permintaan login<br>Harap tunggu selama 5 menit !!');
                redirect('login');
    
            } else {
                $this->session->set_flashdata('error_login', 'Data user tidak ditemukan!<br><i><b>Kesempatan login - '.(3-$attempt).'</b></i>');
                redirect('login');
            }
        }        
    }

    function penalty_remaining($datetime, $full = false) {
		$now = new DateTime;
		$ago = new DateTime($datetime);
		$diff = $now->diff($ago);

		$diff->w = floor($diff->d / 7);
		$diff->d -= $diff->w * 7;

		$string = array(
			'i' => 'menit ',
			's' => 'detik',
		);
		$a = null;
		foreach ($string as $k => &$v) {
			if ($diff->$k) {
				$v = $diff->$k . ' ' . $v;
				$a .= $v;
			} else {
				unset($string[$k]);
			}
		}
		return $a;
	}

    public function logout()
    {
        $this->session->sess_destroy();
        redirect('login');
    }
}
